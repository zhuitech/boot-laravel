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
     * @param string $groups
     * @param string $ttl
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
            $time = (($m['h'] ?? 0) * 60 + ($m['m'] ?? 0)) * 60 + ($m['s'] ?? 0);

            if (is_version('5.5')) { // 5.5 use minutes
                $time = $time / 60;
            }

            // Group
            $parameters = is_version('5.5') ? $request->route()->parameters() : $request->route()->originalParameters();
            $groups = explode('|', $groups);
            foreach ($groups as $group) {
                $group = magic_replace($group, $parameters);
                $key_group = \Cache::get("group:$group", []);
                $key_group[] = $key;
                $key_group = array_unique($key_group);
                \Cache::forever("group:$group", $key_group);//dd($key_group);
            }

            $content = $response->content();
            $headers = $response->headers->all();
            $value = compact('content', 'headers');

            if ($time > 0) {
                \Cache::put($key, $value, $time);
            } else {
                \Cache::forever($key, $value);
            }
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
