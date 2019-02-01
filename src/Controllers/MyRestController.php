<?php

/*
 * This file is part of the current project.
 * 
 * (c) ForeverGlory <http://foreverglory.me/>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ZhuiTech\BootLaravel\Controllers;

use ZhuiTech\BootLaravel\Models\User;
use ZhuiTech\BootLaravel\Repositories\BaseRepository;
use Illuminate\Support\Facades\Auth;
use ZhuiTech\BootLaravel\Exceptions\RestCodeException;
use ZhuiTech\BootLaravel\Repositories\SimpleCriteria;

/**
 * Base controller for resource belongs to someone.
 *
 * @author ForeverGlory
 */
class MyRestController extends RestController
{
    /**
     * 当前用户
     * @var User
     */
    protected $user;

    /**
     * 用户外键字段名
     * @var string
     */
    protected $userForeignKey = 'user_id';

    /**
     * MyRestController constructor.
     * @param BaseRepository $repository
     */
    public function __construct(BaseRepository $repository)
    {
        if (!app()->runningInConsole()) {
            // 获取当前用户
            $this->user = Auth::user();
            if (empty($this->user)) {
                throw new RestCodeException(REST_NOT_LOGIN);
            }

            // 只处理当前用户的资源
            $criteria = new SimpleCriteria([
                $this->userForeignKey => $this->user->id
            ]);
            $repository->pushCriteria($criteria);
        }

        parent::__construct($repository);
    }

    /**
     * @param $data
     * @return mixed
     */
    protected function execStore($data)
    {
        // 设置用户ID
        $data[$this->userForeignKey] = $this->user->id;

        return parent::execStore($data);
    }
}
