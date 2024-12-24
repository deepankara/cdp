<?php

namespace App\Filament\Resources\WhatsappTemplateResource\Pages;

use App\Filament\Resources\WhatsappTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

class EditWhatsappTemplate extends EditRecord
{
    protected static string $resource = WhatsappTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if(isset($data['header_type']) && $data['header_type'] == "IMAGE" || $data['header_type'] == "DOCUMENT" || $data['header_type'] == "VIDEO"){
            if($data['header_type'] == "IMAGE"){
                $attachment = $data['attachment'];
            }else{
                $attachment = $data['document'];
            }
            $binaryData = Storage::disk('local')->get($attachment);
            $size = Storage::disk('local')->size($attachment);
            $fileName = basename($attachment);
            $fileName = explode("/",$fileName)[0];
            $accessToken = env('WHATSAPP_API_TOKEN');
            $baseUrl = 'https://graph.facebook.com/v21.0/1555308755122736/uploads';
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
                    $header['example']['header_text'] = json_decode($whatsappTemplate['header_variables_sample'],true)["{{1}}"];
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
            $body['example']['body_text'] = array_values($whatsappTemplate['body_variables_sample']);
            // $body['example']['body_text'] = array_values(json_decode($whatsappTemplate['body_variables_sample'],true));
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
                    if($value['option'] == 'URL'){
                        $buttonForJson['url'] = $value['url'];
                        if($value['url_type'] == "dynamic"){
                            $buttonForJson['url'] = $buttonForJson['url']."/{{1}}";
                            $buttonForJson['example'] = array($value['url_example']);
                        }
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

        // echo "<pre>";print_r($json);exit;


        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://graph.facebook.com/v21.0/105692085530607/message_templates',
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

        if(isset($response['id']) && $response['id'] != ''){
            $data['template_id'] = $response['id'];
            $data['template_status'] = $response['status'];
        }
        return $data;
    }
}
