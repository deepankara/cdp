<?php

namespace App\Filament\Resources\WhatsappTemplateResource\Pages;

use App\Filament\Resources\WhatsappTemplateResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;


class CreateWhatsappTemplate extends CreateRecord
{
    protected static string $resource = WhatsappTemplateResource::class;

    protected function beforeCreate(): void
    {
        $data = $this->data;
        $found = true;
        // if($data['category'] == "MARKETING"){
        //     $found = false;
        //     if(isset($data['buttons']) && $data['buttons'] != ''){
        //         $buttonsCheck = $data['buttons'];
        //         if(count($buttonsCheck) >= 1){
        //             foreach($buttonsCheck as $buttonCheckKey => $buttonCheckValue){
        //                 if($buttonCheckValue['option'] == 'QUICK_REPLY'){
        //                     if($buttonCheckValue['button_text'] == "STOP"){
        //                         $found = true;
        //                     }
        //                 }
        //             }
        //         }
        //     }
        // }
        if($data['category'] == "MARKETING"){
            $found = false;
            if (str_contains($data['content'], "STOP")) {
                $found = true;
            }
            // if(isset($data['buttons']) && $data['buttons'] != ''){
            //     $buttonsCheck = $data['buttons'];
            //     if(count($buttonsCheck) >= 1){
            //         foreach($buttonsCheck as $buttonCheckKey => $buttonCheckValue){
            //             if($buttonCheckValue['option'] == 'QUICK_REPLY'){
            //                 if($buttonCheckValue['button_text'] == "STOP"){
            //                     $found = true;
            //                 }
            //             }
            //         }
            //     }
            // }
        }

        if(!$found){
            Notification::make()
            ->title("PLEASE ADD STOP QUICK REPLY BUTTON")
            ->danger()
            ->color('danger')
            ->duration(5000)
            ->send();
            $this->halt();
        }

        if(isset($data['category']) && $data['category'] != "AUTHENTICATION"){
            if(isset($data['header_type']) && $data['header_type'] == "IMAGE" || $data['header_type'] == "DOCUMENT" || $data['header_type'] == "VIDEO"){
                if($data['header_type'] == "IMAGE" ){
                    $attachment = $data['attachment'];
                    if(is_array($attachment)){
                        $attachment  = array_values($attachment)[0];
                    }
                }else{
                    $attachment = $data['document'];
                    if(is_array($attachment)){
                        $attachment  = array_values($attachment)[0];
                    }
                }
                $binaryData = Storage::disk('local')->get($attachment);
                $size = Storage::disk('local')->size($attachment);
                $fileName = basename($attachment);
                $fileName = explode("/",$fileName)[0];
                $accessToken = env('WHATSAPP_API_TOKEN');
                $baseUrl = 'https://graph.facebook.com/v21.0/'.env("WHATSAPP_ACCOUNT_ID").'/uploads';
                $fileType = Storage::disk('local')->mimeType($attachment);
                $queryParams = [
                    'file_name' => $fileName,
                    'file_length' => $size,
                    'file_type' => $fileType,
                    'access_token' => $accessToken,
                ];
                $url = $baseUrl . '?' . http_build_query($queryParams);
                
        
                $curl = curl_init();
                curl_setopt_array($curl, 
                    array(
                        CURLOPT_URL => $url,
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => '',
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 0,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => 'POST',
                    )
                );
                $response = curl_exec($curl);
                curl_close($curl);
                $response = json_decode($response,true);
                if(isset($response['id']) && $response['id'] != ''){
                    $uploadId = $response['id'];
                    $data['upload_id'] = $uploadId;
                    $url =  "https://graph.facebook.com/v21.0/".$uploadId;
                    $curl = curl_init();
                    curl_setopt_array($curl, array(
                        CURLOPT_URL => $url, // Replace with your endpoint
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => '',
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 0,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => 'POST',
                        CURLOPT_POSTFIELDS => $binaryData, // Attach binary file data here
                        CURLOPT_HTTPHEADER => array(
                            'Authorization: OAuth '.$accessToken, // Replace with your token
                            'file_offset: 0',
                            'Content-Type: ' . $fileType, // Use the detected MIME type
                        ),
                    ));
                    $response = curl_exec($curl);
                    curl_close($curl);
                    $response = json_decode($response,true);
                    if(isset($response['h']) && $response['h'] != ''){
                        $data['media_id'] = $response['h'];
                    }
                }
            }

            $whatsappTemplate = $data;
            $json = [];
            $json['name'] = $whatsappTemplate['name'];
            $json['language'] = $whatsappTemplate['language'];
            $json['category'] = $whatsappTemplate['category'];
            $json['components'] = [];

            if(isset($whatsappTemplate['header_type']) && $whatsappTemplate['header_type'] != 'NONE'){
                $header = [];
                $header['type'] = "HEADER";
                $header['format'] = $whatsappTemplate['header_type'];
                if($whatsappTemplate['header_type'] == "TEXT"){
                    $header['text'] = $whatsappTemplate['header_name'];
                    if(str_contains($whatsappTemplate['header_name'],"{{1}}")){
                        $header['example']['header_text'] = $whatsappTemplate['header_variables_sample']["{{1}}"];
                    }
                }

                if($whatsappTemplate['header_type'] == "IMAGE" || $whatsappTemplate['header_type'] == "DOCUMENT" || $whatsappTemplate['header_type'] == "VIDEO"){
                    $header['example']['header_handle'] = array($whatsappTemplate['media_id']);
                }
                array_push($json['components'],$header);
            }

            $body = [];
            $body['type'] = 'BODY';
            $body['text'] = $whatsappTemplate['html_content'];
            if(str_contains($whatsappTemplate['html_content'],"{{1}}")){
                $body['example']['body_text'] = [array_values($whatsappTemplate['body_variables_sample'])];
            }
            array_push($json['components'],$body);

            if(isset($whatsappTemplate['content']) && $whatsappTemplate['content'] != ''){
                $footer = [];
                $footer['type'] = 'FOOTER';
                $footer['text'] = $whatsappTemplate['content'];
                array_push($json['components'],$footer);
            }

            if(isset($whatsappTemplate['buttons']) && $whatsappTemplate['buttons'] != ''){
                $buttonsJson = $whatsappTemplate['buttons'];
                if(count($buttonsJson) >= 1){
                    $buttons = [];
                    $buttons['type'] = 'BUTTONS';
                    $buttons['buttons'] = [];
                    foreach($buttonsJson as $key => $value){
                        $buttonForJson = [];
                        $buttonForJson['type'] = $value['option'];
                        $buttonForJson['text'] = $value['button_text'];
                        if($value['option'] == 'URL' && $value['url_type'] == "static"){
                            $buttonForJson['url'] = $value['url'];
                        }
                        if($value['option'] == 'URL' && $value['url_type'] == "dynamic"){
                            $buttonForJson['url'] = $value['dynamic_url'];
                            $buttonForJson['example'] = $value['dynamic_url_example'];
                        }
                        

                        if($value['option'] == "PHONE_NUMBER"){
                            $buttonForJson['phone_number'] = $value['phone_number'];
                        }

                        if($value['option'] == "COPY_CODE"){
                            unset($buttonForJson['text']);
                            $buttonForJson['example'] = $value['offer_code'];
                        }
                        array_push($buttons['buttons'],$buttonForJson);
                    }
                    array_push($json['components'],$buttons);
                }
            }
        }else{
            $whatsappTemplate = $data;
            $json = [
                'name' => $whatsappTemplate['name'],
                'language' => $whatsappTemplate['language'],
                'category' => $whatsappTemplate['category'],
                'components' => []
            ];

            // BODY Component
            if (!empty($whatsappTemplate['add_security_recommendation'])) {
                $json['components'][] = [
                    'type' => 'BODY',
                    'add_security_recommendation' => (bool)$whatsappTemplate['add_security_recommendation']
                ];
            }

            // Footer Component
            if (!empty($whatsappTemplate['code_expiry'])) {
                $json['components'][] = [
                    'type' => 'footer',
                    'code_expiration_minutes' => (int)$whatsappTemplate['code_expiry']
                ];
            }

            // Buttons Component
            if (!empty($whatsappTemplate['copy_code_button_text'])) {
                $json['components'][] = [
                    'type' => 'buttons',
                    'buttons' => [
                        [
                            'type' => 'otp',
                            'otp_type' => 'copy_code',
                            'text' => $whatsappTemplate['copy_code_button_text']
                        ]
                    ]
                ];
            }
        }
        Log::info(json_encode($json));

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://graph.facebook.com/v21.0/'.env("WHATSAPP_API_ID").'/message_templates',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($json),
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer '.env("WHATSAPP_API_TOKEN"),
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        $response = json_decode($response,true);
        Log::info($json);
        Log::info($response);
        if(isset($response['error']) && $response['error'] != ''){
            if(isset($response['error']['message']) && $response['error']['message'] != ''){
                Notification::make()
                ->title($response['error']['message'])
                ->body(isset($response['error']['error_user_msg']) ? $response['error']['error_user_msg'] : 'Default error message')
                ->danger()
                ->color('danger')
                ->duration(5000)
                ->send();
                $this->halt();
            }
        }else{
            if(isset($response['id']) && $response['id'] != ''){
                $data['template_id'] = $response['id'];
                $data['template_status'] = $response['status'];
            }else{
                $this->halt();
            }
        }
        Log::info($this->data);
        $this->data = $data;
    }
}
