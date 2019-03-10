<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016-08-19
 * Time: 12:31
 */

namespace ZhuiTech\BootLaravel\Controllers;

use Bosnadev\Repositories\Eloquent\Repository;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use ZhuiTech\BootLaravel\Exceptions\RestCodeException;
use ZhuiTech\BootLaravel\Models\MemberOwnershipContract;
use ZhuiTech\BootLaravel\Models\OwnershipContract;
use ZhuiTech\BootLaravel\Repositories\BaseRepository;
use ZhuiTech\BootLaravel\Repositories\QueryCriteria;

/**
 * Base class for restfull api.
 *
 * Class RestController
 * @package ZhuiTech\BootLaravel\Controllers
 */
abstract class RestController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests, RestResponse;

    /**
     * 资源仓库
     * @var BaseRepository
     */
    protected $repository;

    /**
     * 表单请求类
     * @var string
     */
    protected $formClass = Request::class;

    /**
     * RestController constructor.
     * @param BaseRepository $repository
     */
    public function __construct(BaseRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * 获取表单请求
     * @return FormRequest
     */
    protected function form()
    {
        return resolve($this->formClass);
    }

    /**
     * 查询对象，没有就抛出异常
     * @param $id
     * @return mixed
     */
    protected function findOrThrow($id)
    {
        // 找一下
        $result = $this->repository->find($id);

        // 找不到
        if (empty($result)) {
            throw new RestCodeException(REST_OBJ_NOT_EXIST);
        }

        return $result;
    }

    /**
     * 返回客户端的语言环境
     * @return array|string
     */
    protected function clientLanguage()
    {
        return request()->header('X-Language');
    }

    // CRUD ************************************************************************************************************

    /**
     * Retrive a list of objects
     * GET	    /photos
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $data = request()->all();

        $result = $this->execIndex($data);

        return $this->success($result);
    }

    protected function execIndex($data)
    {
        $result = $this->repository->query($data);
        return $result;
    }

    /**
     * Show an object
     * GET	/photos/{photo}
     *
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        // 找一下
        $result = $this->execShow($id);

        // 找到了
        return self::success($result);
    }

    protected function execShow($id)
    {
        return $this->findOrThrow($id);
    }

    /**
     * Save a new object
     * POST	/photos
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store()
    {
        $data = $this->form()->all();

        $result = $this->execStore($data);

        // 创建失败
        if (empty($result)) {
            return $this->error(REST_OBJ_CREATE_FAIL);
        }

        // 成功了
        return self::success($result);
    }

    protected function execStore($data)
    {
        $result = $this->repository->create($data);
        return $result;
    }

    /**
     * Update an exists object
     * PUT	/photos/{photo}
     *
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($id)
    {
        $data = $this->form()->all();

        // 找一下
        $model = $this->findOrThrow($id);

        // 更新
        $result = $this->execUpdate($model, $data);

        // 创建失败
        if (empty($result)) {
            return $this->error(REST_OBJ_UPDATE_FAIL);
        }

        // 成功了
        $model = $this->findOrThrow($id);
        return self::success($model);
    }

    protected function execUpdate($model, $data)
    {
        $result = $model->fill($data)->save();
        return $result;
    }

    /**
     * Delete an object
     * DELETE	/photos/{photo}
     *
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        // 找一下
        $model = $this->findOrThrow($id);

        $result = $this->execDestroy($model);

        // 失败了
        if (empty($result)) {
            return $this->error(REST_OBJ_DELETE_FAIL);
        }

        // 成功了
        return self::success($result);
    }

    protected function execDestroy($model)
    {
        $result = $model->delete();
        return $result;
    }

    // Find ************************************************************************************************************

    /**
     * Find object by field
     *
     * @param $field
     * @param $value
     * @return \Illuminate\Http\JsonResponse
     */
    public function findBy($field, $value)
    {
        $data = request()->all();

        // 找一下
        $result = $this->execFindBy($field, $value, $data);

        // 找不到
        if (empty($result)) {
            return $this->error(REST_OBJ_NOT_EXIST);
        }

        // 找到了
        return self::success($result);
    }

    protected function execFindBy($field, $value, $data = [])
    {
        $this->repository->pushCriteria(new QueryCriteria($data));
        $result = $this->repository->findBy($field, $value);
        return $result;
    }

    // Soft Delete *****************************************************************************************************

    /**
     * Retrive trashed objects
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function trashed()
    {
        $data = request()->all();
        $this->repository->onlyTrashed();

        $result = $this->execTrashed($data);
        return $this->success($result);
    }

    protected function execTrashed($data)
    {
        $result = $this->repository->query($data);
        return $result;
    }

    /**
     * Force delete an object
     *
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function erase($id)
    {
        $this->repository->onlyTrashed();

        $model = $this->findOrThrow($id);

        $result = $this->execErase($model);

        // 失败了
        if (empty($result)) {
            return $this->error(REST_OBJ_ERASE_FAIL);
        }

        // 成功了
        return self::success($result);
    }

    protected function execErase($model)
    {
        $result = $model->forceDelete();
        return $result;
    }

    /**
     * Restore a deleted object
     *
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function restore($id)
    {
        $this->repository->onlyTrashed();

        $model = $this->findOrThrow($id);

        $result = $this->execRestore($model);

        // 失败了
        if (empty($result)) {
            return $this->error(REST_OBJ_RESTORE_FAIL);
        }

        // 成功了
        return self::success($result);
    }

    protected function execRestore($model)
    {
        $result = $model->restore();
        return $result;
    }
}