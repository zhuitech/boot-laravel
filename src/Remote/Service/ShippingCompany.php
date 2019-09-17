<?php

namespace ZhuiTech\BootLaravel\Remote\Service;

use ZhuiTech\BootLaravel\Remote\Model;

/**
 * ZhuiTech\BootLaravel\Remote\Service\ShippingCompany
 *
 * @property int $id
 * @property string $code 编码
 * @property string $name 名称
 * @property int $status 状态：0=禁用 1=启用
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @deprecated 
 */
class ShippingCompany extends LogisticsCompany
{
}