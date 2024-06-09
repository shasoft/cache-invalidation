<?php

namespace Shasoft\CacheInvalidation\Tests\Unit;


use Shasoft\CacheInvalidation\Tests\CacheItemFilePrepare;
use Shasoft\CacheInvalidation\Tests\CacheItemUserPrepare;

class MainEventPrepareTest extends Base
{
    //
    public function testGet(): void
    {
        CacheItemFilePrepare::prepare(1);
        CacheItemFilePrepare::prepare(2);
        $val1 = CacheItemFilePrepare::get(1);
        self::assertEquals($val1, "FilePrepare(1,?)");
        $val2 = CacheItemFilePrepare::get(2);
        self::assertEquals($val2, "FilePrepare(2,?)");
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
            )
        );
        $this->assertInvokesEquals(
            CacheItemFilePrepare::class . '::prepareRead([{},{}])',
            CacheItemFilePrepare::class . '->read():"FilePrepare(1,?)"',
            CacheItemFilePrepare::class . '->read():"FilePrepare(2,?)"'
        );
    }
    //
    public function testGet2(): void
    {
        CacheItemUserPrepare::prepare(1);
        $val = CacheItemUserPrepare::get(1);
        self::assertEquals($val, "UserPrepare(1,?FilePrepare(10,?))");
        $this->assertCacheEquals(
            array(
                'CacheItemFilePrepare:10' =>
                array(
                    'value' => 'FilePrepare(10,?)',
                    'label' => 1,
                    'labels' =>
                    array(),
                ),
                '#CacheItemFilePrepare:10' => 1,
                'CacheItemUserPrepare:1' =>
                array(
                    'value' => 'UserPrepare(1,?FilePrepare(10,?))',
                    'label' => 2,
                    'labels' =>
                    array(
                        '#CacheItemFilePrepare:10' => 1,
                    ),
                ),
            )
        );
        $this->assertInvokesEquals(
            CacheItemUserPrepare::class . '::prepareRead([{}])',
            CacheItemFilePrepare::class . '::prepareRead([{}])',
            CacheItemUserPrepare::class . '->read():"UserPrepare(1,?FilePrepare(10,?))"',
            CacheItemFilePrepare::class . '->read():"FilePrepare(10,?)"'
        );
    }
    //
    public function testGetKey(): void
    {
        $val1 = CacheItemFilePrepare::get(2);
        $val2 = CacheItemFilePrepare::get(2, 3);
        self::assertEquals($val1, $val2);
        $this->assertCacheEquals(
            array(
                'CacheItemFilePrepare:2' =>
                array(
                    'value' => 'FilePrepare(2,?)',
                    'label' => 1,
                    'labels' =>
                    array(),
                ),
            )
        );
        $this->assertInvokesEquals(
            CacheItemFilePrepare::class . '::prepareRead([{}])',
            CacheItemFilePrepare::class . '->read():"FilePrepare(2,?)"'
        );
    }
    //
    public function testSetGet(): void
    {

        CacheItemFilePrepare::set(1, 'A');
        CacheItemFilePrepare::set(2, 'B');
        CacheItemFilePrepare::set(3, 'C');
        //
        CacheItemFilePrepare::prepare(1);
        CacheItemFilePrepare::prepare(2);
        CacheItemFilePrepare::prepare(3);
        $val1 = CacheItemFilePrepare::get(1);
        self::assertEquals($val1, "FilePrepare(1,A)");
        $val2 = CacheItemFilePrepare::get(2);
        self::assertEquals($val2, "FilePrepare(2,B)");
        $val3 = CacheItemFilePrepare::get(3);
        self::assertEquals($val3, "FilePrepare(3,C)");
        CacheItemFilePrepare::set(1, 'Z');
        $val1 = CacheItemFilePrepare::get(1);
        self::assertEquals($val1, "FilePrepare(1,Z)");
        //
        $this->assertCacheEquals(
            array(
                'CacheItemFilePrepare:2' =>
                array(
                    'value' => 'FilePrepare(2,B)',
                    'label' => 2,
                    'labels' =>
                    array(),
                ),
                'CacheItemFilePrepare:3' =>
                array(
                    'value' => 'FilePrepare(3,C)',
                    'label' => 3,
                    'labels' =>
                    array(),
                ),
                'CacheItemFilePrepare:1' =>
                array(
                    'value' => 'FilePrepare(1,Z)',
                    'label' => 4,
                    'labels' =>
                    array(),
                ),
            )
        );
        $this->assertInvokesEquals(
            CacheItemFilePrepare::class . '->write("A")',
            CacheItemFilePrepare::class . '->write("B")',
            CacheItemFilePrepare::class . '->write("C")',
            CacheItemFilePrepare::class . '::prepareRead([{},{},{}])',
            CacheItemFilePrepare::class . '->read():"FilePrepare(1,A)"',
            CacheItemFilePrepare::class . '->read():"FilePrepare(2,B)"',
            CacheItemFilePrepare::class . '->read():"FilePrepare(3,C)"',
            CacheItemFilePrepare::class . '->write("Z")',
            CacheItemFilePrepare::class . '::prepareRead([{}])',
            CacheItemFilePrepare::class . '->read():"FilePrepare(1,Z)"'
        );
    }
    //
    public function testSetGet2(): void
    {

        CacheItemFilePrepare::set(1, 'A');
        $val2 = CacheItemFilePrepare::get(2);
        self::assertEquals($val2, "FilePrepare(2,?)");
        //
        $this->assertCacheEquals(
            array(
                'CacheItemFilePrepare:2' =>
                array(
                    'value' => 'FilePrepare(2,?)',
                    'label' => 1,
                    'labels' =>
                    array(),
                ),
            )
        );
        $this->assertInvokesEquals(
            CacheItemFilePrepare::class . '->write("A")',
            CacheItemFilePrepare::class . '::prepareRead([{}])',
            CacheItemFilePrepare::class . '->read():"FilePrepare(2,?)"'
        );
    }
}
