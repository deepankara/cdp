<?php

namespace App\Filament\Resources\WhatsappTemplateResource\Pages;

use App\Filament\Resources\WhatsappTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateWhatsappTemplate extends CreateRecord
{
    protected static string $resource = WhatsappTemplateResource::class;

    // protected function mutateFormDataBeforeCreate(array $data): array
    // {
    //     $whatsappTemplate = $data;
    //     $json = [];
    //     $json['name'] = $whatsappTemplate['name'];
    //     $json['language'] = $whatsappTemplate['language'];
    //     $json['category'] = $whatsappTemplate['category'];
    //     $json['components'] = [];

    //     if(isset($whatsappTemplate['header_type']) && $whatsappTemplate['header_type'] != 'NONE'){
    //         $header = [];
    //         $header['type'] = "HEADER";
    //         $header['format'] = $whatsappTemplate['header_type'];
    //         $header['text'] = $whatsappTemplate['header_name'];
    //         if(str_contains($whatsappTemplate['header_name'],"{{1}}")){
    //             $header['example']['header_text'] = json_decode($whatsappTemplate['header_variables_sample'],true)["{{1}}"];
    //         }
    //         array_push($json['components'],$header);
    //     }

    //     $body = [];
    //     $body['type'] = 'BODY';
    //     $body['text'] = $whatsappTemplate['html_content'];
    //     if(str_contains($whatsappTemplate['html_content'],"{{1}}")){
    //         $body['example']['body_text'] = array_values(json_decode($whatsappTemplate['header_variables_sample'],true));
    //     }
    //     array_push($json['components'],$body);

    //     if(isset($whatsappTemplate['content']) && $whatsappTemplate['content'] != ''){
    //         $footer = [];
    //         $footer['type'] = 'FOOTER';
    //         $footer['text'] = $whatsappTemplate['content'];
    //         array_push($json['components'],$footer);
    //     }

    //     if(isset($whatsappTemplate['buttons']) && $whatsappTemplate['buttons'] != ''){
    //         $buttonsJson = json_decode($whatsappTemplate['buttons'],true);
    //         if(count($buttonsJson) >= 1){
    //             $buttons = [];
    //             $buttons['type'] = 'BUTTONS';
    //             $buttons['buttons'] = [];
    //             foreach($buttonsJson as $key => $value){
    //                 $buttonForJson = [];
    //                 $buttonForJson['type'] = $value['option'];
    //                 $buttonForJson['text'] = $value['button_text'];
    //                 if($value['option'] == 'URL'){
    //                     $buttonForJson['url'] = $value['url'];
    //                 }

    //                 if($value['option'] == "PHONE_NUMBER"){
    //                     $buttonForJson['phone_number'] = $value['phone_number'];
    //                 }

    //                 if($value['option'] == "COPY_OFFER_CODE"){
    //                     $buttonForJson['offer_code'] = $value['offer_code'];
    //                 }
    //                 array_push($buttons['buttons'],$buttonForJson);
    //             }
    //             array_push($json['components'],$buttons);
    //         }
    //     }

    //     $curl = curl_init();
    //     curl_setopt_array($curl, array(
    //         CURLOPT_URL => 'https://graph.facebook.com/v21.0/105692085530607/message_templates',
    //         CURLOPT_RETURNTRANSFER => true,
    //         CURLOPT_ENCODING => '',
    //         CURLOPT_MAXREDIRS => 10,
    //         CURLOPT_TIMEOUT => 0,
    //         CURLOPT_FOLLOWLOCATION => true,
    //         CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    //         CURLOPT_CUSTOMREQUEST => 'POST',
    //         CURLOPT_POSTFIELDS => json_encode($json),
    //         CURLOPT_HTTPHEADER => array(
    //             'Authorization: Bearer '.env("WHATSAPP_API_TOKEN"),
    //             'Content-Type: application/json'
    //         ),
    //     ));

    //     $response = curl_exec($curl);
    //     curl_close($curl);

    // }
}
