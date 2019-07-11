<?php
namespace ZhuiTech\BootLaravel\Repositories;

use Bosnadev\Repositories\Eloquent\Repository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
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
     *
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
     *
     * @return \Eloquent
     */
    public function newModel()
    {
        return $this->newModel;
    }

    /**
     * 设置范围
     *
     * @param $query
     * @return $this
     */
    public function scope($query)
    {
        $query += [
            '_order' => ['id' => 'desc']
        ];

        $this->pushCriteria(new QueryCriteria($query));
        return $this;
    }

    /**
     * Only Trashed
     *
     * @return $this
     */
    public function onlyTrashed()
    {
        $this->model = $this->model->onlyTrashed();
        return $this;
    }

    /**
     * @deprecated
     * Join parent
     *
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
     * @deprecated
     * Join parent
     *
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
     * Join relationship table
     *
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
     * 分页查询
     *
     * @param int $perPage
     * @param array $columns
     * @return mixed
     */
    public function paginate($perPage = 25, $columns = array('*'))
    {
        $this->applyCriteria();

        return $this->model->paginate($perPage, $columns, request()->has('_page') ? '_page' : 'page');
    }

    /**
     * 统计总数
     *
     * @return int
     */
    public function count()
    {
        $this->applyCriteria();
        return $this->model->count();
    }

    /**
     * 获取结果
     *
     * @param $options
     * @return mixed
     */
    public function get($options)
    {
        $size = $options['_size'] ?? 25;
        $limit = $options['_limit'] ?? null;

        $columns = isset($options['_column'])
            ? explode(',', $options['_column'])
            : [$this->newModel()->getTable() . '.*'];

        $result = $limit
            ? $this->all($columns)
            : $this->paginate($size, $columns);

        return $result;
    }

    /**
     * 快捷查询，使用默认scope方法
     *
     * @param $query
     * @return mixed
     */
    public function query($query)
    {
        return $this->scope($query)->get($query);
    }
}