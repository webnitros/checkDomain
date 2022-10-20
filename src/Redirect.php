<?php
/**
 * Created by Andrey Stepanenko.
 * User: webnitros
 * Date: 17.10.2022
 * Time: 14:34
 */

namespace CheckDomain;

use CheckDomain\Exceptions\ExceptionRedirectDomain;
use CheckDomain\Helpers\RequestClient;

class Redirect
{

    /**
     * @param string $url
     * @return Response
     */
    public static function url(string $url)
    {
        $RequestClient = new RequestClient();
        $RequestClient->onRedirect(); // Включение записи редиректов
        $status = $RequestClient->statusRedirects($url);
        $Response = new Response($url);
        if (!empty($status)) {
            $Response->setStatus($status);
            // set redirects
            if ($redirects = $RequestClient->getRedirects()) {
                $Response->setRedirects($redirects);
            }
            $Response->preparationResults();
        } else {
            throw new ExceptionRedirectDomain($RequestClient->getMsg());
        }
        return $Response;
    }
}
