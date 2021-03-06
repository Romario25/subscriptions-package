<?php
namespace Romario25\Subscriptions\Services;


use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

class AppslyerService
{
    
    public static function sendEvent($eventName, $idfa, $deviceId, $price, $currency = 'USD')
    {

        $config = config('subscriptions');

        $body = [
            'appsflyer_id' => $config['appsflyer']['APP_ID'],
            'eventName' => $eventName,
            'af_events_api' => "true",
            'bundle_id' => $config['appsflyer']['BUNDLE'],
            'eventCurrency' => $currency,
            'customer_user_id' => $deviceId,
            'device_id' => $deviceId
        ];

        if (!is_null($idfa)) {
            $body['idfa'] = $idfa;
        }

        $eventValue = [
            'af_revenue' => (string) $price
        ];

        $body['eventValue'] = json_encode($eventValue);


        $client = new Client([
            'timeout'  => 30.0,
        ]);

        try {
            $response = $client->request('POST', 'https://api2.appsflyer.com/inappevent/id' . $config['appsflyer']['APP_ID'], [
                RequestOptions::JSON => $body,
                'headers' => [
                    'authentication' => $config['appsflyer']['DEV_TOKEN'],
                ]
            ]);

            $body = $response->getBody();

            dump($response->getStatusCode());
            dump($body->getContents());

            $phrase = $response->getReasonPhrase();

            if ($phrase != 'OK') {
                \Log::error('Bad response from apps flyer analytics:'.PHP_EOL.$response->getBody());
                return;
            }

        } catch (\Exception $e) {
            \Log::error('SEND EVENT APPSFLYER : :'. $e->getMessage());
            return;
        }
    }

}