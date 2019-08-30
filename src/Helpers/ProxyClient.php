<?php

namespace ZhuiTech\BootLaravel\Helpers;

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
                'Content-Type' => 'application/json',
                'X-FORWARDED-PROTO' => $request->getScheme(),
                'X-FORWARDED-HOST' => $request->server('HTTP_HOST'),
            ],
            'query' => $request->query(),
            'body' => json_encode($request->input())
        ];

        // 传递一些头信息
        foreach (['X-PJAX', 'X-PJAX-Container', 'Accept'] as $item) {
            if ($request->hasHeader($item)) {
                $options['headers'][$item] = $request->header($item);
            }
        }

        // 若包含文件，以multipart方式转发
        if (count($request->allFiles()) > 0) {
            unset($options['headers']['Content-Type']);
            unset($options['body']);
            $options['multipart'] = $this->createMultipart($request->all());
        }

        $this->plain()->request($request->path(), $request->method(), $options);
        return $this->getResponse();
    }
}