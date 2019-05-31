<?php


namespace ZhuiTech\BootLaravel\Providers;

/**
 * 队列、异步消息机制
 * Class QueueProvider
 * @package ZhuiTech\BootLaravel\Providers
 */
class QueueProvider extends ServiceProvider
{
    protected $migrations = [
        'vendor/zhuitech/boot-laravel/migrations/queues'
    ];
}