<?php

declare(strict_types=1);

namespace Gsu\CoreImpactsImport\Repository;

use Gadget\Cache\CacheItemPool;
use Gadget\Io\Cast;
use Gsu\CoreImpactsImport\Model\ImpactsArea;

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
     * @return ImpactsArea[]
     */
    public function fetch(): array
    {
        return array_filter(
            Cast::toArray($this->cache->get('areas') ?? []),
            fn(mixed $v): bool => is_object($v) && $v instanceof ImpactsArea
        );
    }


    /**
     * @param ImpactsArea[] $areas
     * @return bool
     */
    public function store(array $areas): bool
    {
        return $this->cache->set('areas', $areas);
    }
}
