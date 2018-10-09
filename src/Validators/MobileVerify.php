<?php
/**
 * Created by PhpStorm.
 * User: andrew
 * Date: 2018/2/7
 * Time: 14:44
 */

namespace TrackHub\Laraboot\Validators;

use Illuminate\Contracts\Validation\Rule;
use TrackHub\Laraboot\Helpers\RestClient;

/**
 * Class MobileVerify
 * @package TrackHub\Laraboot\Validators
 */
class MobileVerify implements Rule
{
    private $code;

    private $message = '手机号码验证失败';

    /**
     * MobileVerify constructor.
     * @param $code
     * @param $token
     */
    public function __construct($code)
    {
        $this->code = $code;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $client = new RestClient();
        $url = service_url('system', 'api/sms/check');

        // 请求验证
        $result = $client->post($url, [
            'mobile' => $value,
            'verify_code' => $this->code
        ]);

        return $result['status'];
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->message;
    }
}