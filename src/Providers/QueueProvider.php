<?php


namespace ZhuiTech\BootLaravel\Providers;

/**
 * 队列、异步消息机制
 * Class QueueProvider
 * @package ZhuiTech\BootLaravel\Providers
 */
class QueueProvider extends AbstractServiceProvider
{
    public function boot()
    {
        parent::boot();

        $this->loadMigrationsFrom($this->basePath('migrations/queues'));
    }
}