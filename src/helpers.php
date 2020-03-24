<?php
/**
 * Created by PhpStorm.
 * User: andrew
 * Date: 17/8/30
 * Time: 18:05
 */

use Illuminate\Support\Str;
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

if (! function_exists('local_path')) {
    /**
     * 获取本地路径
     *
     * @param $real_path
     * @return mixed
     */
    function local_path($path, $disk = null)
    {
        return Storage::disk($disk)->path($path);
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
        if (empty($path)) {
            return null;
        }

        // 生成URL
        if (URL::isValidUrl($path)) {
            $url = $path;
        } else {
            $url = Storage::disk($disk)->url($path);
        }

        // 返回CDN地址
        return cdn($url);
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
        $cdn = trim(env('CDN_URL', ''), '/');

        // 没有配置CDN
        if (empty($cdn)) {
            return $path;
        }

        if (URL::isValidUrl($path)) {
            // 替换域名
            return str_replace(env('APP_URL', ''), $cdn, $path);
        } else {
            // 直接添加前缀
            return $cdn . '/' . trim($path, '/');
        }
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
     * @param \Illuminate\Support\Collection $list
     * @param \League\Fractal\TransformerAbstract $transformer
     * @return array
     */
    function transform_list($list, \League\Fractal\TransformerAbstract $transformer = null)
    {
        if (empty($transformer)) {
            $class = \ZhuiTech\BootLaravel\Transformers\ModelTransformer::defaultTransformer($list->first());
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

if (!function_exists('unique_no')) {
    /**
     * 创建唯一编号
     *
     * uniqid()：此函数获取一个带前缀、基于当前时间微秒数的唯一ID。
     * substr(uniqid(), 7, 13)：由于uniqid()函数生成的结果前面7位很久才会发生变化，所以有或者没有对于我们没有多少影响，所以我们截取后面经常发生变化的几位。
     * str_split(substr(uniqid(), 7, 13),1)：我们将刚刚生成的字符串进行分割放到数组里面，str_split()第二个参数是每个数组元素的长度。
     * array_map('ord', str_split(substr(uniqid(), 7, 13),1)))：返回字符串的首个字符的 ASCII值，意思就是把第二个参数生成的数组每个元素全部转换为数字，因为刚刚我们截取的字符串中含有字母，不适合订单号。
     * 由于刚刚生成的随机数可能会长短不一（原因就是，每个字符转换为ASCII值可能不一样，有些是2位，有些可能是一位），所以我们截取0-8
     * 
     * @param string $prefix
     * @return string
     */
    function unique_no($prefix = '')
    {
        $uniqid = substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
        return $prefix . date('Ymd') . $uniqid . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
    }
}

if (!function_exists('random_string')) {
    function random_string($length = 10)
    {
        $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyz';
        return strtoupper(substr(str_shuffle($permitted_chars), 0, $length));
    }
}

if (!function_exists('settings')) {
    /**
     * get settings.
     * @param null $key
     * @param null $value
     * @return \Illuminate\Foundation\Application|mixed|string
     */
    function settings($key = null, $value = null)
    {
        if (is_null($key)) {
            return app('system_setting');
        }

        if (is_string($key)) {
            return app('system_setting')->getSetting($key, $value);
        }

        if (is_array($key)) {
            return app('system_setting')->setSetting($key);
        }

        return '';
    }
}

if (!function_exists('is_version')) {
    /**
     * 是否是版本
     * @param $version
     * @return bool
     */
    function is_version($version)
    {
        return Str::startsWith(app()->version(), $version);
    }
}

if (!function_exists('var_export_new')) {
    function var_export_new($expression, $return = FALSE)
    {
        $export = var_export($expression, TRUE);
        $export = preg_replace("/^([ ]*)(.*)/m", '$1$1$2', $export);

        $array = preg_split("/\r\n|\n|\r/", $export);
        $array = preg_replace(["/\s*array\s\($/", "/\)(,)?$/", "/\s=>\s$/"], [NULL, ']$1', ' => ['], $array);
        $array = preg_replace("/\d+ =>/", '', $array);

        $export = join(PHP_EOL, array_filter(["["] + $array));

        if ((bool)$return) {
            return $export;
        } else {
            echo $export;
        }
    }
}