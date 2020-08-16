<?php

namespace ZhuiTech\BootLaravel\Transformers;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use League\Fractal\TransformerAbstract;

/**
 * Class ModelTransformer
 * @package ZhuiTech\BootLaravel\Transformers
 */
class ModelTransformer extends TransformerAbstract
{
	protected $excepts = [];

	protected $only = [];

	protected $casts = [];

	protected $appends = [];

	/**
	 * 默认排除项
	 *
	 * @return array
	 */
	protected function defaultExcepts()
	{
		return ['deleted_at'];
	}

	/**
	 * 转换
	 *
	 * @param $data
	 * @return array
	 */
	public function transform($data)
	{
		$result = [];
		if (method_exists($this, 'execTransform')) {
			$result = call_user_func(array($this, 'execTransform'), $data);
		} elseif ($data instanceof Arrayable) {
			$result = $data->toArray();
		} elseif (is_array($data)) {
			$result = $data;
		} elseif (is_object($data)) {
			$result = (array)$data;
		}

		// 转换字段
		foreach ($this->casts as $field => $caster_setting) {
			// 支持管道
			$casters = explode('|', $caster_setting);

			if (isset($result[$field])) {
				// 获取原始值
				$value = $result[$field];

				// 遍历管道
				foreach ($casters as $caster) {
					$caster_items = explode(':', $caster);

					// 简单转换函数
					if (count($caster_items) == 1) {
						$func = $caster_items[0];
						$value = $func($value);
					}

					// 嵌套转换函数
					if (count($caster_items) == 2) {
						$func = $caster_items[0];
						$value = $func($caster_items[1], $value);
					}
				}

				// 设置处理后的结果
				$result[$field] = $value;
			}
		}

		// 筛选字段
		if (!empty($this->only)) {
			$result = array_only($result, $this->only);
		} else {
			$excepts = array_merge($this->excepts, $this->defaultExcepts());
			$result = array_except($result, $excepts);
		}

		foreach ($this->appends as $field) {
			$result[$field] = $data->$field;
		}

		// 统一NULL为空字符串
		foreach (array_keys($result) as $key) {
			if (is_null($result[$key])) {
				$result[$key] = '';
			}
		}

		return $result;
	}

	protected function includeItem($data, TransformerAbstract $transformer = NULL)
	{
		if (!empty($data)) {
			if (empty($transformer)) {
				$class = self::defaultTransformer($data);
				$transformer = new $class;
			}

			return $this->item($data, $transformer);
		}
	}

	protected function includeCollection($data, TransformerAbstract $transformer = NULL)
	{
		if (!empty($data)) {
			if (empty($transformer)) {
				$item = [];
				if (is_array($data)) {
					$item = array_first($data);
				} elseif ($data instanceof Collection) {
					$item = $data->first();
				}

				$class = self::defaultTransformer($item);
				$transformer = new $class;
			}

			return $this->collection($data, $transformer);
		}
	}

	/**
	 * 获取默认转化器
	 *
	 * @param $data
	 * @param string $type 转化器类型
	 * @return string
	 */
	public static function defaultTransformer($data, $type = '')
	{
		if (is_object($data)) {
			$class = get_class($data);

			// Model类指定的默认转化器
			if (property_exists($class, 'defaultTransformer')) {
				return $class::$defaultTransformer;
			}

			// 根据命名规则找到默认转化器
			$transformerClass = Str::replaceFirst('Models', 'Transformers', $class) . ucwords($type) . 'Transformer';
			if (class_exists($transformerClass)) {
				return $transformerClass;
			}
		}

		// 通用转化器
		return self::class;
	}
}