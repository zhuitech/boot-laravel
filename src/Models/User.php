<?php

namespace ZhuiTech\BootLaravel\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Class User
 * @package ZhuiTech\BootLaravel\Models
 *
 * @property int $id 用户ID
 * @property string|null $type 类型
 *
 */
class User extends Authenticatable implements UserContract
{
	protected $fillable = ['id'];

	function getAuthId()
	{
		return $this->id;
	}

	function getAuthType()
	{
		return $this->type;
	}
}
