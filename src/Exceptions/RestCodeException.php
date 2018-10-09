<?php
/**
 * Created by PhpStorm.
 * User: andrew
 * Date: 2018/1/25
 * Time: 11:03
 */

namespace TrackHub\Laraboot\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 *
 * Class RestCodeException
 * @package TrackHub\Laraboot\Exceptions
 */
class RestCodeException extends HttpException
{
    protected $data;

    /**
     * RestCodeException constructor.
     * @param int $code
     * @param null $data
     */
    public function __construct(int $code = 0, $data = null)
    {
        $errors = config('laraboot.errors');
        $message = $errors[$code];
        $this->data = $data;

        parent::__construct(200, $message, null, [], $code);
    }

    /**
     * 数据
     * @return null
     */
    public function getData() 
    {
        return $this->data;
    }
}