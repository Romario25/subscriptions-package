<?php
namespace Romario25\Subscriptions;


class SubscriptionsService
{
    protected $property = 'test_property';
    protected $value;

    /**
     * SubscriptionService constructor.
     * @param string $value
     */
    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public function getPropertyValue()
    {
        return $this->property . ' ' . $this->value;
    }


}