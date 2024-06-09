<?php

namespace Shasoft\CacheInvalidation\Tests\Unit;


use Shasoft\CacheInvalidation\Tests\CacheItemFile;
use Shasoft\CacheInvalidation\Tests\CacheItemFilesPrepare;
use Shasoft\CacheInvalidation\Tests\CacheItemFilePrepare;
use Shasoft\CacheInvalidation\Tests\CacheItemUserPrepare;

class MainLifetimePrepareTest extends Base
{
    //
    public function testGet(): void
    {

        CacheItemFilesPrepare::prepare();
        $val1 = CacheItemFilesPrepare::get();
        self::assertEquals($val1, "1:FilePrepare(1,?)/FilePrepare(2,?)/FilePrepare(3,?)");
        $val2 = CacheItemFilesPrepare::get();
        self::assertEquals($val1, $val2);
        sleep(2);
        $val3 = CacheItemFilesPrepare::get();
        self::assertEquals($val3, "2:FilePrepare(1,?)/FilePrepare(2,?)/FilePrepare(3,?)");
        $this->assertCacheEquals(
            array(
                'CacheItemFilePrepare:1' =>
                array(
                    'value' => 'FilePrepare(1,?)',
                    'label' => 1,
                    'labels' =>
                    array(),
                ),
                'CacheItemFilePrepare:2' =>
                array(
                    'value' => 'FilePrepare(2,?)',
                    'label' => 2,
                    'labels' =>
                    array(),
                ),
                'CacheItemFilePrepare:3' =>
                array(
                    'value' => 'FilePrepare(3,?)',
                    'label' => 3,
                    'labels' =>
                    array(),
                ),
                'CacheItemFilesPrepare:a:0:{}' =>
                array(
                    'value' => '2:FilePrepare(1,?)/FilePrepare(2,?)/FilePrepare(3,?)',
                    'label' => 5,
                    'lifetime' => 1717772860,
                ),
                '#CacheItemFilesPrepare:a:0:{}' => 5,
            )
        );
        $this->assertInvokesEquals(
            CacheItemFilesPrepare::class . '::prepareRead([{}])',
            CacheItemFilePrepare::class . '::prepareRead([{},{},{}])',
            CacheItemFilesPrepare::class . '->read():"1:FilePrepare(1,?)/FilePrepare(2,?)/FilePrepare(3,?)"',
            CacheItemFilePrepare::class . '->read():"FilePrepare(1,?)"',
            CacheItemFilePrepare::class . '->read():"FilePrepare(2,?)"',
            CacheItemFilePrepare::class . '->read():"FilePrepare(3,?)"',
            CacheItemFilesPrepare::class . '::prepareRead([{}])',
            CacheItemFilesPrepare::class . '->read():"2:FilePrepare(1,?)/FilePrepare(2,?)/FilePrepare(3,?)"'
        );
    }
}
