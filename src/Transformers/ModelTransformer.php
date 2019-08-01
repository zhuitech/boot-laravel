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

    /**
     * 默认排除项
     *
     * @return array
     */
    protected function defaultExcepts()
    {
        return ['updated_at', 'deleted_at'];
    }

    /**
     * 转换
     *
     * @param $data
     * @return array
     */
    public function transform($data)
    {
        $result =[];
        if (method_exists($this, 'execTransform')) {
            $result = call_user_func(array($this, 'execTransform'), $data);
        } elseif ($data instanceof Arrayable) {
            $result = $data->toArray();
        } elseif (is_array($data)) {
            $result = $data;
        } elseif (is_object($data)) {
            $result = (array) $data;
        }

        // 转换字段
        foreach ($this->casts as $field => $caster) {
            if (isset($result[$field])) {
                $value = $result[$field];

                if (function_exists($caster)) {
                    $value = $caster($result[$field]);
                } elseif (method_exists($this, $caster)) {
                    $value = call_user_func(array($this, $caster), $result[$field]);
                }

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

    public static function defaultTransformer($data)
    {
        if (is_object($data)) {
            $class = get_class($data);

            // Model类指定的默认转化器
            if (property_exists($class, 'defaultTransformer')) {
                return $class::$defaultTransformer;
            }

            // 根据命名规则找到默认转化器
            $transformerClass = Str::replaceFirst('Models', 'Transformers', $class) . 'Transformer';
            if (class_exists($transformerClass)) {
                return $transformerClass;
            }
        }

        // 通用转化器
        return self::class;
    }
}