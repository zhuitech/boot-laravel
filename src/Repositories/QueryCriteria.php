<?php
/**
 * Created by PhpStorm.
 * User: andrew
 * Date: 2018/3/6
 * Time: 13:44
 */

namespace ZhuiTech\BootLaravel\Repositories;

use Bosnadev\Repositories\Criteria\Criteria;
use Illuminate\Database\Eloquent\Model;
use Bosnadev\Repositories\Contracts\RepositoryInterface;
use Illuminate\Database\Eloquent\Relations\Relation;
use ZhuiTech\BootLaravel\Exceptions\RestFailException;

/**
 * Class QueryCriteria
 * @package ZhuiTech\BootLaravel\Repositories
 */
class QueryCriteria extends Criteria
{
    protected $query;

    /**
     * QueryStringCriteria constructor.
     */
    public function __construct($query)
    {
        $this->query = $query;
    }

    /**
     * @param Model $model
     * @param RepositoryInterface $repository
     * @return mixed
     */
    public function apply($model, RepositoryInterface $repository)
    {
        // 获取查询条件
        $orders = $this->query['_order'] ?? [];
        $or = $this->query['_or'] ?? false;
        $boolean = $or ? 'or' : 'and';
        $limit = $this->query['_limit'] ?? null;
        $wheres = array_except($this->query, ['_order', '_or', '_limit', '_size', '_column', 'page', '_page', '_include']);

        // Where
        foreach ($wheres as $field => $value) {
            $field = $this->qualified($field, $repository);
            if (is_array($value)) {
                foreach ($value as $operator => $search) {
                    if ($operator == 'null') {
                        $not = $search == '1' ? false : true;
                        $model = $model->whereNull($field, $boolean, $not);
                    }
                    else {
                        $model = $model->where($field, $operator, $search, $boolean);
                    }
                }
            } else {
                $model = $model->where($field, '=', $value, $boolean);
            }
        }

        // Order
        if (!empty($orders)){
            foreach ($orders as $field => $order) {
                $field = $this->qualified($field, $repository);
                $model = $model->orderBy($field, $order);
            }
        }

        // Limit
        if (!empty($limit)) {
            $model = $model->take($limit);
        }

        return $model;
    }

    private function qualified($field, RepositoryInterface $repository)
    {
        if ($repository instanceof BaseRepository) {
            $model = $repository->newModel();

            if (str_contains($field, '!')) {
                [$relationName, $field] = explode('!', $field);

                // 替换关联表名
                if (method_exists($model, $relationName)) {
                    $relation = $model->$relationName();

                    if ($relation instanceof Relation) {
                        $field = $relation->getModel()->getTable() . '!' . $field;
                    }
                }

                // 替换分隔符
                return str_replace('!', '.', $field);
            } else {
                // 添加主表名
                return $model->getTable() . '.' . $field;
            }
        }
    }
}