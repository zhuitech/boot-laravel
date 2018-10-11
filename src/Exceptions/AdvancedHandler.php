<?php

namespace ZhuiTech\BootLaravel\Exceptions;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AdvancedHandler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        RestCodeException::class,
        \League\OAuth2\Server\Exception\OAuthServerException::class
    ];

    /**
     * Prepare exception for rendering.
     *
     * @param  \Exception  $e
     * @return \Exception
     */
    protected function prepareException(Exception $e)
    {
        $e = parent::prepareException($e);

        if ($e instanceof MethodNotAllowedHttpException) {
            $e = new NotFoundHttpException($e->getMessage(), $e);
        }

        return $e;
    }

    /**
     * Convert an authentication exception into a response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $exception
     * @return \Illuminate\Http\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        $errors = config('boot-laravel.errors');

        return $request->expectsJson()
            ? response()->json($this->error(REST_NOT_LOGIN), 401)
            : redirect()->guest(route('login'));
    }

    /**
     * Convert a validation exception into a JSON response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Validation\ValidationException  $exception
     * @return \Illuminate\Http\JsonResponse
     */
    protected function invalidJson($request, ValidationException $exception)
    {
        $errors = config('boot-laravel.errors');

        return response()->json(array_merge(
            $this->error(REST_DATA_VALIDATE_FAIL),
            ['errors' => $exception->errors()]
        ), 200);
    }

    /**
     * Convert the given exception to an array.
     *
     * @param  \Exception  $e
     * @return array
     */
    protected function convertExceptionToArray(Exception $e)
    {
        $errors = config('boot-laravel.errors');

        // 全局异常处理
        if ($e instanceof AccessDeniedHttpException){
            return $this->error(REST_NOT_AUTH);
        }
        elseif ($e instanceof NotFoundHttpException) {
            return $this->error(REST_NOT_FOUND);
        }
        elseif ($e instanceof RestCodeException) {
            return array_merge($this->error(), [
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
                'data' => $e->getData(),
            ]);
        }

        // 默认异常处理
        return config('app.debug') ? array_merge($this->error(), [
            'message' => $e->getMessage(),
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => collect($e->getTrace())->map(function ($trace) {
                return Arr::except($trace, ['args']);
            })->all(),
        ]) : $this->error();
    }

    /**
     * 返回错误消息
     * @param $code
     * @return array
     */
    private function error($code = REST_EXCEPTION)
    {
        $errors = config('boot-laravel.errors');

        return [
            'status' => false,
            'code' => $code,
            'message' => $errors[$code],
            'request' => request()->fullUrl()
        ];
    }

    /**
     * Prepare a response for the given exception.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception $e
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function prepareResponse($request, Exception $e)
    {
        // 调试模式
        if (! $this->isHttpException($e) && config('app.debug')) {
            return $this->toIlluminateResponse(
                $this->convertExceptionToResponse($e), $e
            );
        }

        // 转化成500错误，并显示对应消息
        if ($e instanceof RestCodeException) {
            $e = new HttpException(500, $e->getMessage());
        }

        // 转换成500错误，但是隐藏错误信息
        if (! $this->isHttpException($e)) {
            $e = new HttpException(500, get_class($e));
        }

        return $this->toIlluminateResponse(
            $this->renderHttpException($e), $e
        );
    }
}
