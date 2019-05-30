<?php
namespace ZhuiTech\BootLaravel\Repositories;

use Bosnadev\Repositories\Eloquent\Repository;
use Illuminate\Container\Container as App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Support\Collection;
use ZhuiTech\BootLaravel\Exceptions\RestCodeException;
use ZhuiTech\BootLaravel\Exceptions\RestFailException;

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
     * 重新设置查询范围
     * @return $this
     * @throws \Bosnadev\Repositories\Exceptions\RepositoryException
     */
    public function resetScope()
    {
        $this->makeModel();
        parent::resetScope();
        return $this;
    }

    /**
     * 获取空白Model
     * @return \Eloquent
     */
    public function newModel()
    {
        return $this->newModel;
    }
    
    /**
     * 组合查询
     * @param $query
     * @return mixed
     */
    public function query($query)
    {
        $query += [
            '_order' => ['id' => 'desc']
        ];
        $this->pushCriteria(new QueryCriteria($query));

        $size = $query['_size'] ?? 25;
        $limit = $query['_limit'] ?? null;
        
        $columns = isset($query['_column'])
            ? explode(',', $query['_column'])
            : [$this->newModel()->getTable() . '.*'];

        $result = $limit
            ? $this->all($columns)
            : $this->paginate($size, $columns);

        return $result;
    }

    /**
     * Join parent
     * @param Model $model
     * @param $foreignKey
     * @return $this
     */
    public function joinParent(Model $model, $foreignKey = NULL)
    {
        $foreignKey = $foreignKey ?? $model->getForeignKey();

        $this->model = $this->model
            ->join($model->getTable(), $model->getQualifiedKeyName(), '=', $this->model->getTable() . '.' . $foreignKey);
        return $this;
    }

    /**
     * Join parent
     * @param Model $model
     * @param $foreignKey
     * @return $this
     */
    public function joinChildren(Model $model, $foreignKey = NULL)
    {
        $foreignKey = $foreignKey ?? $model->getForeignKey();

        $this->model = $this->model
            ->join($model->getTable(), $this->model->getQualifiedKeyName(), '=', $model->getTable() . '.' . $foreignKey);
        return $this;
    }

    /**
     * Join
     * @param $relationName
     * @return $this
     */
    public function join($relationName)
    {
        if (!method_exists($this->newModel, $relationName)) {
            throw new RestFailException(sprintf('关系 %s 不存在', $relationName));
        }

        $relation = $this->newModel->$relationName();

        if ($relation instanceof BelongsTo) {
            $this->model = $this->model->join(
                $relation->getModel()->getTable(),
                $relation->getQualifiedOwnerKeyName(), '=', $relation->getQualifiedForeignKey());
        }
        elseif ($relation instanceof HasOneOrMany) {
            $this->model = $this->model->join(
                $relation->getModel()->getTable(),
                $relation->getQualifiedParentKeyName(), '=', $relation->getQualifiedForeignKeyName());
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

    /**
     * @param int $perPage
     * @param array $columns
     * @return mixed
     */
    public function paginate($perPage = 25, $columns = array('*'))
    {
        $this->applyCriteria();
        return $this->model->paginate($perPage, $columns, '_page');
    }
}