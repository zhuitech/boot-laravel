<?php

namespace ZhuiTech\BootLaravel\Middleware;

use Closure;
use DB;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Laravel\Passport\Client;
use Lcobucci\JWT\Parser;
use ZhuiTech\BootLaravel\Controllers\RestResponse;

/**
 * API请求签名验证
 *
 * Class ParseToken
 * @package TrackHub\Wechat\Middleware
 */
class Signature
{
    use RestResponse;

    /**
     * @param \Illuminate\Http\Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $data = $request->input();

        $required = Arr::only($data, ['client_id', 'signature', 'time']);
        if (empty($required)) {
            return $this->fail('请检查必要参数', [
                'client_id' => '客户ID：从管理员获取',
                'signature' => '签名算法：1.业务参数+time参数，去除对象，2.按参数名称字母升序，3.拼接成 querystring 字符串，4.在字符串后追加密钥，经过 md5 计算后转大写',
                'time' => '当前时间：' . Carbon::now()->toDateTimeString(),
            ]);
        }

        $time = Carbon::parse($data['time']);
        if ($time->diffInMinutes() > 5) {
            return $this->fail('请求超时', [
                'server_time' => Carbon::now()->toDateTimeString(),
            ]);
        }

        $client = DB::table('oauth_clients')->where('id', $data['client_id'])->first();
        if (empty($client)) {
            return $this->fail('客户ID不存在', [
                'client_id' => $data['client_id'],
            ]);
        }

        // 1.
        $paras = [];
        foreach ($data as $key => $value) {
            if (in_array($key, ['client_id', 'signature']) || is_array($value)) {
                continue;
            }
            $paras[$key] = $value;
        }

        // 2.
        ksort($paras);

        // 3.
        $parameters = http_build_query($paras);

        // 4.
        $signature = Str::upper(md5($parameters . $client->secret));

        if ($signature != $data['signature']) {
            return $this->fail('签名错误', [
                'parameters' => $parameters,
            ]);
        }

        return $next($request);
    }
}
