<?php
namespace Romario25\Subscriptions\Services;


use GuzzleHttp\RequestOptions;

class AppslyerService
{
    public function sendEvent()
    {
        $url = 'https://api2.appsflyer.com/inappevent/';

        $headers = [
            'authentication' => '',
            'Host' => '',
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ];

        $body = [
            'appsflyer_id' => '',
            'customer_user_id' => '',
            'eventName' => '',
            'eventValue' => [
                'af_revenue' => '',
                'af_content_id' => '',
                'renewal' => ''
            ],
            'eventCurrency' => '',
            'ip' => '',
            'eventTime' => '',
            'af_events_api' => ''
        ];

        $client = new \GuzzleHttp\Client();

        $response = $client->post($url,
            [
                'headers' => $headers,
                RequestOptions::JSON => $body
            ]
        );

        $result = $response->getBody()->getContents();


    }
}