<?php

use Shasoft\Data\Arr;
use Shasoft\Dump\Debug;
use Shasoft\Dump\DebugInvoke;
use Shasoft\PsrCache\CacheItemPool;
use Shasoft\CacheInvalidation\Tests\DebugLog;
use Shasoft\CacheInvalidation\Tests\Unit\Base;
use Shasoft\PsrCache\Adapter\CacheAdapterArray;
use Shasoft\CacheInvalidation\Tests\CacheItemFile;
use Shasoft\CacheInvalidation\Tests\CacheItemUser;
use Shasoft\CacheInvalidation\Tests\CacheItemFiles;
use Shasoft\CacheInvalidation\CacheInvalidationEvent;
use Shasoft\CacheInvalidation\Tests\CacheItemArticle;
use Shasoft\CacheInvalidation\CacheInvalidationManager;
use Shasoft\CacheInvalidation\Tests\CacheItemFilesLong;
use Shasoft\CacheInvalidation\CacheInvalidationLifetime;
use Shasoft\CacheInvalidation\Tests\CacheItemFilePrepare;
use Shasoft\CacheInvalidation\Tests\CacheItemUserPrepare;
use Shasoft\CacheInvalidation\Tests\CacheItemFilesPrepare;
use Shasoft\CacheInvalidation\Tests\CacheInvalidationDebug;
use Shasoft\CacheInvalidation\Tests\CacheItemFileSubLifetime;

require_once __DIR__ . '/../../vendor/autoload.php';


function aaa(int $v)
{
    return CacheInvalidationLifetime::get(function (int $v) {
        return $v * 10;
    }, 1, $v);
}

s_dump_run(function () {
    /*
    s_dd(
        method_exists(CacheItemArticle::class, 'getItem'),
        method_exists(CacheInvalidationEvent::class, 'getItem')
    );
    //*/
    //
    Debug::enable(true);
    //
    $cacheAdapter = new CacheAdapterArray;
    $cacheItemPool = new CacheItemPool($cacheAdapter);
    CacheInvalidationManager::setConfig($cacheItemPool, new CacheInvalidationDebug);
    //

    $val1 = CacheItemFileSubLifetime::get(1);
    $val2 = CacheItemFileSubLifetime::get(1);
    sleep(3);
    $val3 = CacheItemFileSubLifetime::get(1);

    Base::cachePrint($cacheAdapter);
    s_dd(
        $val1,
        $val2,
        $val3,
        Base::cacheValues($cacheAdapter),
        DebugInvoke::allStr()
    );
    /*
    $val1 = CacheItemFiles::lifetime();
    s_dump($val1, Base::cacheValues($cacheAdapter), DebugInvoke::allStr());
    sleep(3);
    $val2 = CacheItemFiles::lifetime();
    s_dd($val1, $val2, Base::cacheValues($cacheAdapter), DebugInvoke::allStr());
    //*/
    //
    if (0) {
        $val1 = aaa(7);
        s_dump($val1, Base::cacheValues($cacheAdapter));
        sleep(3);
        $val2 = aaa(7);
        s_dd($val2, Base::cacheValues($cacheAdapter));
    }

    //$val1 = CacheItemArticle::get(2);
    //s_dd($val1);

    /*
    $val1 = CacheInvalidationManager::get(CacheItemUser::class, 9);
    $val2 = CacheInvalidationManager::get(CacheItemUser::class, 9);
    s_dump($val1, $val2, $cacheAdapter->allStr());
    
    CacheInvalidationManager::set(CacheItemUser::class, 9, 777);
    
    $val1 = CacheInvalidationManager::get(CacheItemUser::class, 9);
    $val2 = CacheInvalidationManager::get(CacheItemUser::class, 9);
    s_dump($val1, $val2, $cacheAdapter->allStr());
    //*/

    $val1 = CacheItemArticle::get(1);
    s_dump($val1, Base::cacheValues($cacheAdapter), DebugInvoke::allStr());
    //$val2 = CacheItemArticle::get(1);
    //s_dump($val1, $val2, Base::cacheValues($cacheAdapter), DebugInvoke::allStr());
    //exit(1);

    CacheItemFile::set(100, 'V');
    $val3 = CacheItemArticle::get(1);
    s_dump($val3,  Base::cacheValues($cacheAdapter), DebugInvoke::allStr());
    //var_export(Base::cacheValues($cacheAdapter));
    Base::cachePrint($cacheAdapter);
    //s_dd(json_encode(Base::cachePrint($cacheAdapter), JSON_PRETTY_PRINT));
    exit(1);
    CacheItemArticle::set(2, 3);
    CacheItemUser::set(2, 333);

    usleep(333);
    $val1 = CacheItemArticle::get(2);
    $val2 = CacheItemArticle::get(2);
    s_dump($val1, $val2, Base::cacheValues($cacheAdapter));

    s_dd(DebugInvoke::allStr());
});
