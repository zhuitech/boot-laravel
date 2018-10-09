<?php
/**
 * Created by PhpStorm.
 * User: andrew
 * Date: 2018/3/8
 * Time: 12:11
 */

namespace TrackHub\Laraboot\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use TrackHub\Laraboot\Exceptions\RestCodeException;
use TrackHub\Laraboot\Repositories\BaseRepository;
use TrackHub\Laraboot\Repositories\SimpleCriteria;
use Illuminate\Support\Str;

/**
 * BaseController for sub-resources
 * Class SubRestController
 * @package TrackHub\Laraboot\Controllers
 */
abstract class SubRestController extends RestController
{
    /**
     * 父资源仓库
     * @var BaseRepository
     */
    protected $parents;

    /**
     * 外键
     * @var
     */
    protected $foreignKey;

    /**
     * 父资源
     * @var Model
     */
    protected $parent;

    /**
     * SubRestController constructor.
     * @param BaseRepository $parents
     */
    public function __construct(BaseRepository $repository, BaseRepository $parents)
    {
        parent::__construct($repository);

        $this->parents = $parents;
        $this->foreignKey = $parents->freshModel()->getForeignKey();

        // 如果当前为子路由，添加默认条件
        $parentId = $this->resolveParameter(request(), $this->parents->model());
        if ($parentId) {
            // 设置查询范围
            $criteria = new SimpleCriteria([
                $this->foreignKey => $parentId
            ]);
            $this->repository->pushCriteria($criteria);

            // 加载父资源
            $parent = $this->parents->find($parentId);
            if (empty($parent)) {
                throw new RestCodeException(REST_OBJ_NOT_EXIST);
            }
            $this->parent = $parent;

            // 添加父资源关联字段
            request()->merge([
                $this->foreignKey => (int)$parentId
            ]);
        }
    }

    /**
     * 获取模型对应的参数
     * @param Request $request
     * @param $class
     * @return null
     */
    private function resolveParameter(Request $request, $class)
    {
        $params = $request->route()->parameters();
        $key = Str::snake(class_basename($class));
        return $params[$key] ?? null;
    }

    public function show($id)
    {
        $id = $this->resolveParameter(request(), $this->repository->model());
        return parent::show($id);
    }

    public function update($id)
    {
        $id = $this->resolveParameter(request(), $this->repository->model());
        return parent::update($id);
    }

    public function destroy($id)
    {
        $id = $this->resolveParameter(request(), $this->repository->model());
        return parent::destroy($id);
    }

    /**
     * 关联父表查询
     * @return mixed
     * @throws \Bosnadev\Repositories\Exceptions\RepositoryException
     */
    public function find()
    {
        $parent = $this->parents->freshModel();
        
        $result = $this->repository
            ->join($parent)
            ->query(request()->all());
        
        return $this->success($result);
    }
}