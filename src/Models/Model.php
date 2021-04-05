<?php

namespace ZhuiTech\BootLaravel\Models;

use Closure;

/**
 * Class Model
 * @package ZhuiTech\BootLaravel\Models
 *
 */
class Model extends \Illuminate\Database\Eloquent\Model
{
	/**
	 * 是否存在关系
	 * @param $object
	 * @param $method
	 * @return bool
	 */
	public static function relationExists($object, $key)
	{
		if (method_exists($object, $key) ||
			(static::$relationResolvers[get_class($object)][$key] ?? null)) {
			return true;
		}
	}
}