<?php

namespace ZhuiTech\BootLaravel\Remote\Service;

use ZhuiTech\BootLaravel\Exceptions\RestFailException;
use ZhuiTech\BootLaravel\Helpers\RestClient;
use ZhuiTech\BootLaravel\Remote\Model;

/**
 * Class PayOrder
 * @package ZhuiTech\BootLaravel\Remote\Service
 *
 * @property int $id
 * @property int|null $status 状态：0=待支付，1=已支付，2=部分支付，3=已退款
 * @property int $user_id 用户ID
 * @property string $order_type 订单类型
 * @property string $order_no 订单编号
 * @property int $order_amount 订单金额
 * @property string|null $subject 标题
 * @property string|null $body 描述
 * @property string|null $notify_url 异步通知
 * @property string|null $return_url 同步通知
 * @property string|null $paid_at 付款时间
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $paid_amount
 * 
 */
class PayOrder extends Model
{
    protected $cache = false;

    const STATUS_WAIT = 0;
    const STATUS_PAID = 1;
    const STATUS_PARTPAID = 2;
    const STATUS_REFUND = 3;

    public static $statusOptions = [
        self::STATUS_WAIT => '待支付',
        self::STATUS_PAID => '已支付',
        self::STATUS_PARTPAID => '部分支付',
        self::STATUS_REFUND => '已退款',
    ];
    
    protected $server = 'service';
    protected $resource = 'api/svc/pay/orders';

    public function charge($channel, $method, $options = null)
    {
        $result = RestClient::server($this->server)->post("api/svc/pay/orders/{$this->id}/charge", compact('channel', 'method', 'options'));
        if ($result['status'] === true) {
            return $result['data'];
        } else {
            throw new RestFailException($result['message'], $result);
        }
    }
}