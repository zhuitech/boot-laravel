<?php

namespace ZhuiTech\BootLaravel\Remote\Traits;

use ZhuiTech\BootLaravel\Remote\Service\PayOrder;

/**
 * Trait PayOrderRelation
 * @package ZhuiTech\BootLaravel\Remote\Traits
 */
trait PayOrderRelation
{
    public static $order_type = 'order';

    /**
     * @return \ZhuiTech\BootLaravel\Remote\Service\PayOrder|null
     */
    public function getRemoteOrder()
    {
        return PayOrder::where('order_type', static::$order_type)->where('order_no', $this->order_no)->first();
    }
}