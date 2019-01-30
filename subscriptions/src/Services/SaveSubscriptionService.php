<?php
namespace Romario25\Subscriptions\Services;

use Illuminate\Support\Str;
use Romario25\Subscriptions\DTO\SubscriptionDto;
use Romario25\Subscriptions\DTO\SubscriptionHistoryDto;
use Romario25\Subscriptions\Entities\Subscription;
use Romario25\Subscriptions\Entities\SubscriptionHistory;

class SaveSubscriptionService
{

    public static function issetSubscription($deviceId, $originalTransactionId)
    {
        return Subscription::where('device_id', $deviceId)
            ->where('original_transaction_id', $originalTransactionId)->first();
    }





    public static function saveSubscription(SubscriptionDto $subscriptionDto)
    {
        /** @var Subscription $subscription */

        $subscription = Subscription::where('device_id', $subscriptionDto->deviceId)
            ->where('original_transaction_id', $subscriptionDto->originalTransactionId)->first();

        if (is_null($subscription)) {
            $subscription = Subscription::create([
                'id' => Str::uuid(),
                'device_id' => $subscriptionDto->deviceId,
                'product_id' => $subscriptionDto->productId,
                'environment' => $subscriptionDto->environment,
                'original_transaction_id' => $subscriptionDto->originalTransactionId,
                'type' => $subscriptionDto->type,
                'start_date' => $subscriptionDto->startDate,
                'end_date' => $subscriptionDto->endDate,
                'latest_receipt' => $subscriptionDto->latestReceipt
            ]);
        } else {
            $subscription->update([
                'device_id' => $subscriptionDto->deviceId,
                'product_id' => $subscriptionDto->productId,
                'environment' => $subscriptionDto->environment,
                'original_transaction_id' => $subscriptionDto->originalTransactionId,
                'type' => $subscriptionDto->type,
                'start_date' => $subscriptionDto->startDate,
                'end_date' => $subscriptionDto->endDate,
                'latest_receipt' => $subscriptionDto->latestReceipt
            ]);
        }

        return $subscription;
    }



    public static function saveSubscriptionHistory(SubscriptionHistoryDto $subscriptionHistoryDto)
    {
        $subscriptionHistory = SubscriptionHistory::where('transaction_id', $subscriptionHistoryDto->transactionId)
            ->first();

        if (is_null($subscriptionHistory)) {
            $subscriptionHistory = SubscriptionHistory::create([
                'id' => $subscriptionHistoryDto->id,
                'subscription_id' => $subscriptionHistoryDto->subscriptionId,
                'product_id' => $subscriptionHistoryDto->productId,
                'environment' => $subscriptionHistoryDto->environment,
                'start_date' => $subscriptionHistoryDto->startDate,
                'end_date' => $subscriptionHistoryDto->endDate,
                'type' => $subscriptionHistoryDto->type,
                'transaction_id' => $subscriptionHistoryDto->transactionId
            ]);

            $count = SubscriptionHistory::where('product_id', $subscriptionHistoryDto->productId)
                ->where('subscription_id', $subscriptionHistoryDto->subscriptionId)->count();

            SubscriptionHistory::where('id', $subscriptionHistory->id)
                ->update([
                    'count' => $count
                ]);

        }
    }

    public static function checkReceiptHistory(array $latestReceiptInfo, $subscription)
    {
        $collect = collect($latestReceiptInfo)
            ->keyBy('transaction_id')->toArray();



        $lastCollect = end($collect);

        $arrayTransactionId = array_keys($collect);

        $deviceId = $subscription->deviceId;

        $savedAlreadyTransactionId = SubscriptionHistory::whereHas('subscription', function($query) use ($deviceId) {
            $query->where('device_id', $deviceId);
        })->pluck('transaction_id')->toArray();

        $arrayDiffTransactionId = array_diff($arrayTransactionId, $savedAlreadyTransactionId);

        if (count($arrayDiffTransactionId) > 0) {
            foreach ($arrayDiffTransactionId as $transactionId) {
                $subscriptionHistoryDTO = new SubscriptionHistoryDto(
                    $subscription->id,
                    $transactionId,
                    $collect[$transactionId]->product_id,
                    $subscription->environment,
                    $collect[$transactionId]->purchase_date_ms,
                    $collect[$transactionId]->expires_date_ms,
                    ($collect[$transactionId]->is_trial_period == "true") ? Subscription::TYPE_TRIAL : Subscription::TYPE_RENEWAL
                );

                SaveSubscriptionService::saveSubscriptionHistory($subscriptionHistoryDTO);
            }

            return collect($latestReceiptInfo)
                ->whereIn('transaction_id', $arrayDiffTransactionId)->toArray();
        }


        return null;


    }
}