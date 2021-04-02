<?php

namespace ZhuiTech\BootLaravel\Services;

use Illuminate\Database\Eloquent\Relations\Relation;

class BootLaravel
{
	protected static $_morphOptions = [];

	/**
	 * 获取类选项
	 *
	 * @param array $keys
	 * @return array
	 */
	public static function morphOptions($keys = [])
	{
		if (!empty(self::$_morphOptions)) {
			return self::$_morphOptions;
		}

		$map = Relation::$morphMap;
		$options = [];
		foreach ($map as $alias => $class_name) {
			if (empty($keys) || in_array($alias, $keys)) {
				$options[$alias] = $alias;
				if (property_exists($class_name, 'class_title')) {
					$options[$alias] = $class_name::$class_title;
				}
			}
		}

		self::$_morphOptions = $options;
		return self::$_morphOptions;
	}

	/**
	 * 获取类别名
	 *
	 * @param $class
	 * @return string
	 */
	public static function morphAlias($class)
	{
		$map = Relation::$morphMap;
		foreach ($map as $alias => $class_name) {
			if ($class == $class_name) {
				return $alias;
			}
		}
		return $class;
	}

	/**
	 * 获取类标题
	 *
	 * @param $alias
	 * @return mixed|string
	 */
	public static function morphTitle($alias)
	{
		$options = self::morphOptions();
		return $options[$alias] ?? '';
	}
}