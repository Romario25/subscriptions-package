<?php
namespace Romario25\Subscriptions\Entities;


use Illuminate\Database\Eloquent\Model;

class SubscriptionIDFA extends Model
{
    protected $primaryKey = false;

    public $incrementing = false;

    public $guarded = [];

    protected $table = 'subscriptions_idfa';


}