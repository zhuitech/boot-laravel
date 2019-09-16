<?php

namespace ZhuiTech\BootLaravel\Controllers;

use App\Http\Controllers\Controller;
use Encore\Admin\Auth\Database\Administrator;
use http\Env\Response;
use Illuminate\Http\Request;
use ZhuiTech\BootLaravel\Models\TokenUser;

class StaffController extends Controller
{
    use RestResponse;

    /**
     * 登录
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function auth(Request $request)
    {
        $data = $this->validate($request, [
            'mobile' => 'required',
            'verify_code' => 'required',
        ]);

        // 请求验证
        $result = RestClient::server('service')->post('api/svc/sms/check', $data);
        if (!$result['status']) {
            return response()->json($result);
        }
        
        // 用户是否存在
        $user = Administrator::whereMobile($data['mobile'])->first();
        if (empty($user)) {
            return $this->fail('用户不存在');
        }

        // 返回
        $tokenUser = new TokenUser([
            'id' => $user->id,
            'type' => 'staff'
        ]);
        
        return $this->success([
            'user' => $user,
            'access_token' => $tokenUser->createToken('staff')->accessToken
        ]);
    }
}