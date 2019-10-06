<?php
/**
 * Created by PhpStorm.
 * User: andrew
 * Date: 17/8/30
 * Time: 18:05
 */

use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use Overtrue\LaravelUploader\Events\FileUploaded;
use Overtrue\LaravelUploader\Events\FileUploading;
use Overtrue\LaravelUploader\Services\FileUpload;

if (! function_exists('relative_path')) {
    /**
     * 从物理路径获取相对路径
     *
     * @param $real_path
     * @return mixed
     */
    function relative_path($real_path)
    {
        return str_replace(public_path(), '', $real_path);
    }
}

if (! function_exists('magic_replace')) {
    /**
     * 替换魔法变量
     * @param $url
     * @param $data
     * @return null|string
     */
    function magic_replace($url, $data)
    {
        if (!empty($url)) {
            $replacements = [];
            foreach ($data as $key => $value) {
                $replacements["{{$key}}"] = $value;
            }
            return strtr($url, $replacements);
        }
        return $url;
    }
}

if (! function_exists('cdn')) {
    /**
     * 生成CDN地址
     * @param $path
     * @return string
     */
    function cdn($path)
    {
        $cdn = env('CDN_URL', '');
        return $cdn.'/'.trim($path, '/');
    }
}

if (! function_exists('yuan')) {
    /**
     * 格式化以分为单位的金额
     *
     * @param $amount
     * @return string
     */
    function yuan($amount, $symbol = false)
    {
        $value = number_format($amount / 100, 2, ".", "");
        
        if ($symbol) {
            $value = '￥' . $value;
        }
        
        return $value;
    }
}

if (! function_exists('transform_item')) {
    /**
     * 转换对象
     *
     * @param $item
     * @param \League\Fractal\TransformerAbstract $transformer
     * @return array
     */
    function transform_item($item, \League\Fractal\TransformerAbstract $transformer = null)
    {
        if (empty($transformer)) {
            $class = \ZhuiTech\BootLaravel\Transformers\ModelTransformer::defaultTransformer($item);
            $transformer = new $class;
        }
        
        $data = new Item($item, $transformer);

        $fractal = resolve(Manager::class);
        return $fractal->createData($data)->toArray();
    }
}

if (! function_exists('transform_list')) {
    /**
     * 转换集合
     *
     * @param $list
     * @param \League\Fractal\TransformerAbstract $transformer
     * @return array
     */
    function transform_list($list, \League\Fractal\TransformerAbstract $transformer = null)
    {
        if (empty($transformer)) {
            $class = \ZhuiTech\BootLaravel\Transformers\ModelTransformer::defaultTransformer($item);
            $transformer = new $class;
        }
        
        $data = new Collection($list, $transformer);

        $fractal = resolve(Manager::class);
        return $fractal->createData($data)->toArray();
    }
}

if (! function_exists('morph_alias')) {
    /**
     * 获取别名
     *
     * @param $class
     * @return string
     */
    function morph_alias($class)
    {
        $map = \Illuminate\Database\Eloquent\Relations\Relation::$morphMap;

        foreach ($map as $alias => $fullname) {
            if ($class == $fullname) {
                return $alias;
            }
        }
        return $class;
    }
}

if (! function_exists('storage_url')) {
    /**
     * 获取存储文件URL
     * 
     * @param $path
     * @param null $disk
     * @return string
     */
    function storage_url($path, $disk = null)
    {
        if (URL::isValidUrl($path)) {
            return $path;
        }

        return Storage::disk($disk)->url($path);
    }
}

if (!function_exists('unique_no')) {
    /**
     * 创建唯一编号
     * 
     * @param string $prefix
     * @return string
     */
    function unique_no($prefix = 'O')
    {
        // 订单号码主体（YYYYMMDDHHIISSNNNNNNNN）
        $order_id_main = date('Ymd') . rand(100000000,999999999);

        // 订单号码主体长度
        $order_id_len = strlen($order_id_main);
        $order_id_sum = 0;

        for($i=0; $i<$order_id_len; $i++){
            $order_id_sum += (int)(substr($order_id_main, $i,1));
        }

        // 唯一订单号码（YYYYMMDDHHIISSNNNNNNNNCC）
        $order_id = $order_id_main . str_pad((100 - $order_id_sum % 100) % 100,2,'0',STR_PAD_LEFT);
        return $prefix.$order_id;
    }
}
