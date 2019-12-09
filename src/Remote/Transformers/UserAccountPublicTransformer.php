<?php

namespace ZhuiTech\BootLaravel\Remote\Transformers;

use ZhuiTech\BootLaravel\Transformers\ModelTransformer;

class UserAccountPublicTransformer extends ModelTransformer
{
    protected $only = ['id', 'name', 'nickname', 'avatar'];
}