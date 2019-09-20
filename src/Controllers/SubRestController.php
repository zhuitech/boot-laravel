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
use Illuminate\Support\Arr;
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
     * 父资源
     * @var Model
     */
    protected $parent;

    /**
     * 父模型类
     * @var 
     */
    protected $parentModel;

    /**
     * 外键
     * @var
     */
    protected $foreignKey;

    /**
     * SubRestController constructor.
     * @param BaseRepository $repository
     * @param BaseRepository $parents
     * @throws \Bosnadev\Repositories\Exceptions\RepositoryException
     */
    public function __construct(BaseRepository $repository, BaseRepository $parents)
    {
        $this->parents = $parents;
        
        if (empty($this->parents->model())) {
            $this->parents->setModel($this->parentModel);
        }
        
        if (empty($this->foreignKey)) {
            $this->foreignKey = $this->parents->newModel()->getForeignKey();
        }

        parent::__construct($repository);
    }

    /**
     * 获取父主键
     * 
     * @return mixed
     */
    protected function parentKey()
    {
        return Arr::first(request()->route()->parameters());
    }

    /**
     * 获取主键
     * 
     * @return mixed
     */
    protected function key()
    {
        return Arr::last(request()->route()->parameters());
    }

    /**
     * 初始化
     */
    protected function prepare()
    {
        $parentId = $this->parentKey();
        
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
        $id = $this->key();
        return parent::show($id);
    }

    public function update($id)
    {
        $id = $this->key();
        return parent::update($id);
    }

    public function destroy($id)
    {
        $id = $this->key();
        return parent::destroy($id);
    }

    /**
     * 关联父表查询
     * @return mixed
     * @throws \Bosnadev\Repositories\Exceptions\RepositoryException
     */
    public function find()
    {
        $parent = $this->parents->newModel();
        $result = $this->repository->join($parent)->query(request()->all());
        return $this->success($result);
    }
}