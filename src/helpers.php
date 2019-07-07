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

if (! function_exists('uploader_save')) {
    /**
     * 保存上传文件
     *
     * @param \Illuminate\Http\Request $request
     * @param string $strategy
     * @return array|bool|null
     * @throws Exception
     */
    function uploader_save(\Illuminate\Http\Request $request, $strategy = 'default')
    {
        $config = uploader_strategy($strategy);

        $inputName = array_get($config, 'input_name', 'file');
        $directory = array_get($config, 'directory', '{Y}/{m}/{d}');
        $disk = array_get($config, 'disk', 'public');

        if (!$request->hasFile($inputName)) {
            throw new \Exception('文件不存在或为空');
        }
        $file = $request->file($inputName);

        Event::fire(new FileUploading($file));

        $result = app(FileUpload::class)->store($file, $disk, $directory);
        if (!is_null($modified = Event::fire(new FileUploaded($file, $result, $strategy, $config), [], true))) {
            $result = $modified;
        }

        return $result;
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

if (! function_exists('setting')) {
    /**
     * Get / set the specified settings value.
     *
     * If an array is passed as the key, we will assume you want to set an array of values.
     *
     * @param  array|string  $key
     * @param  mixed  $default
     * @return mixed|\Illuminate\Config\Repository
     */
    function setting($key = null, $default = null)
    {
        if (is_null($key)) {
            return app('setting');
        }

        if (is_array($key)) {
            return app('setting')->set($key);
        }

        return app('setting')->get($key, $default);
    }
}

if (! function_exists('yuan')) {
    /**
     * 格式化以分为单位的金额
     *
     * @param $amount
     * @return string
     */
    function yuan($amount)
    {
        return number_format($amount / 100, 2, ".", "");
    }
}

if (! function_exists('transform_item')) {
    function transform_item($item, \League\Fractal\TransformerAbstract $transformer)
    {
        $data = new Item($item, $transformer);

        $fractal = resolve(Manager::class);
        return $fractal->createData($data)->toArray();
    }
}

if (! function_exists('transform_list')) {
    function transform_list($list, \League\Fractal\TransformerAbstract $transformer)
    {
        $data = new Collection($list, $transformer);

        $fractal = resolve(Manager::class);
        return $fractal->createData($data)->toArray();
    }
}
