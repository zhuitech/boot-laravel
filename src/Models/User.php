<?php

namespace ZhuiTech\BootLaravel\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;
use ZhuiTech\BootLaravel\Helpers\RestClient;
use ZhuiTech\BootLaravel\Exceptions\RestCodeException;
use ZhuiTech\BootLaravel\Helpers\Restful;

/**
 * Class Member
 * @package ZhuiTech\BootLaravel\Models
 * 
 * @property int $id
 * @property string|null $type 类型
 * @property array|null $scopes 授权范围
 * @property string|null $ip IP
 * @property array|null $data 数据
 *
 */
class User extends Authenticatable
{
    use Notifiable;
}
