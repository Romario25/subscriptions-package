<?php
namespace Romario25\Subscriptions\Services;


use GuzzleHttp\RequestOptions;

class AppslyerService
{
    public function sendEvent($deviceId, $idfa, $eventName, array $eventValue)
    {

        $config = config('subscriptions');


        $purchase_event = array(
            'appsflyer_id' => $deviceId, //device_id
            'idfa' => $idfa,
            'bundle_id' => $config['appsflyer']['BUNDLE'],
            'eventCurrency' => 'USD',
            'ip' => $deviceId,
            'eventTime' => date("Y-m-d H:i:s.000", time()),
        );

        $purchase_event['eventName'] = $eventName;
        $purchase_event['eventValue'] = json_encode($eventValue);

        $data_string = json_encode($purchase_event);

        $ch = curl_init('https://api2.appsflyer.com/inappevent/' . $config['appsflyer']['APP_ID']);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'authentication: ' . $config['appsflyer']['DEV_TOKEN'],
                'Content-Length: ' . strlen($data_string))
        );

        $result = curl_exec($ch);
    }
}