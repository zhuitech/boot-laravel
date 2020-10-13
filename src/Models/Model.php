<?php

namespace ZhuiTech\BootLaravel\Models;

use Closure;

/**
 * Class Model
 * @package ZhuiTech\BootLaravel\Models
 *
 * 1. 增加在Model外部定义relationship的能力
 *
 */
class Model extends \Illuminate\Database\Eloquent\Model
{
	/**
	 * The relation resolver callbacks.
	 *
	 * @var array
	 */
	protected static $relationResolvers = [];

	/**
	 * Define a relation resolver.
	 *
	 * @param  string  $name
	 * @param  \Closure  $callback
	 * @return void
	 */
	public static function resolveRelationUsing($name, Closure $callback)
	{
		static::$relationResolvers = array_replace_recursive(
			static::$relationResolvers,
			[static::class => [$name => $callback]]
		);
	}

	/**
	 * 是否存在关系
	 * @param $object
	 * @param $method
	 * @return bool
	 */
	public static function relationExists($object, $key)
	{
		$class = get_class($object);
		return method_exists($object, $key)|| ($object instanceof Model && isset($object::$relationResolvers[$class][$key]));
	}

	/**
	 * Get a relationship.
	 *
	 * @param  string  $key
	 * @return mixed
	 */
	public function getRelationValue($key)
	{
		// If the key already exists in the relationships array, it just means the
		// relationship has already been loaded, so we'll just return it out of
		// here because there is no need to query within the relations twice.
		if ($this->relationLoaded($key)) {
			return $this->relations[$key];
		}

		// If the "attribute" exists as a method on the model, we will just assume
		// it is a relationship and will load and return results from the query
		// and hydrate the relationship's value on the "relationships" array.
		if (static::relationExists($this, $key)) {
			return $this->getRelationshipFromMethod($key);
		}
	}

	/**
	 * Handle dynamic method calls into the model.
	 *
	 * @param  string  $method
	 * @param  array  $parameters
	 * @return mixed
	 */
	public function __call($method, $parameters)
	{
		if (in_array($method, ['increment', 'decrement'])) {
			return $this->$method(...$parameters);
		}

		/* @var $resolver Closure */
		if ($resolver = static::$relationResolvers[static::class][$method] ?? null) {
			return $resolver->call($this, $this);
		}

		return $this->forwardCallTo($this->newQuery(), $method, $parameters);
	}
}