<?php
namespace ZhuiTech\BootLaravel\Transformers;

use Illuminate\Database\Eloquent\Model;
use League\Fractal\TransformerAbstract;

/**
 * Class ModelTransformer
 * @package ZhuiTech\BootLaravel\Transformers
 */
class ModelTransformer extends TransformerAbstract
{
    protected $excepts = [];

    protected $only = [];

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
     * 转换模型
     *
     * @param Model $model
     * @return array
     */
    public function transform(Model $model)
    {
        if (method_exists($this, 'execTransform')) {
            $result = call_user_func(array($this, 'execTransform'), $model);
        } else {
            $result = $model->toArray();
        }

        if (!empty($this->only)) {
            $result = array_only($result, $this->only);
        } else {
            $excepts = array_merge($this->excepts, $this->defaultExcepts());
            $result = array_except($result, $excepts);
        }

        // 转换 null 字段为空字符串
        foreach (array_keys($result) as $key) {
            if (is_null($result[$key])) {
                $result[$key] = '';
            }
        }

        return $result;
    }
}