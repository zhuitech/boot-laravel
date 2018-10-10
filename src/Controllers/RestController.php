<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016-08-19
 * Time: 12:31
 */

namespace ZhuiTech\LaraBoot\Controllers;

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
use ZhuiTech\LaraBoot\Exceptions\RestCodeException;
use ZhuiTech\LaraBoot\Models\MemberOwnershipContract;
use ZhuiTech\LaraBoot\Models\OwnershipContract;
use ZhuiTech\LaraBoot\Repositories\BaseRepository;
use ZhuiTech\LaraBoot\Repositories\QueryCriteria;

/**
 * Base class for restfull api.
 *
 * Class RestController
 * @package ZhuiTech\LaraBoot\Controllers
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

        // 找不到
        if (empty($result)) {
            return $this->error(REST_OBJ_NOT_EXIST);
        }

        // 找到了
        return self::success($result);
    }

    protected function execShow($id)
    {
        $result = $this->repository->find($id);
        return $result;
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

        $data = $this->assignOwner($data);
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

    protected function assignOwner($data)
    {
        // 设置所属会员
        $model = $this->repository->makeModel();
        if ($model instanceof OwnershipContract) {
            $user = Auth::user();
            if (!empty($user) && $model->assignable($user)) {
                $model->assign($user, $data);
            }
        }

        return $data;
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

        $result = $this->execUpdate($data, $id);

        // 创建失败
        if (empty($result)) {
            return $this->error(REST_OBJ_UPDATE_FAIL);
        }

        // 成功了
        $new = $this->repository->find($id);
        return self::success($new);
    }

    protected function execUpdate($data, $id)
    {
        $result = $this->repository->updateRich($data, $id); // Eloquent method
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
        $result = $this->execDestroy($id);

        // 失败了
        if (empty($result)) {
            return $this->error(REST_OBJ_DELETE_FAIL);
        }

        // 成功了
        return self::success($result);
    }

    protected function execDestroy($id)
    {
        $result = $this->repository->delete($id);
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
        // 找一下
        $result = $this->execFindBy($field, $value);

        // 找不到
        if (empty($result)) {
            return $this->error(REST_OBJ_NOT_EXIST);
        }

        // 找到了
        return self::success($result);
    }

    protected function execFindBy($field, $value, $columns = ['*'])
    {
        $result = $this->repository->findBy($field, $value, $columns);
        return $result;
    }

    /**
     * Find objects belong to me
     */
    public function findMy()
    {
        $model = $this->repository->makeModel();
        if (! $model instanceof OwnershipContract) {
            throw new RestCodeException(REST_OBJ_NO_OWNERSHIP);
        }

        // 没有登录
        $user = Auth::user();
        if (empty($user)) {
            throw new RestCodeException(REST_NOT_LOGIN);
        }

        // 用户类型不对
        if (!$model->assignable($user)) {
            throw new RestCodeException(REST_NOT_AUTH);
        }

        // 设置查询条件
        $data = request()->all();
        $data[$model->ownerKey()] = $user->id;

        $result = $this->execFindMy($data);
        return $this->success($result);
    }

    protected function execFindMy($data)
    {
        $result = $this->repository->query($data);
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
        $model = $this->repository->find($id);

        if (empty($model)) {
            throw new RestCodeException(REST_OBJ_NOT_EXIST);
        }

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
        $model = $this->repository->find($id);

        if (empty($model)) {
            throw new RestCodeException(REST_OBJ_NOT_EXIST);
        }

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