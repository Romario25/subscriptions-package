<?php
namespace Romario25\Subscriptions\DTO;


use Illuminate\Support\Str;
use Romario25\Subscriptions\Entities\Subscription;

class SubscriptionHistoryDto
{

    public $id;

    public $subscription;

    public $transactionId;

    /**
     * SubscriptionHistoryDto constructor.
     * @param $subscription
     * @param $transactionId
     */
    public function __construct(Subscription $subscription, $transactionId)
    {
        $this->subscription = $subscription;
        $this->transactionId = $transactionId;

        $this->id = Str::uuid();
    }


}