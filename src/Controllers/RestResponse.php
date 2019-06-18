<?php
/**
 * Created by PhpStorm.
 * User: andrew
 * Date: 2018/2/9
 * Time: 15:39
 */

namespace ZhuiTech\BootLaravel\Controllers;

use ZhuiTech\BootLaravel\Helpers\Restful;

/**
 *
 * Trait RestResponseTrait
 * @package ZhuiTech\BootLaravel\Controllers
 */
trait RestResponse
{
    /* -----------------------------------------------------------------
     |  Restfull result output methods
     | -----------------------------------------------------------------
     */

    /**
     * API 返回数据
     *
     * @param array $data
     * @param bool $status
     * @param int $code
     * @param string $message
     * @param null $extra
     * @return \Illuminate\Http\JsonResponse
     */
    protected function api($data = [], $status = true, $code = REST_SUCCESS, $message = NULL)
    {
        $result = Restful::format($data, $status, $code, $message);
        return response()->json($result);
    }

    /**
     * 返回错误代码
     *
     * @param $code
     * @param array $data
     * @return \Illuminate\Http\JsonResponse
     */
    protected function error($code, $data = [])
    {
        return self::api($data, false, $code);
    }

    /**
     * 返回成功消息
     *
     * @param array $data
     * @return \Illuminate\Http\JsonResponse
     */
    protected function success($data = [])
    {
        return self::api($data, true, REST_SUCCESS);
    }

    /**
     * 返回错误消息
     *
     * @param $message
     * @param array $data
     * @return \Illuminate\Http\JsonResponse
     */
    protected function fail($message = NULL, $data = [])
    {
        return self::api($data, false, REST_FAIL, $message);
    }
}