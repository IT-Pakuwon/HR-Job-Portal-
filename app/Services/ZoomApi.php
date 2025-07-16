<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Str;

class ZoomApi
{
    // protected $apiKey;
    // protected $apiSecret;
    protected $client;   

    public function __construct()
    {
        // $this->apiKey = config('app.zoom_api_key');
        // $this->apiSecret = config('app.zoom_api_secret');
        $this->client = new Client(['base_uri' => 'https://api.zoom.us/v2/']);    
            
    }

    public function createMeeting($data,$user_idzoom)
    {
        
        $user_id = $user_idzoom;
        $password = Str::random(7);
        // $user_id = 'me';
        $response = $this->client->request('POST', 'users/'.$user_id.'/meetings', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->generateToken(),
                'Content-Type' => 'application/json',
                'Accept'        => 'application/json',
            ],
            // 'json' => $data
            'json' => [
                'topic'      => $data['topic'],
                'type'       => $data['type'],
                'password'   => $password,
                'start_time' => $this->toZoomTimeFormat($data['start_time']),
                'duration'   => $data['duration'],
                'agenda'     => (! empty($data['agenda'])) ? $data['agenda'] : null,
                'timezone'     => 'Asia/Jakarta',
                'settings'   => [            
                    'waiting_room' => false,                   
                    'use_pmi' => false,
                ],
            ]
        ]);

        return json_decode($response->getBody());
    }

    public function updateMeeting($meeting_id, $data) {
        $password = Str::random(7);
        $response = $this->client->request('PATCH', 'meetings/' . $meeting_id, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->generateToken(),
                'Content-Type' => 'application/json',
                // 'Accept'        => 'application/json',
            ],
            // 'json' => [$data]
            'json' => [
                'topic'      => $data['topic'],
                'type'       => $data['type'],
                // 'password'   => $password,
                'start_time' => $this->toZoomTimeFormat($data['start_time']),
                'duration'   => $data['duration'],
                'agenda'     => (! empty($data['agenda'])) ? $data['agenda'] : null,
                'timezone'     => 'Asia/Jakarta',
                'settings'   => [
                    // 'host_video'        => ($data['host_video'] == "1") ? true : false,
                    // 'participant_video' => ($data['participant_video'] == "1") ? true : false,
                    'waiting_room'      => false,
                    'use_pmi' => false,
                ],
            ]
        ]);
        // dd($response);
        return json_decode($response->getBody());
              
    }

    public function getinvitation($id)
    {
        $response = $this->client->request('GET', 'meetings/'.$id.'/invitation', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->generateToken(),
                'Content-Type' => 'application/json',
                'Accept'        => 'application/json',
            ],
            'json' => []            
        ]);

        return json_decode($response->getBody(),true);
    }

    public function deletezoom($id)
    {
        $response = $this->client->request('delete', 'meetings/'.$id, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->generateToken(),
                'Content-Type' => 'application/json',
                'Accept'        => 'application/json',
            ],
            'json' => []            
        ]);

        return json_decode($response->getBody());
    }

    // protected function generateToken()
    // {
        
    //     $key = env('ZOOM_API_KEY', '');
    //     $secret = env('ZOOM_API_SECRET', '');
    //     $payload = [
    //         'iss' => $key,
    //         'exp' => strtotime('+1 minute'),
    //     ];

    //     return \Firebase\JWT\JWT::encode($payload, $secret, 'HS256');


    // }
    protected function generateToken()
    {
        // $clientId = env('ZOOM_CLIENT_ID', '');
        // $clientSecret = env('ZOOM_CLIENT_SECRET', '');
        // $accountId = env('ZOOM_ACCOUNT_ID', '');

        $clientId = 'n7O2kL_7RamGpRw04uxIA';
        $clientSecret = 'sndmCjWRuXTB5tA627M1huWUh1ehiSAe';
        $accountId = '5a_wdwK6TjGg8dl2ITsY7A';
        
        $client = new Client(['base_uri' => 'https://zoom.us/oauth/token']);
        $authHeader = base64_encode($clientId . ':' . $clientSecret);
        $response = $client->request('POST', '', [
            'headers' => [                
                'Authorization' => 'Basic ' . $authHeader,         
            ],
            'form_params' => [
                'grant_type'    => 'account_credentials',               
                'account_id' => $accountId,
            ],
        ]);

        $responseData = json_decode($response->getBody(), true);
       
        return $responseData['access_token'];
    }

    public function toZoomTimeFormat(string $dateTime)
    {
        // try {
            $date = new \DateTime($dateTime);

            return $date->format('Y-m-d\TH:i:s');
        // } catch (\Exception $e) {
        //     Log::error('ZoomJWT->toZoomTimeFormat : '.$e->getMessage());

        //     return '';
        // }
    }


}
