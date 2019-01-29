<?php
namespace Romario25\Subscriptions\Services;


use Illuminate\Support\Facades\Log;

use Illuminate\Support\Str;
use Romario25\Subscriptions\DTO\SubscriptionDto;
use Romario25\Subscriptions\DTO\SubscriptionHistoryDto;
use Romario25\Subscriptions\Entities\Subscription;
use Romario25\Subscriptions\Entities\SubscriptionHistory;

class HandlerVerifyReceipt
{
    public static function handler($deviceId, $latestReceipt, $latestReceiptInfo, $environment, $type)
    {
        try {
            \DB::beginTransaction();

            $issetTransaction = SubscriptionHistory::where('transaction_id')
                ->whereHas('subscription', function($query) use ($deviceId) {
                    $query->where('device_id', $deviceId);
                })->exists();

            if (!$issetTransaction) {

                $subscriptionDTO = new SubscriptionDto(
                    Str::uuid(),
                    $deviceId,
                    $latestReceipt['original_transaction_id'],
                    $latestReceiptInfo['product_id'],
                    $environment,
                    $type,
                    $latestReceiptInfo['purchase_date_ms'],
                    $latestReceiptInfo['expires_date'],
                    $latestReceipt
                );

                $subscription = SaveSubscriptionService::saveSubscription($subscriptionDTO);

                $subscriptionHistoryDTO = new SubscriptionHistoryDto($subscription, $latestReceipt['transaction_id']);

                SaveSubscriptionService::saveSubscriptionHistory($subscriptionHistoryDTO);
            }


            \DB::commit();
        } catch (\Exception $e) {
            Log::error('ERROR HANDLER VERIFY RECEIPT : ' . $e->getMessage());
            \DB::rollBack();
        }
    }

    private static function defineType($pendingRenewalInfo, $latestReceiptInfo)
    {
        if ($pendingRenewalInfo->expiration_intent == 1 ) {
            return Subscription::TYPE_CANCEL;
        }
//
//        $receiptInfo = $this->sortLatestReceiptInfo($data->latest_receipt_info);
//
//        $latestReceiptInfo = end($receiptInfo);
//
//        $countReceiptInfo = count($receiptInfo);
//
//        if ($latestReceiptInfo->is_trial_period == "true") {
//            return Subscription::TYPE_TRIAL;
//        }
//
//        if ($countReceiptInfo == 2 && !isset($data->pending_renewal_info)) {
//            return Subscription::TYPE_INITIAL_BUY;
//        }

        return Subscription::TYPE_RENEWAL;
    }
}