<?php

namespace ZhuiTech\LaraBoot\Helpers;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use ZhuiTech\LaraBoot\Exceptions\RestCodeException;
use ZhuiTech\LaraBoot\Exceptions\UnableToExecuteRequestException;
use Exception;

/**
 * Class Http.
 */
class RestClient
{
    /**
     * Used to identify handler defined by client code
     * Maybe useful in the future.
     */
    const USER_DEFINED_HANDLER = 'userDefined';

    /**
     * Http client.
     *
     * @var HttpClient
     */
    protected $client;

    /**
     * The middlewares.
     *
     * @var array
     */
    protected $middlewares = [];

    /**
     * @var array
     */
    protected static $globals = [
        'curl' => [
            CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
        ],
    ];

    /**
     * Guzzle client default settings.
     *
     * @var array
     */
    protected static $defaults = [
        'headers' => [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ],
    ];

    /**
     * Set guzzle default settings.
     *
     * @param array $defaults
     */
    public static function setDefaultOptions($defaults = [])
    {
        self::$defaults = array_merge(self::$globals, $defaults);
    }

    /**
     * Return current guzzle default settings.
     *
     * @return array
     */
    public static function getDefaultOptions()
    {
        return self::$defaults;
    }

    /**
     * GET request.
     *
     * @param string $url
     * @param array $queries
     *
     * @return ResponseInterface
     *
     * @throws Exception
     */
    public function get($url, array $queries = [])
    {
        $response = $this->request($url, 'GET', [
            'query' => $queries
        ]);

        return self::parseJSON($response->getBody());
    }

    /**
     * POST request.
     *
     * @param string $url
     * @param array|string $options
     *
     * @param array $queries
     * @return ResponseInterface
     *
     * @throws Exception
     */
    public function post($url, $options = [], $queries = [])
    {
        $response = $this->request($url, 'POST', [
            'query' => $queries,
            'body' => json_encode($options, JSON_UNESCAPED_UNICODE)
        ]);

        return self::parseJSON($response->getBody());
    }

    /**
     * POST request.
     *
     * @param string $url
     * @param array|string $options
     *
     * @param array $queries
     * @return ResponseInterface
     *
     * @throws Exception
     */
    public function put($url, $options = [], $queries = [])
    {
        $response = $this->request($url, 'PUT', [
            'query' => $queries,
            'body' => json_encode($options, JSON_UNESCAPED_UNICODE)
        ]);

        return self::parseJSON($response->getBody());
    }

    /**
     * POST request.
     *
     * @param string $url
     * @param array|string $options
     *
     * @param array $queries
     * @return ResponseInterface
     *
     * @throws Exception
     */
    public function delete($url, $options = [], $queries = [])
    {
        $response = $this->request($url, 'DELETE', [
            'query' => $queries,
            'body' => json_encode($options, JSON_UNESCAPED_UNICODE)
        ]);

        return self::parseJSON($response->getBody());
    }

    /**
     * Upload file.
     *
     * @param string $url
     * @param array $files
     * @param array $form
     *
     * @param array $queries
     * @return ResponseInterface
     *
     * @throws Exception
     */
    public function upload($url, array $files = [], array $form = [], array $queries = [])
    {
        $multipart = [];

        foreach ($files as $name => $path) {
            $multipart[] = [
                'name' => $name,
                'contents' => fopen($path, 'r'),
            ];
        }

        foreach ($form as $name => $contents) {
            $multipart[] = compact('name', 'contents');
        }

        $response = $this->request($url, 'POST', [
            'query' => $queries,
            'multipart' => $multipart
        ]);

        return self::parseJSON($response->getBody());
    }

    /**
     * Set GuzzleHttp\Client.
     *
     * @param \GuzzleHttp\Client $client
     *
     * @return RestClient
     */
    public function setClient(HttpClient $client)
    {
        $this->client = $client;
        return $this;
    }

    /**
     * Return GuzzleHttp\Client instance.
     *
     * @return \GuzzleHttp\Client
     */
    public function getClient()
    {
        if (!($this->client instanceof HttpClient)) {
            $this->client = new HttpClient();
        }

        return $this->client;
    }

    /**
     * Add a middleware.
     *
     * @param callable $middleware
     *
     * @return $this
     */
    public function addMiddleware(callable $middleware)
    {
        array_push($this->middlewares, $middleware);

        return $this;
    }

    /**
     * Return all middlewares.
     *
     * @return array
     */
    public function getMiddlewares()
    {
        return $this->middlewares;
    }

    /**
     * Make a request.
     *
     * @param string $url
     * @param string $method
     * @param array $options
     *
     * @return ResponseInterface
     *
     * @throws Exception
     */
    public function request($url, $method = 'GET', $options = [])
    {
        $method = strtoupper($method);
        $options = array_merge(self::$defaults, $options);
        $options['handler'] = $this->getHandler();

        try {
            $response = $this->getClient()->request($method, $url, $options);
        } catch (RequestException $e) {
            $data = [
                'url' => $url,
                'method' => $method,
                'options' => $options
            ];

            if ($e->hasResponse()) {
                $data += [
                    'status' => $e->getResponse()->getStatusCode(),
                ];

                try {
                    $data['content'] = $this->parseJSON($e->getResponse()->getBody());
                }
                catch (Exception $ex){
                    $data['content'] = $this->parseJSON($e->getResponse()->getBody()->getContents());
                }
            }

            throw new RestCodeException(REST_REMOTE_FAIL, $data);
        }

        return $response;
    }

    /**
     * @param \Psr\Http\Message\ResponseInterface|string $body
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function parseJSON($body)
    {
        if ($body instanceof ResponseInterface) {
            $body = $body->getBody();
        }

        // XXX: json maybe contains special chars. So, let's FUCK the WeChat API developers ...
        $body = $this->fuckTheWeChatInvalidJSON($body);

        if (empty($body)) {
            return false;
        }

        $contents = json_decode($body, true);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \Exception('Failed to parse JSON: '.json_last_error_msg());
        }

        return $contents;
    }

    /**
     * Filter the invalid JSON string.
     *
     * @param \Psr\Http\Message\StreamInterface|string $invalidJSON
     *
     * @return string
     */
    protected function fuckTheWeChatInvalidJSON($invalidJSON)
    {
        return preg_replace('/[\x00-\x1F\x80-\x9F]/u', '', trim($invalidJSON));
    }

    /**
     * Build a handler.
     *
     * @return HandlerStack
     */
    protected function getHandler()
    {
        $stack = HandlerStack::create();

        foreach ($this->middlewares as $middleware) {
            $stack->push($middleware);
        }

        if (isset(static::$defaults['handler']) && is_callable(static::$defaults['handler'])) {
            $stack->push(static::$defaults['handler'], self::USER_DEFINED_HANDLER);
        }

        return $stack;
    }
}
