<?php

namespace Shasoft\CacheInvalidation\Tests\Unit;


use Shasoft\CacheInvalidation\Tests\CacheItemFile;
use Shasoft\CacheInvalidation\Tests\CacheItemFiles;
use Shasoft\CacheInvalidation\Tests\CacheItemFilesLong;
use Shasoft\CacheInvalidation\Tests\CacheItemFilePrepare;
use Shasoft\CacheInvalidation\Tests\CacheItemUserPrepare;
use Shasoft\CacheInvalidation\Tests\CacheItemFileSubLifetime;

class MainIntersectionTest extends Base
{
    //
    public function testLifetimeSubEvent(): void
    {

        $val1 = CacheItemFilesLong::get();
        self::assertEquals($val1, "1:File(1,?)/File(2,?)/File(3,?)");
        CacheItemFile::set(1, 'Z');
        $val2 = CacheItemFilesLong::get();
        self::assertEquals($val1, $val2);
        sleep(6);
        $val3 = CacheItemFilesLong::get();
        self::assertEquals($val3, "2:File(1,Z)/File(2,?)/File(3,?)");
        $this->assertCacheEquals(
            array(
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
                'CacheItemFilesLong:a:0:{}' =>
                array(
                    'value' => '2:File(1,Z)/File(2,?)/File(3,?)',
                    'label' => 6,
                    'lifetime' => 1717786663,
                ),
                '#CacheItemFilesLong:a:0:{}' => 6,
                'CacheItemFile:1' =>
                array(
                    'value' => 'File(1,Z)',
                    'label' => 5,
                    'labels' =>
                    array(),
                ),
            )
        );
        $this->assertInvokesEquals(
            CacheItemFilesLong::class . '->read():"1:File(1,?)/File(2,?)/File(3,?)"',
            CacheItemFile::class . '->read():"File(1,?)"',
            CacheItemFile::class . '->read():"File(2,?)"',
            CacheItemFile::class . '->read():"File(3,?)"',
            CacheItemFile::class . '->write("Z")',
            CacheItemFilesLong::class . '->read():"2:File(1,Z)/File(2,?)/File(3,?)"',
            CacheItemFile::class . '->read():"File(1,Z)"',
        );
    }
    public function testEventSubLifetime(): void
    {
        $val1 = CacheItemFileSubLifetime::get(1);
        self::assertEquals($val1, "FileSubLifetime(1,?){1:File(1,?)/File(2,?)/File(3,?)}");
        $val2 = CacheItemFileSubLifetime::get(1);
        self::assertEquals($val1, $val2);
        sleep(3);
        $val3 = CacheItemFileSubLifetime::get(1);
        self::assertEquals($val3, "FileSubLifetime(1,?){2:File(1,?)/File(2,?)/File(3,?)}");

        $this->assertCacheEquals(
            array(
                'CacheItemFile:1' =>
                array(
                    'value' => 'File(1,?)',
                    'label' => 1,
                    'labels' =>
                    array(),
                ),
                '#CacheItemFile:1' => 1,
                'CacheItemFile:2' =>
                array(
                    'value' => 'File(2,?)',
                    'label' => 2,
                    'labels' =>
                    array(),
                ),
                '#CacheItemFile:2' => 2,
                'CacheItemFile:3' =>
                array(
                    'value' => 'File(3,?)',
                    'label' => 3,
                    'labels' =>
                    array(),
                ),
                '#CacheItemFile:3' => 3,
                'CacheItemFiles:a:0:{}' =>
                array(
                    'value' => '2:File(1,?)/File(2,?)/File(3,?)',
                    'label' => 6,
                    'lifetime' => 1717788768,
                ),
                'CacheItemFileSubLifetime:1' =>
                array(
                    'value' => 'FileSubLifetime(1,?){2:File(1,?)/File(2,?)/File(3,?)}',
                    'label' => 7,
                    'labels' =>
                    array(
                        '#CacheItemFile:1' => 1,
                        '#CacheItemFile:2' => 2,
                        '#CacheItemFile:3' => 3,
                        '#CacheItemFiles:a:0:{}' => 6,
                    ),
                ),
                '#CacheItemFiles:a:0:{}' => 6,
            )
        );
        $this->assertInvokesEquals(
            CacheItemFileSubLifetime::class . '->read():"FileSubLifetime(1,?){1:File(1,?)/File(2,?)/File(3,?)}"',
            CacheItemFiles::class . '->read():"1:File(1,?)/File(2,?)/File(3,?)"',
            CacheItemFile::class . '->read():"File(1,?)"',
            CacheItemFile::class . '->read():"File(2,?)"',
            CacheItemFile::class . '->read():"File(3,?)"',
            CacheItemFileSubLifetime::class . '->read():"FileSubLifetime(1,?){2:File(1,?)/File(2,?)/File(3,?)}"',
            CacheItemFiles::class . '->read():"2:File(1,?)/File(2,?)/File(3,?)"',
        );
    }
}
