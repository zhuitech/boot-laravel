<?php

namespace ZhuiTech\BootLaravel\Middleware;

use Closure;
use DB;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Laravel\Passport\Client;
use Lcobucci\JWT\Parser;
use ZhuiTech\BootLaravel\Controllers\RestResponse;

/**
 * 缓存
 *
 * Class ParseToken
 * @package TrackHub\Wechat\Middleware
 */
class Cache
{
    /**
     * @param \Illuminate\Http\Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next, $groups = '', $ttl = '20m')
    {
        /* @var Response $response */
        $response = $next($request);

        // 设置缓存
        if ($response->status() == 200) {
            // Key
            $key = md5($request->getRequestUri());

            // TTL
            preg_match('/((?<h>[\d]+)h)?((?<m>[\d]+)m)?((?<s>[\d]+)s)?/', $ttl, $m);
            $m = array_filter($m);
            $seconds = (($m['h'] ?? 0) * 60 + ($m['m'] ?? 0)) * 60 + ($m['s'] ?? 0);
            
            // Group
            $parameters = $request->route()->originalParameters();
            $groups = explode('|', $groups);
            foreach ($groups as $group) {
                $group = magic_replace($group, $parameters);
                $key_group = \Cache::get("group:$group", []);
                $key_group[] = $key;
                $key_group = array_unique($key_group);
                \Cache::put("group:$group", $key_group);
            }

            $content = $response->content();
            $headers = $response->headers->all();
            \Cache::put($key, compact('content', 'headers'), $seconds);
        }

        return $response;
    }

    /**
     * 清除多组缓存
     * @param array $groups
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public static function forget($groups = [])
    {
        foreach ($groups as $group) {
            $key_group = \Cache::get("group:$group", []);
            \Cache::deleteMultiple($key_group);
        }
    }
}
