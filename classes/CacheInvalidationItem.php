<?php

namespace Shasoft\CacheInvalidation;

use Psr\Cache\CacheItemInterface;

class CacheInvalidationItem
{
    public function __construct(
        public CacheItemInterface $cacheItem,
        public ?object $object
    ) {
    }
};
