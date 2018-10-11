<?php
/**
 * Created by PhpStorm.
 * User: andrew
 * Date: 17/8/22
 * Time: 17:34
 */

namespace ZhuiTech\BootLaravel\Profiles;

/**
 * 配置档案安装器
 * interface ProfileInstaller
 * @package ZhuiTech\BootLaravel\Profiles
 */
interface ProfileInstaller
{
    /**
     * 名称
     * @return mixed
     */
    public function name();

    /**
     * 安装
     * @param $profile
     * @param $messages array|string
     * @return mixed
     */
    public function install($profile, &$messages = []);
}