<?php
namespace Romario25\Subscriptions\Services;


use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Romario25\Subscriptions\DTO\SubscriptionDto;
use Romario25\Subscriptions\DTO\SubscriptionHistoryDto;
use Romario25\Subscriptions\Entities\Subscription;
use Romario25\Subscriptions\Entities\SubscriptionHistory;

class HandlerAppleWebhook
{
    public static function handler($data)
    {

        try {

            \DB::beginTransaction();

            $latestReceiptInfo = $data['latest_receipt_info'];

            $subcriptionDTO = new SubscriptionDto(
                Str::uuid(),
                $latestReceiptInfo['unique_vendor_identifier'],
                $latestReceiptInfo['original_transaction_id'],
                $latestReceiptInfo['product_id'],
                $data['environment'],
                $data['notification_type'],
                $latestReceiptInfo['purchase_date_ms'],
                $latestReceiptInfo['expires_date'],
                $data['latest_receipt']
            );

            $subscription = SaveSubscriptionService::saveSubscription($subcriptionDTO);

            $subscriptionHistoryDto = new SubscriptionHistoryDto($subscription, $latestReceiptInfo['transaction_id']);


            SaveSubscriptionService::saveSubscriptionHistory($subscriptionHistoryDto);

            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            Log::error('ERROR HANDLER APPLE WEBHOOK : ' . $e->getMessage());
        }



    }


}