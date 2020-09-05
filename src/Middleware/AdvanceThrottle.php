<?php

namespace ZhuiTech\BootLaravel\Middleware;

use Illuminate\Routing\Middleware\ThrottleRequests;

/**
 * 自定义流控
 * Class AdvanceThrottle
 * @package ZhuiTech\BootLaravel\Middleware
 */
class AdvanceThrottle extends ThrottleRequests
{
	protected function resolveRequestSignature($request)
	{
		return 'advance.' . parent::resolveRequestSignature($request);
	}

	protected function getHeaders($maxAttempts, $remainingAttempts, $retryAfter = null)
	{
		$headers = [
			'X-RateLimit-Limit-1' => $maxAttempts,
			'X-RateLimit-Remaining-1' => $remainingAttempts,
		];

		if (! is_null($retryAfter)) {
			$headers['Retry-After'] = $retryAfter;
			$headers['X-RateLimit-Reset'] = $this->availableAt($retryAfter);
		}

		return $headers;
	}
}