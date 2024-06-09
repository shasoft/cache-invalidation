<?php

namespace Shasoft\CacheInvalidation\Tests\Unit;


use Shasoft\CacheInvalidation\Tests\CacheItemFile;
use Shasoft\CacheInvalidation\Tests\CacheItemFiles;
use Shasoft\CacheInvalidation\Tests\CacheItemFilePrepare;
use Shasoft\CacheInvalidation\Tests\CacheItemUserPrepare;

class MainLifetimeTest extends Base
{
    //
    public function testGet(): void
    {

        $val1 = CacheItemFiles::get();
        self::assertEquals($val1, "1:File(1,?)/File(2,?)/File(3,?)");
        $val2 = CacheItemFiles::get();
        self::assertEquals($val1, $val2);
        sleep(1);
        $val3 = CacheItemFiles::get();
        self::assertEquals($val3, "2:File(1,?)/File(2,?)/File(3,?)");
        $this->assertCacheEquals(
            array(
                'CacheItemFile:1' =>
                array(
                    'value' => 'File(1,?)',
                    'label' => 1,
                    'labels' =>
                    array(),
                ),
                'CacheItemFile:2' =>
                array(
                    'value' => 'File(2,?)',
                    'label' => 2,
                    'labels' =>
                    array(),
                ),
                'CacheItemFile:3' =>
                array(
                    'value' => 'File(3,?)',
                    'label' => 3,
                    'labels' =>
                    array(),
                ),
                'CacheItemFiles:a:0:{}' =>
                array(
                    'value' => '2:File(1,?)/File(2,?)/File(3,?)',
                    'label' => 5,
                    'lifetime' => 1717601262,
                ),
                '#CacheItemFiles:a:0:{}' => 5,
            )
        );
        $this->assertInvokesEquals(
            CacheItemFiles::class . '->read():"1:File(1,?)/File(2,?)/File(3,?)"',
            CacheItemFile::class . '->read():"File(1,?)"',
            CacheItemFile::class . '->read():"File(2,?)"',
            CacheItemFile::class . '->read():"File(3,?)"',
            CacheItemFiles::class . '->read():"2:File(1,?)/File(2,?)/File(3,?)"'
        );
    }
}
