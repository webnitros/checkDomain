<?php

namespace CheckDomain\Helpers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use function DI\string;

/**
 * Created by Andrey Stepanenko.
 * User: webnitros
 * Date: 26.08.2021
 * Time: 14:27
 */
final class RequestClient extends Client
{
    /* @var string|null $_msg */
    protected $_msg;

    /* @var string|null $_method */
    protected $_method;
    /* @var string|null $_url */
    protected $_url;

    /* @var string $_user_agent */
    protected $_user_agent = 'SiteChecker.guru/1.0.0';

    /* @var boolean $_exception_error */
    protected $_exception_error = false;


    /* @var int $_timeout */
    protected $_timeout = 5;
    /* @var int $_connect_timeout */
    protected $_connect_timeout = 3;
    /* @var array $_options */
    private $_options = [];
    /**
     * @var int
     */
    private $_response_status;
    /* @var array $_config */
    private $_config = [];

    /**
     * @param array $setting
     * @param false $verify // Проверка локального сертификата
     */
    public function __construct(array $setting = [], $verify = false)
    {
        $localConfig = array_merge($setting, ['verify' => $verify]);
        parent::__construct($localConfig);
    }

    /**
     * Статус страницы с запретом на редирект
     * @param string $url
     * @return null|int
     */
    public function statusNoRedirects($url)
    {
        $this->sendRequest('get', $url, [
            'allow_redirects' => false // Запрет на перенаправление
        ]);
        return $this->statusCode();
    }

    /**
     * Статус страницы с возможность перенаправления страницы
     * @param string $url
     * @return null|int
     */
    public function statusRedirects($url)
    {
        $this->sendRequest('get', $url, [
            'allow_redirects' => true // Запрет на перенаправление
        ]);
        return $this->statusCode();
    }


    public function getMsg()
    {
        return $this->_msg;
    }


    public function userAgent($string)
    {
        $this->_user_agent = $string;
    }

    public function timeout(int $seconds)
    {
        $this->_timeout = $seconds;
    }

    public function connectTimeout(int $seconds)
    {
        $this->_connect_timeout = $seconds;
    }


    public function statusCode()
    {
        if (!$this->_response_status) {
            if (method_exists($this->response, 'getStatusCode')) {
                $status = $this->response->getStatusCode();
            }
        } else {
            $status = $this->_response_status;
        }

        return (int)$status;
    }


    public function contentType()
    {
        $ContentType = null;
        $str = $this->response->getHeaderLine('Content-Type');
        if (!empty($str)) {
            $array = explode(';', $str);
            $co = !empty($array[0]) ? $array[0] : '';
            $ContentType = $co;
        }
        return (string)$ContentType;
    }


    /* @var array|null $_redirects */
    protected $_redirects;

    /* @var boolean $_onRedirect */
    protected $_onRedirect = false;


    /**
     * Включение записи редиректов
     */
    public function onRedirect()
    {
        if (!$this->_onRedirect) {
            $this->_onRedirect = true;
            $this->_config['allow_redirects'] = [
                'max' => 10,        // allow at most 10 redirects.
                'strict' => true,      // use "strict" RFC compliant redirects.
                'referer' => true,      // add a Referer header
                #'protocols' => ['https'], // only allow https URLs
                'on_redirect' => function (
                    RequestInterface  $request,
                    ResponseInterface $response,
                    UriInterface      $uri
                ) {
                    $this->_redirects[] = [
                        'from' => (string)$request->getUri(),
                        'from_target' => (string)$request->getRequestTarget(),
                        'to' => (string)$uri,
                        'to_https' => (string)$uri->getScheme(),
                        'status_code' => $response->getStatusCode(),
                    ];
                    if (count($this->_redirects) > 10) {
                        throw new \Exception('Циклическая переадресация более 10 раз подряд. Процесс остановлен');
                    }
                },
                'track_redirects' => true
            ];
        }
    }

    /**
     * @param $key
     * @param $value
     */
    public function setOptions($key, $value)
    {
        $this->_config[$key] = $value;
    }

    /**
     * Вернет все редиректы
     * @return array|null
     */
    public function getRedirects()
    {
        return $this->_redirects;
    }


    /**
     * @param $method
     * @param $url
     * @param array $options
     * @return bool
     */
    private function sendRequest($method, $url, $config = [])
    {
        $this->_exception_error = false;
        $this->_redirects = null;
        $this->_response_status = 0;
        $this->_error = false;
        $this->_msg = null;
        $this->_url = null;
        $this->_method = null;
        $this->_options = [];
        $this->response = null;
        $e = null;
        $result = false;
        try {

            $config = array_merge([
                'http_errors' => true, // Для выброса статуса страницы 400
                'timeout' => $this->_timeout, // Таймаут для содинения
                'connect_timeout' => $this->_connect_timeout, // Время ожидания для конекта
                'headers' => [
                    'User-Agent' => $this->_user_agent
                ],
            ], $this->_config);
            $options = array_merge($config, $config);

            $this->_options = $options;
            $this->_url = $url;
            $this->_method = $method;
            $this->response = $this->{$method}($url, $options);
            $result = true;
        } catch (BadResponseException $e) {
            $this->_error = true;
            $this->_response_status = 500;
        } catch (RequestException $e) {
            $this->_error = true;
            $this->_response_status = 400;
        } catch (\Exception $e) {
            $this->_error = true;
        }

        if (!is_null($e)) {
            if ($e->getCode()) {
                $this->_response_status = $e->getCode();
            }
            if ($e->hasResponse()) {
                $this->response = $e->getResponse();
            }
            $this->_msg = $e->getMessage();
        }

        return $result;
    }


    /**
     * Вернет true если было исключение
     * @return boolean
     */
    public function isExceptionError()
    {
        return $this->_exception_error;
    }

    /**
     * @return mixed
     */
    public function getOptions()
    {
        return $this->_options;
    }

    /**
     * @return mixed
     */
    public function getDebug()
    {
        return [
            'success' => !$this->_error,
            'response_status' => $this->statusCode(),
            'url' => $this->_url,
            'method' => $this->_method,
            'options' => $this->_options,
            'msg' => $this->_msg,
            'on_redirect' => $this->_onRedirect,
        ];
    }


    /**
     * Вернет весь ответ в string
     * @return string|null
     */
    public function getResponseStr()
    {
        return $this->response ? Psr7\str($this->response) : null;
    }
}
