<?php
/**
 * Created by PhpStorm.
 * User: andrew
 * Date: 17/8/21
 * Time: 22:23
 */

namespace ZhuiTech\BootLaravel\Core;

/**
 * 兼容5.3版本
 * Class Application
 * @package ZhuiTech\BootLaravel\Compatible
 */
class CompatibilityApplication extends \Illuminate\Foundation\Application
{
    /**
     * Register a binding with the container.
     *
     * @param  string|array  $abstract
     * @param  \Closure|string|null  $concrete
     * @param  bool  $shared
     * @return void
     */
    public function bind($abstract, $concrete = null, $shared = false)
    {
        // If the given types are actually an array, we will assume an alias is being
        // defined and will grab this "real" abstract class name and register this
        // alias with the container so that it can be used as a shortcut for it.
        if (is_array($abstract)) {
            list($abstract, $alias) = $this->extractAlias($abstract);

            $this->alias($abstract, $alias);
        }

        parent::bind($abstract, $concrete, $shared);
    }

    /**
     * Extract the type and alias from a given definition.
     *
     * @param  array  $definition
     * @return array
     */
    protected function extractAlias(array $definition)
    {
        return [key($definition), current($definition)];
    }
}