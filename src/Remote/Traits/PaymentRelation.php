<?php

namespace ZhuiTech\BootLaravel\Remote\Traits;

use ZhuiTech\BootLaravel\Remote\Service\PayPayment;

/**
 * Trait PaymentRelation
 * @package ZhuiTech\BootLaravel\Remote\Traits
 * @property \Illuminate\Support\Collection|PayPayment[] $payments
 * @property \Illuminate\Support\Collection|PayPayment[] $success_payments
 * @property int $paid_amount
 */
trait PaymentRelation
{
    public static $order_type = 'order';

    /**
     * @return \Illuminate\Support\Collection|\ZhuiTech\BootLaravel\Remote\Service\PayPayment[]
     */
    public function getPaymentsAttribute()
    {
        return PayPayment::where('order_type', static::$order_type)->where('order_id', $this->id)->get();
    }

    /**
     * @return \Illuminate\Support\Collection|\ZhuiTech\BootLaravel\Remote\Service\PayPayment[]
     */
    public function getSuccessPaymentsAttribute()
    {
        return PayPayment::where('order_type', static::$order_type)->where('order_id', $this->id)
            ->where('status', PayPayment::STATUS_SUCCESS)->get();
    }

    /**
     * @return int
     */
    public function getPaidAmountAttribute()
    {
        return $this->success_payments->sum('pay_amount');
    }
}