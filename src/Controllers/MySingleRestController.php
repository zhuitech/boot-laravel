<?php
/**
 * Created by PhpStorm.
 * User: breeze
 * Date: 2019-05-02
 * Time: 16:47
 */

namespace ZhuiTech\BootLaravel\Controllers;


/**
 * Class MySingleRestController
 * @package ZhuiTech\BootLaravel\Controllers
 */
class MySingleRestController extends MyRestController
{
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
        }
        else {
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