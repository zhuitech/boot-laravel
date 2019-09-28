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
            'body' => $request->getContent(),
        ];

        // 传递一些头信息
        foreach (['X-PJAX', 'X-PJAX-Container', 'Accept', 'Content-Type'] as $item) {
            if ($request->hasHeader($item)) {
                $options['headers'][$item] = $request->header($item);
            }
        }

        // multipart
        if (Str::startsWith($request->header('Content-Type'), 'multipart/form-data')) {
            unset($options['headers']['Content-Type']);
            unset($options['body']);
            $options['multipart'] = $this->createMultipart($request->all());
        }

        $this->plain()->request($request->path(), $request->method(), $options);
        return $this->getResponse();
    }
}