<?php

namespace ZhuiTech\BootLaravel\Remote;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use ZhuiTech\BootLaravel\Exceptions\RestCodeException;
use ZhuiTech\BootLaravel\Helpers\RestClient;
use ZhuiTech\BootLaravel\Remote\Service\UserAccount;

/**
 * 远程模型
 * 
 * @mixin Builder
 */
class Model extends \Illuminate\Database\Eloquent\Model
{
    /**
     * 微服务名称
     * @var
     */
    protected $server;

    /**
     * resource 接口
     * @var
     */
    protected $resource;

    /**
     * 是否启用缓存
     * @var bool
     */
    protected $cache = false;

    /**
     * 缓存前缀
     * @var string
     */
    protected $cache_prefix = 'Remote';

    /**
     * 查询参数
     * _order, _or, _limit, _page, _size, _column
     * @var array
     */
    public $queries = [
        '_limit' => -1,
        '_order' => ['id' => 'desc']
    ];

    # Model 复写 ########################################################################################################

    /**
     * 删除
     *
     * @return void
     */
    protected function performDeleteOnModel()
    {
        $result = RestClient::server($this->server)->delete("{$this->resource}/{$this->getKey()}");

        // 处理返回结果
        if ($result['status'] === true) {
            $this->exists = false;
            
            // 更新缓存
            \Cache::delete($this->itemCacheKey($this->getKey()));
        } else {
            throw new RestCodeException($result['code'], $result['data'], $result['message']);
        }
    }

    /**
     * 更新
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return bool
     */
    protected function performUpdate(\Illuminate\Database\Eloquent\Builder $query)
    {
        // If the updating event returns false, we will cancel the update operation so
        // developers can hook Validation systems into their models and cancel this
        // operation if the model does not pass validation. Otherwise, we update.
        if ($this->fireModelEvent('updating') === false) {
            return false;
        }

        // Once we have run the update operation, we will fire the "updated" event for
        // this model instance. This will allow developers to hook into these after
        // models are updated, giving them a chance to do any special processing.
        $dirty = $this->getDirty();

        if (count($dirty) > 0) {
            // 更新
            $result = RestClient::server($this->server)->put("{$this->resource}/{$this->getKey()}", $dirty);

            // 处理返回结果
            if ($result['status'] === true) {
                $this->fireModelEvent('updated', false);
                $this->syncChanges();

                // 更新缓存
                \Cache::delete($this->itemCacheKey($this->getKey()));
            } else {
                throw new RestCodeException($result['code'], $result['data'], $result['message']);
            }
        }

        return true;
    }

    /**
     * 新增
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return bool
     */
    protected function performInsert(\Illuminate\Database\Eloquent\Builder $query)
    {
        if ($this->fireModelEvent('creating') === false) {
            return false;
        }

        // If the model has an incrementing key, we can use the "insertGetId" method on
        // the query builder, which will give us back the final inserted ID for this
        // table from the database. Not all tables have to be incrementing though.
        $attributes = $this->getAttributes();

        // 远程调用
        $result = RestClient::server($this->server)->post("{$this->resource}", $attributes);

        // 处理返回结果
        if ($result['status'] === true) {
            $this->exists = true;
            $this->wasRecentlyCreated = true;
            $this->fireModelEvent('created', false);
            
            // 重新初始化对象，获取自增字段值
            $this->setRawAttributes($result['data']);
        } else {
            throw new RestCodeException($result['code'], $result['data'], $result['message']);
        }

        return true;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder|Builder
     */
    public function newQuery()
    {
        return new Builder($this);
    }

    /**
     * @param array|string $relations
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|Builder
     */
    public static function with($relations)
    {
        return static::newQuery();
    }

    # 辅助方法 ##########################################################################################################
    
    public function itemCacheKey($id)
    {
        return implode('.', [$this->cache_prefix, class_basename($this), $id]);
    }

    public function performFind($id, $columns = ['*'])
    {
        $key = $this->itemCacheKey($id);

        // 查询缓存
        if ($this->cache and $data = \Cache::get($key)) {
            return $data;
        }

        // 请求后端服务
        $result = RestClient::server($this->server)->get("{$this->resource}/{$id}");

        // 处理返回结果
        if ($result['status'] === true) {
            $data = static::newFromBuilder($result['data']);

            // 设置缓存
            if ($this->cache) {
                \Cache::put($key, $data);
            }
        }

        return $data ?? null;
    }

    public function performQuery($columns = ['*'])
    {
        // 请求后端服务
        $result = RestClient::server($this->server)->get($this->resource, $this->queries);

        // 处理返回结果
        $list = collect();
        if ($result['status'] === true) {
            foreach ($result['data'] as $item) {
                $list[] = static::newFromBuilder($item);
            }
            
            if (!empty($result['meta']['pagination'])){
                $paginator = new LengthAwarePaginator($list, $result['meta']['pagination']['total'], $perPage);
                $paginator->setPath(url()->current());
                return $paginator;
            } else {
                return $list;
            }
        }
        
        return $list;
    }
    
    public function reload()
    {
        // 更新缓存
        \Cache::delete($this->itemCacheKey($this->getKey()));
        return $this->performFind($this->getKey());
    }

    /**
     * 批量加载
     * 
     * @param Collection $items
     * @return Collection
     */
    public static function batchInclude(Collection $list, $foreignKey, $transformer = 'public')
    {
        $ids = array_unique($list->pluck($foreignKey)->toArray());
        $models = static::where('id', 'in', $ids)->limit(-1)->transformer($transformer)->get();
        $foreignModel = Arr::first(explode('_', $foreignKey));
        
        $list->map(function ($item) use ($models, $foreignKey, $foreignModel) {
            $item->$foreignModel = $models->where('id', $item->$foreignKey)->first();
        });

        return $list;
    }
}