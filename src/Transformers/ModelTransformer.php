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
     * @param Model $model
     * @return array
     */
    public function transform(Model $model)
    {
        $result = $model->toArray();

        if (!empty($this->only)) {
            $result = array_only($result, $this->only);
        } else {
            $result = array_except($result, $this->excepts);
        }

        return $result;
    }
}