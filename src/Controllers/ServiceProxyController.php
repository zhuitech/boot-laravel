<?php

namespace ZhuiTech\BootLaravel\Controllers;

use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Illuminate\Routing\Controller;
use ZhuiTech\BootAdmin\Admin\Controllers\AdminController;
use ZhuiTech\BootLaravel\Helpers\ProxyClient;
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
}