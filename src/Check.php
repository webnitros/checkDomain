<?php
/**
 * Created by Andrey Stepanenko.
 * User: webnitros
 * Date: 17.10.2022
 * Time: 14:34
 */

namespace CheckDomain;

use CheckDomain\Helpers\RequestClient;

class Check
{

    /**
     * @param string $url
     * @return array|null
     */
    public static function url(string $url)
    {
        $RequestClient = new RequestClient();
        $RequestClient->onRedirect(); // Включение записи редиректов
        $status = $RequestClient->statusRedirects($url);
        $data = [
            'status' => $status,
            'redirects' => $RequestClient->getRedirects(),
        ];
        return $data;
    }
}
