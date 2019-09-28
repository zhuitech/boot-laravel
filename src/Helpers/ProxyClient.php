<?php

namespace ZhuiTech\BootLaravel\Helpers;

use Illuminate\Support\Str;

/**
 * 反向代理
 *
 * Class HttpProxy
 * @package ZhuiTech\BootLaravel\Helpers
 */
class ProxyClient extends RestClient
{
    /**
     * @return \GuzzleHttp\Psr7\Response
     */
    public function pass()
    {
        $request = request();

        $options = [
            'allow_redirects' => false,
            'headers' => [
                'X-FORWARDED-PROTO' => $request->getScheme(),
                'X-FORWARDED-HOST' => $request->server('HTTP_HOST'),
            ],
            'query' => $request->query(),
        ];
        foreach (['X-PJAX', 'X-PJAX-Container', 'Accept'] as $item) {
            if ($request->hasHeader($item)) {
                $options['headers'][$item] = $request->header($item);
            }
        }

        // Multipart
        if (Str::startsWith($request->header('Content-Type'), 'multipart/form-data')) {
            $options['multipart'] = $this->createMultipart($request->all());
        } else {
            // Other
            $options['headers']['Content-Type'] = $request->header('Content-Type');
            $options['body'] = $request->getContent();
        }

        // 注意此处不能用 $request->method()，要完全模拟原始请求
        $method = strtoupper($request->server->get('REQUEST_METHOD', 'GET'));
        $this->plain()->request($request->path(), $method, $options);
        return $this->getResponse();
    }
}