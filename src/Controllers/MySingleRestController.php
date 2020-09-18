<?php

namespace ZhuiTech\BootLaravel\Controllers;


use Illuminate\Http\JsonResponse;

/**
 * Class MySingleRestController
 * @package ZhuiTech\BootLaravel\Controllers
 */
abstract class MySingleRestController extends MyRestController
{
	/**
	 * Retrive a list of objects
	 * GET        /photos
	 *
	 * @return JsonResponse
	 */
	public function index()
	{
		$this->prepare();

		$data = request()->all();
		$result = $this->execIndex($data);

		// v2 使用 transformer
		if ($this->version >= 2) {
			$result = $this->transformItem($result);
		}

		return $this->success($result);
	}

	/**
	 * 只返回一个
	 * @param $data
	 * @return mixed
	 */
	public function execIndex($data)
	{
		return $this->repository->all()->first();
	}

	/**
	 * 添加或更新
	 * @param $data
	 * @return mixed
	 */
	public function execStore($data)
	{
		$model = $this->repository->all()->first();

		if (empty($model)) {
			return $this->storeSingle($data);
		} else {
			return $this->updateSingle($model, $data);
		}
	}

	/**
	 * 添加
	 * @param $data
	 * @return mixed
	 */
	protected function storeSingle($data)
	{
		return parent::execStore($data);
	}

	/**
	 * 更新
	 * @param $model
	 * @param $data
	 * @return mixed
	 */
	protected function updateSingle($model, $data)
	{
		return parent::execUpdate($model, $data);
	}

	public function update($id)
	{
		return $this->fail('不支持的方法');
	}

	public function destroy($id)
	{
		return $this->fail('不支持的方法');
	}
}