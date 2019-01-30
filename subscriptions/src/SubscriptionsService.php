<?php
namespace Romario25\Subscriptions;


use DB;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Str;
use Romario25\Subscriptions\DTO\SubscriptionDto;
use Romario25\Subscriptions\Entities\Subscription;
use Romario25\Subscriptions\Entities\SubscriptionHistory;
use Romario25\Subscriptions\Services\AppslyerService;
use Romario25\Subscriptions\Services\HandlerAppleWebhook;
use Romario25\Subscriptions\Services\ReceiptService;
use Romario25\Subscriptions\Services\SaveSubscriptionService;
use Romario25\Subscriptions\Services\VerifyService;

class SubscriptionsService
{

    private $config;
    private $verifyService;
    private $receiptService;

    public function __construct(ReceiptService $receiptService, VerifyService $verifyService)
    {
        $this->config = config('subscriptions');
        $this->verifyService = $verifyService;
        $this->receiptService = $receiptService;
    }


    public function handlerAppleWebhook($data)
    {

        HandlerAppleWebhook::handler($data);
    }

    public function handlerReceipt($deviceId, $environment, $latestReceipt, $latestReceiptInfo, $pendingRenewalInfo)
    {

        $endLatestReceiptInfo = end($latestReceiptInfo);

        $subscriptionDTO = new SubscriptionDto(
            $deviceId,
            $endLatestReceiptInfo->original_transaction_id,
            $endLatestReceiptInfo->product_id,
            $environment,
            $this->defineType($pendingRenewalInfo, $latestReceiptInfo),
            $endLatestReceiptInfo->purchase_date_ms,
            $endLatestReceiptInfo->expires_date_ms,
            $latestReceipt
        );

        $subscription = SaveSubscriptionService::saveSubscription($subscriptionDTO);

        $diffTransaction = SaveSubscriptionService::checkReceiptHistory($latestReceiptInfo, $subscription);

        foreach ($diffTransaction as $transaction) {
            AppslyerService::sendEvent(
                Subscription::TYPE_RENEWAL,
                '2DD5392C-ACA8-40C1-A309-2875582C3567',
                $deviceId,
                0);
        }


        $event = $this->getEventBySubscription($subscription);


        AppslyerService::sendEvent(
            $event,
            '2DD5392C-ACA8-40C1-A309-2875582C3567',
            $deviceId,
            0);

    }

    public function getResponseAppleReceipt($latestReceipt)
    {
        return $this->receiptService->sendReceipt($latestReceipt);
    }




    public function verifyReceipt($receiptToken)
    {
        try {

            $verifyData = $this->verifyService->verifyReceipt($receiptToken);

            return [
                'status' => 'OK'
            ];

        } catch (\Exception $e) {
            return [
                'status' => 'ERROR',
                'message' => $e->getMessage()
            ];
        }

    }




    private function sortLatestReceiptInfo($latestReceiptInfo) : array
    {
        $collect = collect($latestReceiptInfo);

        return $collect->sortBy('purchase_date_ms')->toArray();
    }

    private function defineType($pendingRenewalInfo, $latestReceiptInfo)
    {
        if (isset($pendingRenewalInfo->expiration_intent) && $pendingRenewalInfo->expiration_intent == 1 ) {
            return Subscription::TYPE_CANCEL;
        }

        $receiptInfo = $this->sortLatestReceiptInfo($latestReceiptInfo);

        $latestReceiptInfo = end($receiptInfo);

        $countReceiptInfo = count($receiptInfo);

        if ($latestReceiptInfo->is_trial_period == "true") {
            return Subscription::TYPE_TRIAL;
        }

        if ($countReceiptInfo == 2 && !isset($pendingRenewalInfo->expiration_intent)) {
            return Subscription::TYPE_INITIAL_BUY;
        }

        return Subscription::TYPE_RENEWAL;
    }

    public function getEventBySubscription(Subscription $subscription)
    {
        $config = config('subscriptions');

        $eventDuration = $config['events_duration'];

        $subscriptionType = $subscription->type;

        $prefix = 'test_';

        $event = '';

        $key = array_search($subscription->product_id, $eventDuration);

        switch ($subscriptionType) {
            case Subscription::TYPE_TRIAL:
                $event =  $prefix . 'start_trial';
            break;
            case Subscription::TYPE_INITIAL_BUY:
                $event = $prefix . $key . '_1';
            break;
            case Subscription::TYPE_RENEWAL:
                $count = SubscriptionHistory::where('subscription_id')
                    ->where('type', Subscription::TYPE_RENEWAL)->count();
                $event = $prefix . $key . '_' . $count;
            break;
            case Subscription::TYPE_CANCEL:
                $count = SubscriptionHistory::where('subscription_id')
                    ->where('type', Subscription::TYPE_RENEWAL)->count();
                $event = $prefix . 'cancel_' . $key . '_' . $count;
            break;
        }

        return $event;
    }


}