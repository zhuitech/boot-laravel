<?php

namespace ZhuiTech\BootLaravel\Remote\Traits;

use ZhuiTech\BootLaravel\Remote\Service\PayPayment;

/**
 * Trait PaymentRelation
 * @package ZhuiTech\BootLaravel\Remote\Traits
 * @property \Illuminate\Support\Collection|PayPayment[] $payments 
 */
trait PaymentRelation
{
    public static $order_type = 'order';

    /**
     * @return \Illuminate\Support\Collection|PayPayment[]
     */
    public function getPaymentsAttribute()
    {
        return PayPayment::where('order_type', static::$order_type)->where('order_id', $this->id)->get();
    }
}