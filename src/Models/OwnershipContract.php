<?php
/**
 * Created by PhpStorm.
 * User: andrew
 * Date: 2018/3/3
 * Time: 16:31
 */

namespace TrackHub\Laraboot\Models;

/**
 * Interface MemberOwnershipContract
 * @package TrackHub\Laraboot\Models
 */
interface OwnershipContract
{
    /**
     * 可否分配用户
     * @param User $user
     * @return mixed
     */
    public function assignable(User $user);

    /**
     * 分配给用户
     * @param User $user
     * @return mixed
     */
    public function assign(User $user, &$data = NULL);

    /**
     * 是否属于用户
     * @param User $user
     * @return mixed
     */
    public function belong(User $user);

    /**
     * 拥有者外键
     * @return mixed
     */
    public function ownerKey();
}