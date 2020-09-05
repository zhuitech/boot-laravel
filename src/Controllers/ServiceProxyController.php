<?php

namespace ZhuiTech\BootLaravel\Controllers;

use Exception;
use GuzzleHttp\Psr7\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Log;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use ZhuiTech\BootLaravel\Helpers\ProxyClient;
use ZhuiTech\BootLaravel\Helpers\Restful;
use ZhuiTech\BootLaravel\Models\TokenUser;

class ServiceProxyController extends Controller
{
	/**
	 * 代理API
	 *
	 * @return Response
	 */
	public function api()
	{
		return ProxyClient::server('service')->pass();
	}

	/**
	 * 代理签发令牌
	 * @return JsonResponse
	 */
	public function token()
	{
		$result = ProxyClient::server('service')->passJson();

		if ($result['status'] != false) {
			$user = new TokenUser($result['data']);
			$result['data']['token'] = $user->createToken('members')->accessToken;
		}

		return response()->json($result);
	}

	/**
	 * 中台服务通知
	 * @throws \Exception
	 */
	public function notify()
	{
		$data = request()->all();

		try {
			// 触发事件
			event("service.notify.{$data['event']}", [$data['data']]);

			// 正常返回
			$result = Restful::format([]);
		}
		catch (Exception $ex) {
			Log::error($ex);
			$result = Restful::format([], false, REST_FAIL, $ex->getMessage());
		}

		return response()->json($result);
	}
}