<?php
namespace Romario25\Subscriptions\Services;


use Romario25\Subscriptions\DTO\SubscriptionDto;
use Romario25\Subscriptions\DTO\SubscriptionHistoryDto;
use Romario25\Subscriptions\Entities\Subscription;
use Romario25\Subscriptions\Entities\SubscriptionHistory;

class SaveSubscriptionService
{
    public static function saveSubscription(SubscriptionDto $subscriptionDto)
    {
        /** @var Subscription $subscription */
        $subscription = Subscription::updateOrCreate(
            [
                'device_id' => $subscriptionDto->deviceId,
                'original_transaction_id' => $subscriptionDto->originalTransactionId
            ],
            [
                'id' => $subscriptionDto->id,
                'device_id' => $subscriptionDto->deviceId,
                'product_id' => $subscriptionDto->productId,
                'environment' => $subscriptionDto->environment,
                'original_transaction_id' => $subscriptionDto->originalTransactionId,
                'type' => $subscriptionDto->type,
                'start_date' => $subscriptionDto->startDate,
                'end_date' => $subscriptionDto->endDate,
                'latest_receipt' => $subscriptionDto->latestReceipt
            ]
        );

        return $subscription;
    }

    public static function saveSubscriptionHistory(SubscriptionHistoryDto $subscriptionHistoryDto)
    {
        $subscriptionHistory = SubscriptionHistory::where('transaction_id', $subscriptionHistoryDto->transactionId)
            ->first();

        if (is_null($subscriptionHistory)) {
            $subscriptionHistory = SubscriptionHistory::create([
                'id' => $subscriptionHistoryDto->id,
                'subscription_id' => $subscriptionHistoryDto->subscription->id,
                'product_id' => $subscriptionHistoryDto->subscription->product_id,
                'environment' => $subscriptionHistoryDto->subscription->environment,
                'start_date' => $subscriptionHistoryDto->subscription->start_date,
                'end_date' => $subscriptionHistoryDto->subscription->end_date,
                'type' => $subscriptionHistoryDto->subscription->type,
                'transaction_id' => $subscriptionHistoryDto->transactionId
            ]);

            $count = SubscriptionHistory::where('product_id', $subscriptionHistoryDto->subscription->product_id)
                ->where('subscription_id', $subscriptionHistoryDto->subscription->id)->count();

            SubscriptionHistory::where('id', $subscriptionHistory->id)
                ->update([
                    'count' => $count
                ]);


        }
    }
}