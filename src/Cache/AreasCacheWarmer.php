<?php

declare(strict_types=1);

namespace Gsu\CoreImpactsImport\Cache;

use Gadget\Io\Cast;
use Gadget\Io\File;
use Gadget\Io\JSON;
use Gsu\CoreImpactsImport\Model\ImpactsArea;
use Gsu\CoreImpactsImport\Repository\ImpactsAreaRepository;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

final class AreasCacheWarmer implements CacheWarmerInterface
{
    /**
     * @param ImpactsAreaRepository $areasRepository
     */
    public function __construct(private ImpactsAreaRepository $areasRepository)
    {
    }


    /** @inheritdoc */
    public function warmUp(
        string $cacheDir,
        ?string $buildDir = null
    ): array {
        $warmupFile = new File(__DIR__ . '/../Resources/ImpactsAreas.json');
        $this->areasRepository->store(array_map(
            ImpactsArea::create(...),
            Cast::toArray(JSON::decode($warmupFile->getContents()))
        ));

        return [];
    }


    /**
     * @return bool
     */
    public function isOptional(): bool
    {
        return true;
    }
}
