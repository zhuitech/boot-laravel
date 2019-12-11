<?php

namespace ZhuiTech\BootLaravel\Controllers;

use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Illuminate\Routing\Controller;
use ZhuiTech\BootAdmin\Admin\Controllers\AdminController;
use ZhuiTech\BootLaravel\Helpers\ProxyClient;
use ZhuiTech\BootLaravel\Helpers\Restful;
use ZhuiTech\BootLaravel\Models\TokenUser;
use ZhuiTech\BootLaravel\Models\User;

class ServiceProxyController extends Controller
{
    /**
     * 代理API
     *
     * @return \GuzzleHttp\Psr7\Response
     */
    public function api()
    {
        return ProxyClient::server('service')->pass();
    }

    /**
     * 代理签发令牌
     * @return \Illuminate\Http\JsonResponse
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
     */
    public function notify()
    {
        $data = request()->all();
        $event = $data['action'] ?? $data['event'];
        event("service.notify.$event", [$data]);

        $result = Restful::format();
        return response()->json($result);
    }
}