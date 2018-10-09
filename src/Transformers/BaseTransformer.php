<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016-08-23
 * Time: 13:30
 */

namespace TrackHub\Laraboot\Transformers;

use Illuminate\Database\Eloquent\Model;
use League\Fractal\TransformerAbstract;

/**
 * 基础模型转换器
 *
 * Class BaseTransformer
 * @package TrackHub\Laraboot\Transformers
 */
class BaseTransformer extends TransformerAbstract
{
    /**
     * @var array 要排除的字段
     */
    protected $excludes = [];

    public function transform(Model $model)
    {
        $data = $model->toArray();

        if (!empty($this->excludes)) {
            $data = array_except($data, $this->excludes);
        }

        return $data;
    }
}