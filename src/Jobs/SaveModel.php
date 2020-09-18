<?php

namespace ZhuiTech\BootLaravel\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SaveModel implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	/**
	 * @var Model
	 */
	protected $class;

	protected $attributes;

	public function __construct(Model $model)
	{
		$this->class = get_class($model);
		$this->attributes = $model->getAttributes();
	}

	public function handle()
	{
		/* @var $model Model */
		$model = new $this->class;
		$model->setRawAttributes($this->attributes);
		$model->save();
	}
}