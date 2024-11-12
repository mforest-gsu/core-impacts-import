<?php

declare(strict_types=1);

namespace Gsu\CoreImpactsImport\Cache;

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
        $this->areasRepository->store(array_map(
            ImpactsArea::create(...),
            JSON::decode(File::getContents(__DIR__ . '/../Resources/ImpactsAreas.json'))
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
