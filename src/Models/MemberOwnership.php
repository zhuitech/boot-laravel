<?php
/**
 * Created by PhpStorm.
 * User: andrew
 * Date: 2018/3/3
 * Time: 16:43
 */

namespace TrackHub\Laraboot\Models;

/**
 * Trait MemberOwnership
 * @package TrackHub\Laraboot\Models
 */
trait MemberOwnership
{
    /**
     * 可否分配用户
     * @param User $user
     * @return mixed
     */
    public function assignable(User $user)
    {
        return $user->type == 'members';
    }

    /**
     * 分配给用户
     * @param User $user
     * @return mixed
     */
    public function assign(User $user, &$data = NULL)
    {
        $user->loadData();

        if (empty($data)) {
            $this->{$this->ownerKey()} = $user->id;
            $this->member_nickname = $user->data['nickname'] ?? null;
        }
        else {
            $data[$this->ownerKey()] = $user->id;
            $data['member_nickname'] = $user->data['nickname'] ?? null;
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
        return 'member_id';
    }
}