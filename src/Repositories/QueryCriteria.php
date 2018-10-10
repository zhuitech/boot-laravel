<?php
/**
 * Created by PhpStorm.
 * User: andrew
 * Date: 2018/3/6
 * Time: 13:44
 */

namespace ZhuiTech\LaraBoot\Repositories;

use Bosnadev\Repositories\Criteria\Criteria;
use Illuminate\Database\Eloquent\Model;
use Bosnadev\Repositories\Contracts\RepositoryInterface;

/**
 * Class QueryCriteria
 * @package ZhuiTech\LaraBoot\Repositories
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
        $orders = $this->query['order'] ?? [];
        $or = $this->query['or'] ?? false;
        $boolean = $or ? 'or' : 'and';
        $limit = $this->query['limit'] ?? null;
        $wheres = array_except($this->query, ['order', 'or', 'limit', 'page', 'per_page', 'columns']);

        // Where
        foreach ($wheres as $field => $value) {
            $field = $this->convert($field);
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
                $field = $this->convert($field);
                $model = $model->orderBy($field, $order);
            }
        }

        // Limit
        if (!empty($limit)) {
            $model = $model->take($limit);
        }

        return $model;
    }

    private function convert($field)
    {
        return str_replace('!', '.', $field);
    }
}