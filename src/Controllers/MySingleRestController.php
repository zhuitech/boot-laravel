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
            return parent::execStore($data);
        }
        else {
            return parent::execUpdate($model, $data);
        }
    }
}