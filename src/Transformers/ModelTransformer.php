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
    protected $excepts = ['created_at', 'updated_at', 'deleted_at'];

    protected $only = [];

    /**
     * 转换模型
     *
     * @param Model $model
     * @return array
     */
    public function transform(Model $model)
    {
        $result = $this->execTransform($model);

        if (!empty($this->only)) {
            $result = array_only($result, $this->only);
        } else {
            $result = array_except($result, $this->excepts);
        }

        // 转换 null 字段为空字符串
        foreach (array_keys($result) as $key) {
            if (is_null($result[$key])) {
                $result[$key] = '';
            }
        }

        return $result;
    }

    /**
     * 执行转换
     *
     * @param Model $model
     * @return array
     */
    protected function execTransform(Model $model)
    {
        $result = $model->toArray();
        return $result;
    }
}