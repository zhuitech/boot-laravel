<?php
/**
 * Created by PhpStorm.
 * User: andrew
 * Date: 2018/2/9
 * Time: 15:39
 */

namespace TrackHub\Laraboot\Controllers;

/**
 *
 * Trait RestResponseTrait
 * @package TrackHub\Laraboot\Controllers
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
     * @return \Illuminate\Http\JsonResponse
     */
    protected function api($data = [], $status = true, $code = REST_SUCCESS, $message = '')
    {
        return response()->json([
            'status' => $status,
            'code' => $code,
            'message' => $message,
            'data' => is_array($data) && empty($data) ? null : $data
        ]);
    }

    /**
     * 返回错误消息
     * @param $code
     * @return \Illuminate\Http\JsonResponse
     */
    protected function error($code)
    {
        $errors = config('laraboot.errors');
        return self::api(null, false, $code, $errors[$code]);
    }

    /**
     * 返回调用成功的消息
     * @param array $data
     * @return \Illuminate\Http\JsonResponse
     */
    protected function success($data = [])
    {
        $errors = config('laraboot.errors');
        return self::api($data, true, REST_SUCCESS, $errors[REST_SUCCESS]);
    }
}