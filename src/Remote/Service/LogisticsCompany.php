<?php

namespace ZhuiTech\BootLaravel\Remote\Service;

use Illuminate\Support\Carbon;
use ZhuiTech\BootLaravel\Remote\Model;

/**
 * ZhuiTech\BootLaravel\Remote\Service\ShippingCompany
 *
 * @property int $id
 * @property string $code 编码
 * @property string $name 名称
 * @property int $status 状态：0=禁用 1=启用
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 */
class LogisticsCompany extends Model
{
	protected $server = 'service';
	protected $resource = 'api/svc/logistics/companies';
	protected $cache = true;

	public $queries = [
		'_limit' => -1,
		'_order' => ['id' => 'asc']
	];

	public static function ensure($name, $code = null): LogisticsCompany
	{
		if (!empty($code)) {
			$company = LogisticsCompany::where('code', $code)->first();
		} elseif (!empty($name)) {
			$company = LogisticsCompany::where('name', $name)->first();
		}

		if (empty($company)) {
			$company = new LogisticsCompany();
			$company->code = $code ?? $name;
			$company->name = $name;
			$company->save();
		}

		return $company;
	}
}
