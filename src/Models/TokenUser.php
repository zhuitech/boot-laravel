<?php
/**
 * Created by PhpStorm.
 * User: andrewzhou
 * Date: 2018/5/9
 * Time: 下午12:55
 */

namespace ZhuiTech\BootLaravel\Models;

use Laravel\Passport\HasApiTokens;

class TokenUser extends User
{
    use HasApiTokens;
}