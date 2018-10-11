<?php
namespace ZhuiTech\BootLaravel\Repositories;

use Bosnadev\Repositories\Eloquent\Repository;
use Illuminate\Container\Container as App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * 仓库模式基类
 *
 * Class BaseRepository
 * @package ZhuiTech\BootLaravel\Repositories
 */
abstract class BaseRepository extends Repository
{
    /**
     * @var \Eloquent
     */
    protected $model;

    /**
     * 获取空白Model
     * @return \Eloquent
     */
    public function freshModel()
    {
        return app($this->model());
    }
    
    /**
     * 组合查询
     * @param $query
     * @return mixed
     */
    public function query($query)
    {
        $this->pushCriteria(new QueryCriteria($query));

        $size = $query['per_page'] ?? 25;
        $limit = $query['limit'] ?? null;
        
        $columns = isset($query['columns'])
            ? explode(',', $query['columns'])
            : [$this->freshModel()->getTable() . '.*'];

        $result = $limit
            ? $this->all($columns)
            : $this->paginate($size, $columns);

        return $result;
    }

    /**
     * Inner Join
     * @param $table
     * @param $foreignKey
     * @return $this
     */
    public function join(Model $model)
    {
        $foreignKey = $this->model->getTable() . '.' . $model->getForeignKey();
        $this->model = $this->model
            ->join($model->getTable(), $model->getQualifiedKeyName(), '=', $foreignKey);
        return $this;
    }

    /**
     * Left Join
     * @param Model $model
     * @return $this
     */
    public function leftJoin(Model $model, $reverse = false)
    {
        if ($reverse) {
            $this->model = $this->model->leftJoin(
                $model->getTable(),
                $this->model->getQualifiedKeyName(),
                '=',
                $model->getTable() . '.' . $this->model->getForeignKey()
            );
        }
        else {
            $this->model = $this->model->leftJoin(
                $model->getTable(),
                $this->model->getTable() . '.' . $model->getForeignKey(),
                '=',
                $model->getQualifiedKeyName()
            );
        }

        return $this;
    }

    /**
     * Only Trashed
     * @return $this
     */
    public function onlyTrashed()
    {
        $this->model = $this->model->onlyTrashed();
        return $this;
    }
}