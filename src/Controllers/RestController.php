<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016-08-19
 * Time: 12:31
 */

namespace ZhuiTech\BootLaravel\Controllers;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use ZhuiTech\BootLaravel\Exceptions\RestCodeException;
use ZhuiTech\BootLaravel\Repositories\BaseRepository;
use ZhuiTech\BootLaravel\Repositories\QueryCriteria;
use ZhuiTech\BootLaravel\Transformers\ModelTransformer;

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
     * 版本
     * @var int
     */
    protected $version;

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
     * 转化器类
     * @var string
     */
    protected $transformer = ModelTransformer::class;

    /**
     * RestController constructor.
     * @param BaseRepository $repository
     */
    public function __construct(BaseRepository $repository)
    {
        $this->repository = $repository;

        if (empty($this->version)) {
            $this->version = env('REST_VERSION', 2);
        }
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

    /**
     * 执行一些初始化
     */
    protected function prepare()
    {

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
        $this->prepare();

        $data = request()->all();
        $result = $this->execIndex($data);

        // v2 使用 transformer
        if ($this->version == 2) {
            $result = $this->transformList($result, new $this->transformer);
        }

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
        $this->prepare();

        // 找一下
        $result = $this->execShow($id);

        // v2 使用 transformer
        if ($this->version == 2) {
            $result = $this->transformItem($result, new $this->transformer);
        }

        // 找到了
        return self::success($result);
    }

    protected function execShow($id)
    {
        return $this->findOrThrow($id);
    }

    /**
     * Save a new object
     * POST    /photos
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function store()
    {
        $this->prepare();

        try {
            DB::beginTransaction();

            $data = $this->form()->all();
            $result = $this->execStore($data);

            // 创建失败
            if (empty($result)) {
                DB::rollBack();
                return $this->error(REST_OBJ_CREATE_FAIL);
            }
            else {
                // 成功了
                DB::commit();

                return self::success($result);
            }
        }
        catch (\Exception $ex) {
            DB::rollBack();
            throw $ex;
        }
    }

    protected function execStore($data)
    {
        $result = $this->repository->create($data);
        return $result;
    }

    /**
     * Update an exists object
     * PUT    /photos/{photo}
     *
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function update($id)
    {
        $this->prepare();

        try {
            DB::beginTransaction();

            $data = $this->form()->all();

            // 找一下
            $model = $this->findOrThrow($id);

            // 更新
            $result = $this->execUpdate($model, $data);

            // 更新失败
            if ($result === false) {
                DB::rollBack();
                return $this->error(REST_OBJ_UPDATE_FAIL);
            }
            else {
                // 成功了
                DB::commit();

                // 默认返回新的模型
                if ($result === true) {
                    $result = $this->findOrThrow($id);
                }
                return self::success($result);
            }
        } catch (\Exception $ex) {
            DB::rollBack();
            throw $ex;
        }
    }

    protected function execUpdate($model, $data)
    {
        $result = $model->fill($data)->save();
        return $result;
    }

    /**
     * Delete an object
     * DELETE    /photos/{photo}
     *
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function destroy($id)
    {
        $this->prepare();

        try {
            DB::beginTransaction();

            // 找一下
            $model = $this->findOrThrow($id);

            // 删除
            $result = $this->execDestroy($model);

            // 失败了
            if (empty($result)) {
                DB::rollBack();
                return $this->error(REST_OBJ_DELETE_FAIL);
            }
            else {
                // 成功了
                DB::commit();
                return self::success($result);
            }
        } catch (\Exception $ex) {
            DB::rollBack();
            throw $ex;
        }
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
        $this->prepare();

        $data = request()->all();

        // 找一下
        $result = $this->execFindBy($field, $value, $data);

        // 找不到
        if (empty($result)) {
            return $this->error(REST_OBJ_NOT_EXIST);
        }

        // v2 使用 transformer
        if ($this->version == 2) {
            $result = $this->transformItem($result, new $this->transformer);
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
        $this->prepare();

        $data = request()->all();
        $result = $this->execTrashed($data);
        return $this->success($result);
    }

    protected function execTrashed($data)
    {
        $result = $this->repository->onlyTrashed()->query($data);
        return $result;
    }

    /**
     * Force delete an object
     *
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function erase($id)
    {
        $this->prepare();

        try {
            DB::beginTransaction();

            $this->repository->onlyTrashed();
            $model = $this->findOrThrow($id);
            $result = $this->execErase($model);

            // 失败了
            if (empty($result)) {
                DB::rollBack();
                return $this->error(REST_OBJ_ERASE_FAIL);
            }
            else {
                // 成功了
                DB::commit();
                return self::success($result);
            }
        } catch (\Exception $ex) {
            DB::rollBack();
            throw $ex;
        }
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
     * @throws \Exception
     */
    public function restore($id)
    {
        $this->prepare();

        try {
            DB::beginTransaction();

            $this->repository->onlyTrashed();
            $model = $this->findOrThrow($id);
            $result = $this->execRestore($model);

            // 失败了
            if (empty($result)) {
                DB::rollBack();
                return $this->error(REST_OBJ_RESTORE_FAIL);
            }
            else {
                // 成功了
                DB::commit();
                return self::success($result);
            }
        } catch (\Exception $ex) {
            DB::rollBack();
            throw $ex;
        }
    }

    protected function execRestore($model)
    {
        $result = $model->restore();
        return $result;
    }
}