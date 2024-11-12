<?php

declare(strict_types=1);

namespace Gsu\CoreImpactsImport\Repository;

use Gadget\Cache\CacheItemPool;
use Gadget\Io\Cast;
use Gsu\CoreImpactsImport\Model\ImpactsArea;
use Psr\Cache\CacheItemInterface;

final class ImpactsAreaRepository
{
    /**
     * @param CacheItemPool $cache
     */
    public function __construct(private CacheItemPool $cache)
    {
        $this->cache = $cache->withNamespace(self::class);
    }


    /**
     * @return CacheItemInterface
     */
    private function getAreas(): CacheItemInterface
    {
        return $this->cache->get('areas');
    }


    /**
     * @return ImpactsArea[]
     */
    public function fetch(): array
    {
        $cacheItem = $this->getAreas();
        return array_filter(
            $cacheItem->isHit() ? Cast::toArray($cacheItem->get()) : [],
            fn(mixed $v): bool => is_object($v) && $v instanceof ImpactsArea
        );
    }


    /**
     * @param ImpactsArea[] $areas
     * @return bool
     */
    public function store(array $areas): bool
    {
        return $this->cache->save($this->getAreas()->set($areas));
    }
}
