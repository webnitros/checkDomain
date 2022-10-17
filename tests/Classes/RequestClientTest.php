<?php
/**
 * Created by Andrey Stepanenko.
 * User: webnitros
 * Date: 17.10.2022
 * Time: 14:34
 */

namespace Tests\Classes;

use App\Check;
use Tests\TestCase;

class RequestClientTest extends TestCase
{

    public function urls()
    {
        return [
            [
                'url' => 'https://fandeco.ru/robots.txt',
                'count' => 0
            ],
            [
                'url' => 'http://fandeco.ru/robots.txt',
                'count' => 1
            ],
            [
                'url' => 'http://www.fandeco.ru/robots.txt',
                'count' => 1
            ],
            [
                'url' => 'http://www.fandeco.ru/catalog/podvesnyie-svetilniki/interer-dlya-bolshix-zalov',
                'count' => 1
            ],
        ];
    }

    public function urls_de2()
    {
        return [
            [
                'url' => 'https://dev2.massive.ru/robots.txt',
                'count' => 0
            ],
            [
                'url' => 'http://dev2.massive.ru/robots.txt',
                'count' => 1
            ],
            [
                'url' => 'http://www.dev2.massive.ru/robots.txt',
                'count' => 1
            ],
            [
                'url' => 'http://www.dev2.massive.ru/catalog/podvesnyie-svetilniki/interer-dlya-bolshix-zalov',
                'count' => 1
            ],
        ];
    }

    /**
     * @dataProvider urls_de2
     */
    public function testStatusNoRedirects($url, $countRedirect)
    {
        $data = Check::url($url);
        $count = !empty($data['redirects']) ? count($data['redirects']) : 0;

        $urls = '';
        if (!empty($data['redirects'])) {
            $urls = array_column($data['redirects'], 'to');
            $urls = implode(',', $urls);
        }
        self::assertEquals($countRedirect, $count, 'url ' . $url . ' urls ' . $urls);
    }
}
