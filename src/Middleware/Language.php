<?php

namespace ZhuiTech\BootLaravel\Middleware;

use Closure;
use Exception;
use Illuminate\Support\Facades\App;
use Lcobucci\JWT\Parser;

/**
 * 语言切换
 *
 * Class ParseToken
 * @package TrackHub\Wechat\Middleware
 */
class Language
{
    /**
     * @param \Illuminate\Http\Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next){
        $lang = $request->header('X-Language');

        if (!empty($lang)) {
            App::setLocale($lang);
        }

        return $next($request);
    }
}
