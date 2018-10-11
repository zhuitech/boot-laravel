<?php
/**
 * Created by PhpStorm.
 * User: andrew
 * Date: 17/8/22
 * Time: 17:37
 */

namespace ZhuiTech\BootLaravel\Profiles;

/**
 * 配置项安装器
 * Class SettingsInstaller
 * @package ZhuiTech\BootLaravel\Profiles
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