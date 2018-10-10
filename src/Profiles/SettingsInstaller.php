<?php
/**
 * Created by PhpStorm.
 * User: andrew
 * Date: 17/8/22
 * Time: 17:37
 */

namespace ZhuiTech\LaraBoot\Profiles;

/**
 * 配置项安装器
 * Class SettingsInstaller
 * @package ZhuiTech\LaraBoot\Profiles
 */
class SettingsInstaller implements ProfileInstaller
{
    /**
     * 名称
     * @return mixed
     */
    public function name()
    {
        return 'settings';
    }

    /**
     * 安装
     * @param $profile
     * @param $messages array|string
     * @return mixed
     */
    public function install($profile, &$messages = [])
    {
        // TODO: Implement install() method.
    }
}