<?php
/**
 * Created by PhpStorm.
 * User: andrew
 * Date: 2018/3/8
 * Time: 12:11
 */

namespace ZhuiTech\BootLaravel\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use ZhuiTech\BootLaravel\Exceptions\RestCodeException;
use ZhuiTech\BootLaravel\Repositories\BaseRepository;
use ZhuiTech\BootLaravel\Repositories\SimpleCriteria;
use Illuminate\Support\Str;

/**
 * BaseController for sub-resources
 * Class SubRestController
 * @package ZhuiTech\BootLaravel\Controllers
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
     * @param BaseRepository $repository
     * @param BaseRepository $parents
     */
    public function __construct(BaseRepository $repository, BaseRepository $parents)
    {
        $this->parents = $parents;
        $this->foreignKey = $parents->freshModel()->getForeignKey();

        parent::__construct($repository);
    }

    protected function parentKey()
    {
        return Str::snake(class_basename($this->parents->model()));
    }

    protected function resourceKey()
    {
        return Str::snake(class_basename($this->repository->model()));
    }

    /**
     * 初始化
     */
    protected function prepare()
    {
        $params = request()->route()->parameters();
        $parentId = $params[$this->parentKey()];
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

        parent::prepare();
    }

    public function show($id)
    {
        $params = request()->route()->parameters();
        $id = $params[$this->resourceKey()];
        return parent::show($id);
    }

    public function update($id)
    {
        $params = request()->route()->parameters();
        $id = $params[$this->resourceKey()];
        return parent::update($id);
    }

    public function destroy($id)
    {
        $params = request()->route()->parameters();
        $id = $params[$this->resourceKey()];
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