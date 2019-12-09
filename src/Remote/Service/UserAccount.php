<?php

namespace ZhuiTech\BootLaravel\Remote\Service;

use Illuminate\Support\Collection;
use ZhuiTech\BootLaravel\Remote\Model;
use ZhuiTech\BootLaravel\Remote\Transformers\UserAccountPublicTransformer;

class UserAccount extends Model
{
    protected $server = 'service';
    protected $resource = 'api/svc/user/accounts';

    /**
     * 绑定用户信息到列表
     * @param Collection $items
     * @return Collection
     */
    public static function includePublic(Collection $items)
    {
        $ids = $items->pluck('user_id')->toArray();
        $users = UserAccount::where('id', 'in', $ids)->limit(-1)->get();
        foreach ($items as $item) {
            $user = $users->where('id', $item->user_id)->first();
            if (!empty($user)) {
                $item->user = transform_item($user, new UserAccountPublicTransformer());
            }
        }
        return $items;
    }
}