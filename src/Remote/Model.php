<?php

namespace ZhuiTech\BootLaravel\Remote;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use ZhuiTech\BootLaravel\Exceptions\RestCodeException;
use ZhuiTech\BootLaravel\Helpers\RestClient;

/**
 * 远程模型
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
     * 查询参数
     * _order, _or, _limit, _page, _size, _column
     * @var array
     */
    protected $queries = [
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
    protected function performUpdate(Builder $query)
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
    protected function performInsert(Builder $query)
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
        } else {
            throw new RestCodeException($result['code'], $result['data'], $result['message']);
        }

        return true;
    }

    /**
     * @return static
     */
    public function newQuery()
    {
        return new static();
    }

    /**
     *
     * @param array|string $relations
     * @return static
     */
    public static function with($relations)
    {
        return new static();
    }

    /**
     * @return static
     */
    public static function query()
    {
        return (new static)->newQuery();
    }

    # Builder 复写 ######################################################################################################
    # 由于Model使用魔术方法转发Builder的调用，所以这里需要重写，并且不能用parent调用

    /**
     * 查找单个
     *
     * @param mixed $id
     * @param array $columns
     * @return static
     */
    public function find($id, $columns = ['*'])
    {
        // 请求后端服务
        $result = RestClient::server($this->server)->get("{$this->resource}/{$id}");

        // 处理返回结果
        if ($result['status'] === true) {
            return static::newFromBuilder($result['data']);
        }

        return null;
    }

    /**
     * 查询
     *
     * @param  array  $columns
     * @return \Illuminate\Support\Collection
     */
    public function get($columns = ['*'])
    {
        // 请求后端服务
        $result = RestClient::server($this->server)->get($this->resource, $this->queries);

        // 处理返回结果
        $list = collect();
        if ($result['status'] === true) {
            foreach ($result['data'] as $item) {
                $list[] = static::newFromBuilder($item);
            }
        }

        return $list;
    }

    /**
     * 分页查询
     *
     * @param  int  $perPage
     * @param  array  $columns
     * @param  string  $pageName
     * @param  int|null  $page
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     *
     * @throws \InvalidArgumentException
     */
    public function paginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null)
    {
        // 分页
        $page = $page ?: Paginator::resolveCurrentPage($pageName);
        $perPage = $perPage ?: $this->getPerPage();
        $this->forPage($page, $perPage);

        // 请求后端服务
        $result = RestClient::server($this->server)->get($this->resource, $this->queries);

        $list = collect();
        if ($result['status'] === true) {
            foreach ($result['data'] as $item) {
                $list[] = static::newFromBuilder($item);
            }
        }

        $paginator = new LengthAwarePaginator($list, $result['meta']['pagination']['total'], $perPage);
        $paginator->setPath(url()->current());
        return $paginator;
    }

    /**
     * 分批处理数据
     * @param int $count
     * @param callable $callback
     * @return bool
     */
    public function chunk($count, callable $callback)
    {
        // 添加默认排序
        if (empty($this->queries['_order'])) {
            $this->orderBy($this->getKeyName(), 'asc');
        }

        $page = 1;
        do {
            // We'll execute the query for the given page and get the results. If there are
            // no results we can just break and return from here. When there are results
            // we will call the callback with the current chunk of these results here.
            $results = $this->forPage($page, $count)->get();

            $countResults = $results->count();
            if ($countResults == 0) {
                break;
            }

            // On each chunk result set, we will pass them to the callback and then let the
            // developer take care of everything within the callback, which allows us to
            // keep the memory low for spinning through large result sets for working.
            if ($callback($results, $page) === false) {
                return false;
            }

            unset($results);
            $page++;
        } while ($countResults == $count);

        return true;
    }

    /**
     * 删选
     * @param $column
     * @param null $operator
     * @param null $value
     * @param string $boolean
     * @return static
     */
    public function where($column, $operator = null, $value = null, $boolean = 'and')
    {
        if ($value == NULL) {
            $value = $operator;
            $operator = '=';
        }

        $this->queries += [$column => []];
        $this->queries[$column][$operator] = $value;

        return $this;
    }

    /**
     * 排序
     * @param $column
     * @param string $direction
     * @return static
     */
    public function orderBy($column, $direction = 'asc')
    {
        $this->queries += ['_order' => []];
        $this->queries['_order'][$column] = $direction;

        return $this;
    }

    /**
     * 分页
     * @param  int  $page
     * @param  int  $perPage
     * @return static
     */
    public function forPage($page, $perPage = 15)
    {
        $this->queries['_page'] = $page;
        $this->queries['_size'] = $perPage;
        unset($this->queries['_limit']);

        return $this;
    }

    /**
     * @param int $value
     * @return static
     */
    public function limit($value)
    {
        $this->queries['_limit'] = $value;
        return $this;
    }

    /**
     * @param int $value
     * @return static
     */
    public function take($value)
    {
        return $this->limit($value);
    }

    /**
     * @param array $columns
     * @return static
     */
    public function first($columns = ['*'])
    {
        return $this->take(1)->get($columns)->first();
    }

    # 辅助方法 ##########################################################################################################
}