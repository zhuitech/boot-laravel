<?php
/**
 * Created by PhpStorm.
 * User: andrew
 * Date: 17/9/14
 * Time: 23:41
 */

namespace ZhuiTech\LaraBoot\Helpers;

use Yingou\ChinaRegion\RegionUtils;

/**
 * 行政区划辅助类
 *
 * Class Regions
 * @package ZhuiTech\LaraBoot\Helpers
 */
class Regions
{
    /**
     * 省份数据
     * @return array
     */
    public static function provinces ()
    {
        static $provinces = [];

        if (empty($provinces)) {
            $regions = RegionUtils::listSubNode('000000');
            foreach ($regions as $region) {
                if (!empty($region->getName())) {
                    $provinces[$region->getId()] = $region->getName();
                }
            }
        }

        return $provinces;
    }
}