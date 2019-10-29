<?php

namespace ZhuiTech\BootLaravel\Services;

use ZhuiTech\BootLaravel\Helpers\RestClient;

class EchoClient extends RestClient
{
    protected $server = 'echo';

    public function request($path, $method = 'GET', $options = [])
    {
        $options += ['query' => []];
        $options['query']['auth_key'] = env('ECHO_KEY');

        return parent::request($path, $method, $options);
    }
}