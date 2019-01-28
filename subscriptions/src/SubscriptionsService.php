<?php
namespace Romario25\Subscriptions;


use DB;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Str;
use Romario25\Subscriptions\Entities\Subscription;
use Romario25\Subscriptions\Entities\SubscriptionHistory;
use Romario25\Subscriptions\Services\VerifyService;

class SubscriptionsService
{

    private $config;
    private $verifyService;

    public function __construct(VerifyService $verifyService)
    {
        $this->config = config('subscriptions');
        $this->verifyService = $verifyService;
    }





    public function verifyReceipt($receiptToken, $deviceId, $userId)
    {
        try {
            $verifyData = $this->verifyService->verifyReceipt($receiptToken);

            // save or update subscription
            $subscription = $this->saveSubscription($verifyData, $deviceId, $userId);


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

    public function saveSubscription($data, $deviceId, $userId = null)
    {

        //try {

       //     DB::beginTransaction();

            $latestReceiptInfo = $this->sortLatestReceiptInfo($data->latest_receipt_info);

            $latestReceiptInfo = end($latestReceiptInfo);

            /** @var Subscription $subscription */
            $subscription = Subscription::updateOrCreate(
                [
                    'device_id' => $deviceId,
                    'original_transaction_id' => $data->pending_renewal_info[0]->original_transaction_id
                ],
                [
                    'id' => Str::uuid(),
                    'user_id' => $userId,
                    'device_id' => $deviceId,
                    'product_id' => $latestReceiptInfo->product_id,
                    'environment' => $data->environment,
                    'original_transaction_id' => $data->pending_renewal_info[0]->original_transaction_id,
                    'type' => $this->defineType($data),
                    'start_date' => $latestReceiptInfo->purchase_date_ms,
                    'end_date' => $latestReceiptInfo->expires_date_ms,
                    'latest_receipt' => $data->latest_receipt
                ]

            );

            $this->saveSubscriptionHistory($subscription, $latestReceiptInfo->transaction_id);

           // DB::commit();

            return $subscription;
//        } catch (\Exception $e) {
//            \Log::error('ERROR SAVE SUBSCRIPTION : ' . $e->getMessage());
//            DB::rollBack();
//        }

    }


    public function saveSubscriptionHistory(Subscription $subscription, $transactionId)
    {
        $subscriptionHistory = SubscriptionHistory::where('transaction_id', $transactionId)->first();

        if (is_null($subscriptionHistory)) {
            $subscriptionHistory = SubscriptionHistory::create([
                'id' => Str::uuid(),
                'subscription_id' => $subscription->id,
                'product_id' => $subscription->product_id,
                'environment' => $subscription->environment,
                'start_date' => $subscription->start_date,
                'end_date' => $subscription->end_date,
                'type' => $subscription->type,
                'transaction_id' => $transactionId
            ]);

            $count = SubscriptionHistory::where('product_id', $subscription->product_id)
                ->where('subscription_id', $subscription->id)->count();

            SubscriptionHistory::where('id', $subscriptionHistory->id)
                ->update([
                    'count' => $count
                ]);


        }
    }


    private function sortLatestReceiptInfo($latestReceiptInfo) : array
    {
        $collect = collect($latestReceiptInfo);

        return $collect->sortBy('purchase_date_ms')->toArray();
    }

    private function defineType($data)
    {
        if (isset($data->pending_renewal_info) && $data->pending_renewal_info == 1 ) {
            return Subscription::TYPE_CANCEL;
        }

        $receiptInfo = $this->sortLatestReceiptInfo($data->latest_receipt_info);

        $latestReceiptInfo = end($receiptInfo);

        $countReceiptInfo = count($receiptInfo);

        if ($latestReceiptInfo->is_trial_period == true) {
            return Subscription::TYPE_TRIAL;
        }

        if ($countReceiptInfo == 2 && !isset($data->pending_renewal_info)) {
            return Subscription::TYPE_INITIAL_BUY;
        }

        return Subscription::TYPE_RENEWAL;
    }


}