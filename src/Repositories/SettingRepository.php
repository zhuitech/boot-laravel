<?php

namespace ZhuiTech\BootLaravel\Repositories;

/**
 * Interface SettingInterface
 */
interface SettingRepository
{
    /**
     * @param array $settings
     * @return mixed
     */
    public function set(array $settings);

    /**
     * @param $key
     * @return mixed
     */
    public function get($key, $default = null);

    /**
     * @return mixed
     */
    public function allToArray();
}
