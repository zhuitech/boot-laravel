<?php

namespace ZhuiTech\BootLaravel\Auth;

use Illuminate\Session\CacheBasedSessionHandler;
use ZhuiTech\BootLaravel\Models\User;

/**
 * 会员会话
 * Class MemberSessionHandler
 * @package ZhuiTech\BootLaravel\Auth
 */
class MemberSessionHandler extends CacheBasedSessionHandler
{
    public function read($sessionId)
    {
        $sessionId = $this->sessionId();
        return parent::read($sessionId);
    }

    public function write($sessionId, $data)
    {
        $sessionId = $this->sessionId();
        return parent::write($sessionId, $data);
    }

    public function destroy($sessionId)
    {
        $sessionId = $this->sessionId();
        return parent::destroy($sessionId);
    }

    /**
     * 重写会话ID为每个用户唯一，便于在服务之间共享会话数据
     * @return string
     */
    private function sessionId()
    {
        /* @var User $user*/
        $user = \Auth::user();
        return "sess.{$user->type}.{$user->id}";
    }
}