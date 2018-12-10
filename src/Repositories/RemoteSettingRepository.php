<?php
/**
 * Created by PhpStorm.
 * User: breeze
 * Date: 2018-12-10
 * Time: 0:00
 */

namespace ZhuiTech\BootLaravel\Repositories;

use ZhuiTech\BootLaravel\Helpers\RestClient;

/**
 * Class RemoteSettingRepository
 * @package ZhuiTech\BootLaravel\Repositories
 */
class RemoteSettingRepository implements SettingRepository
{
    /**
     * @param array $settings
     * @return mixed
     */
    public function set(array $settings)
    {
        if (count($settings) <= 0) {
            return false;
        }

        foreach ($settings as $key => $val) {
            RestClient::server('system')->post('api/system/settings/' . $key, $val);
        }
    }

    /**
     * @param $key
     * @return mixed
     */
    public function get($key, $default = null)
    {
        $result = RestClient::server('system')->get('api/system/settings/' . $key);

        if ($result['status']) {
            return $result['data']['value'];
        }

        if(!is_null($default)){
            return $default;
        }
    }

    /**
     * @return mixed
     */
    public function allToArray()
    {
        $result = RestClient::server('system')->get('api/system/settings', ['_limit' => -1]);

        if ($result['status']) {
            $keyed = collect($result['data'])->mapWithKeys(function ($item) {
                return [$item['key'] => $item['value']];
            });

            return $keyed->toArray();
        }

        return [];
    }
}