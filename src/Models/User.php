<?php

namespace TrackHub\Laraboot\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;
use TrackHub\Laraboot\Helpers\RestClient;
use TrackHub\Laraboot\Exceptions\RestCodeException;

/**
 * Class Member
 * @package TrackHub\Laraboot\Models
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

        // 查询用户
        $client = new RestClient();
        $url = $this->isAdmin()
            ? service_url('auth', 'api/users/' . $this->id)
            : service_url('member', 'api/members/' . $this->id);
        $result = $client->get($url);

        if (!$result['status']) {
            throw new RestCodeException(REST_FAIL, '用户不存在');
        }

        $this->data = $result['data'];
    }
}
