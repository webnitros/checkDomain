<?php
/**
 * Created by Andrey Stepanenko.
 * User: webnitros
 * Date: 20.10.2022
 * Time: 12:02
 */

namespace Tests;

use Tests\TestCase;

class RedirectTest extends TestCase
{
    public function testUrlStatus()
    {
        $Response = \CheckDomain\Redirect::url('http://www.buseo.ru/');
        self::assertEquals(200, $Response->status());
        self::assertEquals(1, $Response->total());
        self::assertCount(2, $Response->urls());
    }
}
