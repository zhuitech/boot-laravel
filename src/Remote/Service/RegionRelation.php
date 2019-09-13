<?php

namespace ZhuiTech\BootLaravel\Remote\Service;

use Illuminate\Support\Str;

/**
 * Trait RegionRelation
 * @package ZhuiTech\BootLaravel\Remote\Service
 * 
 * @property-read LogisticsRegion $province
 * @property-read LogisticsRegion $city
 * @property-read LogisticsRegion $district
 * @property-read string $region_text
 */
trait RegionRelation
{
    public function getProvinceAttribute()
    {
        if (!empty($this->province_code)) {
            return LogisticsRegion::query()->find($this->province_code);
        }
    }

    public function getCityAttribute()
    {
        if (!empty($this->city_code)) {
            return LogisticsRegion::query()->find($this->city_code);
        }
    }

    public function getDistrictAttribute()
    {
        if (!empty($this->district_code)) {
            return LogisticsRegion::query()->find($this->district_code);
        }
    }
    
    public function getRegionTextAttribute()
    {
        $text = [];
        
        if (!empty($this->province)) {
            $text[] = $this->province->name;
        }

        if (!empty($this->city)) {
            $text[] = $this->city->name;
        }

        if (!empty($this->district)) {
            $text[] = $this->district->name;
        }
        
        return implode(' ', $text);
    }
}