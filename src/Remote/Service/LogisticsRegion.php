<?php

namespace ZhuiTech\BootLaravel\Remote\Service;

use ZhuiTech\BootLaravel\Helpers\RestClient;
use ZhuiTech\BootLaravel\Remote\Model;

/**
 * Class LogisticsRegion
 * @package ZhuiTech\BootLaravel\Remote\Service
 * 
 * @property string $code
 * @property string $name
 * @property string $parent_code
 * @property LogisticsRegion $parent
 */
class LogisticsRegion extends Model
{
    const OPTIONS_URL = '/api/svc/logistics/regions/select';
    
    protected $server = 'service';
    protected $resource = 'api/svc/logistics/regions';
    protected $cache = true;

    /**
     * 多级下拉框选项
     * 
     * @param null $parentCode
     * @return array|\Illuminate\Support\Collection
     */
    public static function selectOptions($parentCode = null)
    {
        $result = RestClient::server('service')->get(self::OPTIONS_URL, ['q' => $parentCode]);
        if (!empty($result) && (!isset($result['status']) || $result['status'] == false)) {
            return collect($result)->pluck('text', 'id')->toArray();
        }
        return [];
    }

    public function getParentAttribute()
    {
        return self::find($this->parent_code);
    }
}