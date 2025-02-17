<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Log;
use Carbon\Carbon;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RulesController;
use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\Http;
use Jenssegers\Agent\Agent;
use Illuminate\Support\Facades\Cache;
use Auth;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Arr;

class SendgridController extends BaseController
{
    public static function sendGridEmail($attributes){
        $sendGridApiKey = env('SENDGRID_EMAIL_KEY');
        $sendGridFromName = env('SENDGRID_FROM_EMAIL');

        $data = [
            'personalizations' => [
                [
                    'to' => [
                        ['email' => $attributes['email']]
                    ],
                    'custom_args' => [
                        'type' => "transactional",
                        "template_id" => $attributes['template_id']
                    ]

                ]
            ],
            'from' => [
                'email' => $sendGridFromName,
                'name' => $attributes['email_from_name']
            ],
            'subject' => $attributes['email_subject'],
            'content' => [
                [
                    'type' => 'text/html',
                    'value' => $attributes['html_content']
                ]
            ],
            'tracking_settings' => [
                'click_tracking' => [
                    'enable' => true,
                    'enable_text' => true
                ],
                'open_tracking' => [
                    'enable' => true
                ]
            ]
        ];

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.sendgrid.com/v3/mail/send',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>json_encode($data),
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer '.$sendGridApiKey.'',
                'Content-Type: application/json'
            ),
        ));
        $response = curl_exec($curl);
        $error_msg = curl_error($curl);
        $http_status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        // echo $http_status_code;
        return $http_status_code;
    }

    public static function sendSendGridEmail($campaign,$customers){
        $sendGridApiKey = env('SENDGRID_EMAIL_KEY');
        $sendGridFromName = env('SENDGRID_FROM_EMAIL');

        if(isset($campaign['is_retarget']) && $campaign['is_retarget'] == 'yes'){
            $data = [
                'personalizations' => [
                    [
                        'to' => [
                            ['email' => $customers->email]
                        ],
                        'custom_args' => [
                            'campaign_id' => $campaign['campaign_id'],
                            'retargeting_campaign_id' => $campaign['id']
                        ]
    
                    ]
                ],
                'from' => [
                    'email' => $sendGridFromName,
                    'name' => $campaign['retarget']['email_from_name']
                ],
                'subject' => $campaign['retarget']['email_subject'],
                'content' => [
                    [
                        'type' => 'text/html',
                        'value' => $customers->html_content
                    ]
                ],
                'tracking_settings' => [
                    'click_tracking' => [
                        'enable' => true,
                        'enable_text' => true
                    ],
                    'open_tracking' => [
                        'enable' => true
                    ]
                ]
            ];
        }else{
            $data = [
                'personalizations' => [
                    [
                        'to' => [
                            ['email' => "gilchrist.auxilo@gmail.com"]
                        ],
                        'custom_args' => [
                            'campaign_id' => $campaign['campaign_id']
                        ]
    
                    ]
                ],
                'from' => [
                    'email' => $sendGridFromName,
                    'name' => $campaign['email_from_name']
                ],
                'subject' => $campaign['email_subject'],
                'content' => [
                    [
                        'type' => 'text/html',
                        'value' => $customers->html_content
                    ]
                ],
                'tracking_settings' => [
                    'click_tracking' => [
                        'enable' => true,
                        'enable_text' => true
                    ],
                    'open_tracking' => [
                        'enable' => true
                    ]
                ]
            ];
        }
        echo "<pre>";print_r($data);

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.sendgrid.com/v3/mail/send',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>json_encode($data),
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer '.$sendGridApiKey.'',
                'Content-Type: application/json'
            ),
        ));
        $response = curl_exec($curl);
        $http_status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        echo "<pre>";print_r($http_status_code);exit;
        curl_close($curl);
        return true;
    }
}