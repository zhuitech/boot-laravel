<?php

namespace ZhuiTech\BootLaravel\Middleware;

use Closure;
use Exception;
use Lcobucci\JWT\Parser;

/**
 * 解析AccessToken
 *
 * Class ParseToken
 * @package TrackHub\Wechat\Middleware
 */
class ParseToken
{
    /**
     * @param \Illuminate\Http\Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next){
        $raw = $request->bearerToken();

        if (!empty($raw)) {
            $token = (new Parser())->parse($raw);

            $request->attributes->set('oauth_access_token_id', $token->getClaim('jti'));
            $request->attributes->set('oauth_client_id', $token->getClaim('aud'));
            $request->attributes->set('oauth_user_id', $token->getClaim('sub'));
            $request->attributes->set('oauth_scopes', $token->getClaim('scopes'));
        }

        return $next($request);
    }
}
