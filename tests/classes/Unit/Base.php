<?php

namespace Shasoft\CacheInvalidation\Tests\Unit;

use Shasoft\Data\Arr;
use Shasoft\Dump\DebugInvoke;
use PHPUnit\Framework\TestCase;
use Shasoft\PsrCache\CacheItemPool;
use Shasoft\PsrCache\Adapter\CacheAdapterArray;
use Shasoft\CacheInvalidation\Tests\CacheItemFile;
use Shasoft\CacheInvalidation\Tests\CacheItemUser;
use Shasoft\CacheInvalidation\Tests\CacheItemArticle;
use Shasoft\CacheInvalidation\CacheInvalidationManager;
use Shasoft\CacheInvalidation\Tests\CacheItemFilePrepare;
use Shasoft\CacheInvalidation\Tests\CacheItemUserPrepare;
use Shasoft\CacheInvalidation\Tests\CacheInvalidationDebug;

class Base extends TestCase
{
    // Настройка среды окружения теста
    static public function settingUpTheEnvironment(\RunManager $runManager, string $bootstrap): void
    {
        $runManager->setWebServer(
            __DIR__ . '/../../test-site',
            ['shasoft-test.ru']
        );
    }
    //
    protected ?CacheAdapterArray $cacheAdapter = null;
    protected ?CacheItemPool $cacheItemPool = null;
    public function setUp(): void
    {
        parent::setUp();
        DebugInvoke::clear();
        $this->cacheAdapter = new CacheAdapterArray;
        $this->cacheItemPool = new CacheItemPool($this->cacheAdapter);
        CacheInvalidationManager::setConfig($this->cacheItemPool, new CacheInvalidationDebug);
        CacheItemArticle::clear();
        CacheItemUser::clear();
        CacheItemFile::clear();
        CacheItemUserPrepare::clear();
        CacheItemFilePrepare::clear();
    }
    public function tearDown(): void
    {
        $this->cacheAdapter = null;
        $this->cacheItemPool = null;
        parent::tearDown();
    }
    static public function cacheValues(CacheAdapterArray $cacheAdapter): array
    {
        $ret = [];
        $all = $cacheAdapter->all();
        foreach ($all as $key => $value) {
            $ret[$key] = $value[0];
        }
        return $ret;
    }
    static public function cachePrint(CacheAdapterArray $cacheAdapter): void
    {
        $values = self::cacheValues($cacheAdapter);
        $str = var_export($values, true);
        /*
        $str = json_encode($values, JSON_PRETTY_PRINT);
        $str = str_replace('{', '[', $str);
        $str = str_replace('}', ']', $str);
        $str = str_replace(':', '=>', $str);
        //*/
        echo '<pre style="padding:16px;background-color:LightYellow">';
        echo $str;
        echo '</pre>';
    }
    private function replaceLifetime(array &$arr): void
    {
        // Заменить значение времени
        foreach ($arr as $key => $item) {
            if (is_array($item)) {
                if (array_key_exists('lifetime', $item)) {
                    $item['lifetime'] = 123456;
                    $arr[$key] = $item;
                }
            }
        }
    }
    public function assertCacheEquals(array $values): void
    {
        $cacheValues = Base::cacheValues($this->cacheAdapter);
        self::replaceLifetime($values);
        self::replaceLifetime($cacheValues);
        //
        $rc = Arr::equals($cacheValues, $values);
        $message = 'КЭШ имеет неверное значение';
        if (!$rc) {
            $message .= PHP_EOL . var_export($cacheValues, true);
            $message .= PHP_EOL . var_export($values, true);
        }
        self::assertTrue($rc, $message);
    }
    public function assertInvokesEquals(...$traces): void
    {
        $rc = DebugInvoke::compare(...$traces);
        $message = 'Ошибка списка вызовов DebugInvoke';
        if ($rc !== true) {
            $message .= PHP_EOL . $rc[0];
            $message .= PHP_EOL . $rc[1];
            $rc = false;
        }
        self::assertTrue($rc, $message);
    }
}
