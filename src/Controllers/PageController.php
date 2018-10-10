<?php
/**
 * Created by PhpStorm.
 * User: andrew
 * Date: 17/9/15
 * Time: 10:06
 */

namespace ZhuiTech\LaraBoot\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

abstract class PageController extends RestController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * 处理并显示异常信息
     *
     * @param callable $process
     * @param null $data
     * @param null $rules
     * @return \Illuminate\Http\Response
     */
    protected function process(callable $process, $data = NULL, $rules = NULL)
    {
        try
        {
            // 验证
            if (!empty($rules)) {

                $validator = Validator::make($data, $rules);

                if ($validator->fails()) {
                    // 显示错误消息
                    $messages = $validator->messages()->getMessages();
                    foreach ($messages as $msg) {
                        flash($msg[0])->error()->important();
                    }

                    // 返回
                    return redirect()->back()->withInput();
                }
            }

            // 执行操作
            $result = $process($data);

            // 操作成功
            flash('操作成功')->success()->important();

            // 返回
            return $result;
        }
        catch (AccessDeniedHttpException $exception) {
            throw $exception;
        }
        catch (\Exception $exception){
            flash('操作失败：'.$exception->getMessage())->error()->important();
            return redirect()->back();
        }
    }

    /**
     * 处理并显示异常信息
     *
     * @param callable $process
     * @param null $data
     * @param null $rules
     * @return \Illuminate\Http\JsonResponse
     */
    protected function ajax(callable $process, $data = NULL, $rules = NULL)
    {
        return parent::process($process, $data, $rules);
    }
}