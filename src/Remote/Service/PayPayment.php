<?php

namespace ZhuiTech\BootLaravel\Remote\Service;

use ZhuiTech\BootLaravel\Exceptions\RestFailException;
use ZhuiTech\BootLaravel\Helpers\RestClient;
use ZhuiTech\BootLaravel\Remote\Model;

/**
 * Class PayPayment
 * @package ZhuiTech\BootLaravel\Remote\Service
 * 
 * @property int $id
 * @property int $user_id 用户ID
 * @property int $order_id 业务ID
 * @property string $order_type 业务类型
 * @property string $order_no 业务编号
 * @property string|null $out_trade_no 交易编号
 * @property int $order_amount 订单金额
 * @property int $pay_amount 支付金额
 * @property int $actual_amount 实际支付金额
 * @property string|null $subject 标题
 * @property string|null $body 描述
 * @property string|null $callback 回调地址
 * @property int|null $status 状态：0=未支付，1=支付成功
 * @property int|null $channel_id 通道ID
 * @property string|null $transaction_no 交易流水号
 * @property mixed|null $result 结果
 * @property string|null $paid_at 付款时间
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * 
 */
class PayPayment extends Model
{
    const STATUS_WAIT = 0;
    const STATUS_SUCCESS = 1;

    public static $statusOptions = [
        self::STATUS_WAIT => '待支付',
        self::STATUS_SUCCESS => '已支付',
    ];
    
    protected $server = 'service';
    protected $resource = 'api/svc/pay/payments';

    public function charge($channel, $method, $options = null)
    {
        $result = RestClient::server($this->server)->post("api/svc/pay/payments/{$this->id}/charge", compact('channel', 'method', 'options'));
        if ($result['status'] === true) {
            return $result['data'];
        } else {
            throw new RestFailException($result['message'], $result);
        }
    }
}