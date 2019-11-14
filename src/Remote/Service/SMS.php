<?php

namespace ZhuiTech\BootLaravel\Remote\Service;

use ZhuiTech\BootLaravel\Helpers\RestClient;

class SMS
{
    /**
     * 发送短信
     * 
     * @param $mobile
     * @param $template
     * @param array $data
     * @return array|mixed
     */
    public static function send($mobile, $template, $data = [])
    {
        $data = [
            'mobile' => $mobile,
            'template' => $template,
            'data' => $data,
        ];
        
        return RestClient::server('service')->post('api/svc/sms/send', $data);
    }

    public static function check($to, $code)
    {
        $result = RestClient::server('service')->post('api/svc/sms/check', ['mobile' => $to, 'verify_code' => $code]);
        return $result['status'] ?? false;
    }
}