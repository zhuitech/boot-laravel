<?php

namespace ZhuiTech\BootLaravel\Helpers;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use JsonSchema\Exception\JsonDecodingException;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use ZhuiTech\BootLaravel\Exceptions\RestCodeException;
use ZhuiTech\BootLaravel\Exceptions\UnableToExecuteRequestException;
use Exception;
use ZhuiTech\BootLaravel\Models\User;
use Illuminate\Support\Facades\App;

/**
 * Restful客户端
 * Class Http.
 */
class RestClient
{
    /**
     * Http client.
     *
     * @var HttpClient
     */
    protected $client;

    /**
     * 全局参数
     * @var array
     */
    public static $globals = [
        'curl' => [
            CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
        ],
    ];

    /**
     * 默认请求参数
     * @var array
     */
    protected $defaults = [
        'headers' => [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ],
    ];

    /**
     * 默认的服务器
     * @var null
     */
    protected $server = NULL;

    /**
     * 模拟用户
     * @var User
     */
    protected $user = NULL;

    /**
     * 返回一个新实例
     * @param $server
     * @return $this
     */
    public static function server($server = NULL)
    {
        $instance = new static();
        $instance->user = Auth::user();

        if (!empty($server)) {
            $instance->server = $server;
        }

        return $instance;
    }

    /**
     * Set GuzzleHttp\Client.
     *
     * @param \GuzzleHttp\Client $client
     *
     * @return $this
     */
    public function client(HttpClient $client)
    {
        $this->client = $client;
        return $this;
    }

    /**
     * Set guzzle settings.
     *
     * @param array $defaults
     * @return $this
     */
    public function options($defaults = [])
    {
        $this->defaults = array_merge(self::$globals, $defaults);
        return $this;
    }

    /**
     * 以用户身份请求
     * @param User $user
     * @return $this
     */
    public function as(User $user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * GET request.
     *
     * @param $url
     * @param array $queries
     * @return array|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function get($url, array $queries = [])
    {
        return $this->request($url, 'GET', [
            'query' => $queries
        ]);
    }

    /**
     * POST request.
     *
     * @param $url
     * @param array $data
     * @param array $queries
     * @return array|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function post($url, $data = [], $queries = [])
    {
        return $this->request($url, 'POST', [
            'query' => $queries,
            'body' => json_encode($data, JSON_UNESCAPED_UNICODE)
        ]);
    }

    /**
     * PUT request.
     *
     * @param $url
     * @param array $data
     * @param array $queries
     * @return array|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function put($url, $data = [], $queries = [])
    {
        return $this->request($url, 'PUT', [
            'query' => $queries,
            'body' => json_encode($data, JSON_UNESCAPED_UNICODE)
        ]);
    }

    /**
     * DELETE request.
     *
     * @param $url
     * @param array $data
     * @param array $queries
     * @return array|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function delete($url, $data = [], $queries = [])
    {
        return $this->request($url, 'DELETE', [
            'query' => $queries,
            'body' => json_encode($data, JSON_UNESCAPED_UNICODE)
        ]);
    }

    /**
     * Upload file.
     *
     * @param $url
     * @param array $files
     * @param array $form
     * @param array $queries
     * @return array|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function upload($url, array $files = [], array $form = [], array $queries = [])
    {
        $multipart = [];

        foreach ($files as $name => $file) {
            $path = $file;
            if ($file instanceof UploadedFile) {
                $path = $file->path();
            }

            $multipart[] = [
                'name' => $name,
                'contents' => fopen($path, 'r'),
            ];
        }

        foreach ($form as $name => $contents) {
            $multipart[] = compact('name', 'contents');
        }

        $headers = $this->defaults['headers'];
        unset($headers['Content-Type']);

        return $this->request($url, 'POST', [
            'query' => $queries,
            'multipart' => $multipart,
            'headers' => $headers
        ]);
    }

    /**
     * Make a request.
     *
     * @param $path
     * @param string $method
     * @param array $options
     * @return array|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function request($path, $method = 'GET', $options = [])
    {
        $url = $this->getUrl($path);
        $method = strtoupper($method);
        $options = array_merge($this->defaults, $options);

        // 传递请求头信息
        if (!app()->runningInConsole()) {
            foreach (['X-User', 'X-User-Type', 'X-Language'] as $key) {
                if (Request::hasHeader($key)) {
                    $options['headers'][$key] = Request::header($key);
                }
            }
        }

        try {
            $response = $this->getClient()->request($method, $url, $options);
            $content = $response->getBody()->getContents();
            $result = json_decode($content, true);

            // 处理JSON解析失败
            if (JSON_ERROR_NONE !== json_last_error()) {
                return Restful::format(
                    [
                        'error' => json_last_error_msg(),
                        'url' => $url,
                        'method' => $method,
                        'options' => $options,
                        'status' => $response->getStatusCode(),
                        'content' => $content
                    ], false, REST_DATA_JSON_FAIL
                );
            }
            else {
                return $result;
            }
        } catch (RequestException $e) {
            $data = [
                'error' => $e->getMessage(),
                'url' => $url,
                'method' => $method,
                'options' => $options,
            ];
            if ($e->hasResponse()) {
                $data += [
                    'status' => $e->getResponse()->getStatusCode(),
                    'content' => $e->getResponse()->getBody()->getContents()
                ];
            }

            return Restful::format($data, false, REST_REMOTE_FAIL);
        }
    }

    /**
     * Return GuzzleHttp\Client instance.
     *
     * @return \GuzzleHttp\Client
     */
    protected function getClient()
    {
        if (!($this->client instanceof HttpClient)) {
            $this->client = new HttpClient();
        }

        return $this->client;
    }

    /**
     * 获取正确的请求地址
     * @param $path
     * @return mixed
     */
    public function getUrl($path)
    {
        if (str_contains($path, '://') || empty($this->server)) {
            return $path;
        }

        $prefix = '';
        if (str_contains($this->server, '://')) {
            $prefix = $this->server;
        }
        else {
            $prefix = env('SERVICE_' . strtoupper($this->server), false);
        }

        return rtrim($prefix, '/') . '/' . ltrim($path, '/');
    }
}
