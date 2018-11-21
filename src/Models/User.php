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
    
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * 是会员
     * @return bool
     */
    public function isMember()
    {
        return $this->type == 'members';
    }

    /**
     * 是管理员
     * @return bool
     */
    public function isAdmin()
    {
        return $this->type == 'admins';
    }

    /**
     * 加载用户数据
     */
    public function loadData()
    {
        if (!empty($this->data)) {
            return;
        }

        // 请求后端服务
        if ($this->isAdmin()) {
            $result = RestClient::server('admin')->get("api/admin/users/{$this->id}");
        }
        else {
            $result = RestClient::server('member')->get("api/member/users/{$this->id}");
        }

        // 处理返回结果
        $this->data = Restful::handle($result,
            function ($data, $message, $code) {
                // success
                return $data;
            });
    }
}
