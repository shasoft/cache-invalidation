<?php

namespace Shasoft\CacheInvalidation\Tests\Unit;

use Shasoft\CacheInvalidation\Tests\CacheItemArticle;
use Shasoft\CacheInvalidation\Tests\CacheItemFile;
use Shasoft\CacheInvalidation\Tests\CacheItemUser;
use Shasoft\Dump\DebugInvoke;

class MainEventTest extends Base
{
    //
    public function testGet(): void
    {
        $val = CacheItemArticle::get(1);
        self::assertEquals($val, "Article(1,?File(10,?)-User(10,?File(100,?)))");
        $this->assertCacheEquals(array(
            'CacheItemArticle:1' => array(
                'value' => 'Article(1,?File(10,?)-User(10,?File(100,?)))',
                'label' => 4,
                'labels' => array(
                    '#CacheItemFile:100' => 2,
                    '#CacheItemFile:10' => 1,
                    '#CacheItemUser:10' => 3
                )
            ),
            'CacheItemFile:10' => array(
                'value' => 'File(10,?)', 'label' => 1, 'labels' => array(),
            ),
            'CacheItemUser:10' => array(
                'value' => 'User(10,?File(100,?))',
                'label' => 3,
                'labels' => array('#CacheItemFile:100' => 2)
            ),
            'CacheItemFile:100' => array(
                'value' => 'File(100,?)',
                'label' => 2,
                'labels' => array(),
            ),
            // Значение меток
            '#CacheItemUser:10' => 3,
            '#CacheItemFile:10' => 1,
            '#CacheItemFile:100' => 2,
        ));
        $this->assertInvokesEquals(
            CacheItemArticle::class . '->read():"Article(1,?File(10,?)-User(10,?File(100,?)))"',
            CacheItemFile::class . '->read():"File(10,?)"',
            CacheItemUser::class . '->read():"User(10,?File(100,?))"',
            CacheItemFile::class . '->read():"File(100,?)"',
        );
    }
    //
    public function testGet2(): void
    {
        $val1 = CacheItemArticle::get(1);
        self::assertEquals($val1, "Article(1,?File(10,?)-User(10,?File(100,?)))");
        $val2 = CacheItemArticle::get(1);
        self::assertEquals($val1, $val2);
        $this->assertCacheEquals(
            array(
                'CacheItemArticle:1' => array(
                    'value' => 'Article(1,?File(10,?)-User(10,?File(100,?)))',
                    'label' => 4,
                    'labels' => array(
                        '#CacheItemFile:100' => 2,
                        '#CacheItemFile:10' => 1,
                        '#CacheItemUser:10' => 3
                    )
                ),
                'CacheItemFile:10' => array(
                    'value' => 'File(10,?)', 'label' => 1, 'labels' => array(),
                ),
                'CacheItemUser:10' => array(
                    'value' => 'User(10,?File(100,?))',
                    'label' => 3,
                    'labels' => array('#CacheItemFile:100' => 2)
                ),
                'CacheItemFile:100' => array(
                    'value' => 'File(100,?)',
                    'label' => 2,
                    'labels' => array(),
                ),
                // Значение меток
                '#CacheItemUser:10' => 3,
                '#CacheItemFile:10' => 1,
                '#CacheItemFile:100' => 2,
            )
        );
        $this->assertInvokesEquals(
            CacheItemArticle::class . '->read():"Article(1,?File(10,?)-User(10,?File(100,?)))"',
            CacheItemFile::class . '->read():"File(10,?)"',
            CacheItemUser::class . '->read():"User(10,?File(100,?))"',
            CacheItemFile::class . '->read():"File(100,?)"',
        );
    }
    //
    public function testGetKey(): void
    {
        $val1 = CacheItemFile::get(2);
        $val2 = CacheItemFile::get(2, 3);
        self::assertEquals($val1, $val2);
        $this->assertCacheEquals(
            array(
                'CacheItemFile:2' =>
                array(
                    'value' => 'File(2,?)',
                    'label' => 1,
                    'labels' =>
                    array(),
                ),
            )
        );
        $this->assertInvokesEquals(
            CacheItemFile::class . '->read():"File(2,?)"'
        );
    }
    //
    public function testGetSet(): void
    {
        $val1 = CacheItemArticle::get(1);
        self::assertEquals($val1, "Article(1,?File(10,?)-User(10,?File(100,?)))");
        $this->assertInvokesEquals(
            CacheItemArticle::class . '->read():"Article(1,?File(10,?)-User(10,?File(100,?)))"',
            CacheItemFile::class . '->read():"File(10,?)"',
            CacheItemUser::class . '->read():"User(10,?File(100,?))"',
            CacheItemFile::class . '->read():"File(100,?)"',
        );
        //
        DebugInvoke::clear();
        CacheItemFile::set(10, 'Z');
        $this->assertInvokesEquals(
            CacheItemFile::class . '->write("Z")',
        );
        //
        DebugInvoke::clear();
        $val2 = CacheItemArticle::get(1);
        self::assertEquals($val2, "Article(1,?File(10,Z)-User(10,?File(100,?)))");
        $this->assertInvokesEquals(
            CacheItemArticle::class . '->read():"Article(1,?File(10,Z)-User(10,?File(100,?)))"',
            CacheItemFile::class . '->read():"File(10,Z)"',
        );
        //
        $this->assertCacheEquals(array(
            'CacheItemFile:100' => array(
                'value' => 'File(100,?)',
                'label' => 2,
                'labels' => array()
            ),
            'CacheItemUser:10' => array(
                'value' => 'User(10,?File(100,?))',
                'label' => 3,
                'labels' => array(
                    '#CacheItemFile:100' => 2
                )
            ),
            'CacheItemArticle:1' => array(
                'value' => 'Article(1,?File(10,Z)-User(10,?File(100,?)))',
                'label' => 6,
                'labels' => array(
                    '#CacheItemFile:100' => 2,
                    '#CacheItemFile:10' => 5,
                    '#CacheItemUser:10' => 3
                )
            ),
            'CacheItemFile:10' => array(
                'value' => 'File(10,Z)',
                'label' => 5,
                'labels' => array()
            ),
            // Значение меток
            '#CacheItemUser:10' => 3,
            '#CacheItemFile:10' => 5,
            '#CacheItemFile:100' => 2
        ));
    }
    //
    public function testGetSet2(): void
    {
        $val1 = CacheItemArticle::get(1);
        self::assertEquals($val1, "Article(1,?File(10,?)-User(10,?File(100,?)))");
        $this->assertInvokesEquals(
            CacheItemArticle::class . '->read():"Article(1,?File(10,?)-User(10,?File(100,?)))"',
            CacheItemFile::class . '->read():"File(10,?)"',
            CacheItemUser::class . '->read():"User(10,?File(100,?))"',
            CacheItemFile::class . '->read():"File(100,?)"',
        );
        //
        DebugInvoke::clear();
        CacheItemFile::set(100, 'V');
        $this->assertInvokesEquals(
            CacheItemFile::class . '->write("V")',
        );
        //
        DebugInvoke::clear();
        $val2 = CacheItemArticle::get(1);
        self::assertEquals($val2, "Article(1,?File(10,?)-User(10,?File(100,V)))");
        $this->assertInvokesEquals(
            CacheItemArticle::class . '->read():"Article(1,?File(10,?)-User(10,?File(100,V)))"',
            CacheItemUser::class . '->read():"User(10,?File(100,V))"',
            CacheItemFile::class . '->read():"File(100,V)"',
        );
        //
        $this->assertCacheEquals(
            array(
                'CacheItemFile:10' =>
                array(
                    'value' => 'File(10,?)',
                    'label' => 1,
                    'labels' =>
                    array(),
                ),
                '#CacheItemFile:10' => 1,
                'CacheItemUser:10' =>
                array(
                    'value' => 'User(10,?File(100,V))',
                    'label' => 6,
                    'labels' =>
                    array(
                        '#CacheItemFile:100' => 5,
                    ),
                ),
                '#CacheItemUser:10' => 6,
                'CacheItemArticle:1' =>
                array(
                    'value' => 'Article(1,?File(10,?)-User(10,?File(100,V)))',
                    'label' => 7,
                    'labels' =>
                    array(
                        '#CacheItemFile:100' => 5,
                        '#CacheItemFile:10' => 1,
                        '#CacheItemUser:10' => 6,
                    ),
                ),
                'CacheItemFile:100' =>
                array(
                    'value' => 'File(100,V)',
                    'label' => 5,
                    'labels' =>
                    array(),
                ),
                '#CacheItemFile:100' => 5,
            )
        );
    }
}
