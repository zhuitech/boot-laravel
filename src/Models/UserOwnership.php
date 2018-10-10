<?php
/**
 * Created by PhpStorm.
 * User: andrew
 * Date: 2018/3/3
 * Time: 16:43
 */

namespace ZhuiTech\LaraBoot\Models;

/**
 * Trait MemberOwnership
 * @package ZhuiTech\LaraBoot\Models
 */
trait UserOwnership
{
    /**
     * 可否分配用户
     * @param User $user
     * @return mixed
     */
    public function assignable(User $user)
    {
        return $user->type == 'admins';
    }

    /**
     * 分配给用户
     * @param User $user
     * @return mixed
     */
    public function assign(User $user, &$data = NULL)
    {
        if (empty($data)) {
            $this->{$this->ownerKey()} = $user->id;
        }
        else {
            $data[$this->ownerKey()] = $user->id;
        }
    }

    /**
     * 是否属于用户
     * @param User $user
     * @return mixed
     */
    public function belong(User $user)
    {
        return $this->assignable($user) && $this->{$this->ownerKey()} == $user->id;
    }

    /**
     * 拥有者外键
     * @return mixed
     */
    public function ownerKey()
    {
        return 'user_id';
    }
}