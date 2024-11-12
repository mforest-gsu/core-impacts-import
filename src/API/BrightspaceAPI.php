<?php

declare(strict_types=1);

namespace Gsu\CoreImpactsImport\API;

use Brightspace\Api\Core\Model\PagedResultSet;
use Gsu\CoreImpactsImport\API\Action\GetOrgUnitDescendants;
use Gsu\CoreImpactsImport\API\Action\GetOutcomeRegistry;
use Gsu\CoreImpactsImport\API\Action\GetOutcomeRegistryId;
use Gsu\CoreImpactsImport\API\Action\PutOutcomeRegistry;
use Gsu\CoreImpactsImport\Model\OutcomeRegistry;

final class BrightspaceAPI
{
    /**
     * @param GetOrgUnitDescendants $getOrgUnitDescendants
     * @param GetOutcomeRegistryId $getOutcomeRegistryId,
     * @param GetOutcomeRegistry $getOutcomeRegistry
     * @param PutOutcomeRegistry $putOutcomeRegistry
     */
    public function __construct(
        private GetOrgUnitDescendants $getOrgUnitDescendants,
        private GetOutcomeRegistryId $getOutcomeRegistryId,
        private GetOutcomeRegistry $getOutcomeRegistry,
        private PutOutcomeRegistry $putOutcomeRegistry
    ) {
    }


    /**
     * @param int $orgUnitId
     * @param int $orgUnitType
     * @param int|null $firstChild
     * @param mixed $param
     * @return iterable<int>
     */
    public function getCourses(
        int $orgUnitId,
        int $orgUnitType,
        int|null $firstChild = null
    ): iterable {
        return PagedResultSet::forEach(fn(string|null $b) => $this->getOrgUnitDescendants->invoke(
            $orgUnitId,
            $orgUnitType,
            $b ?? $firstChild
        ));
    }


    /**
     * @param int $orgUnitId
     * @return string
     */
    public function getRegistryId(int $orgUnitId): string
    {
        return $this->getOutcomeRegistryId->invoke($orgUnitId);
    }


    /**
     * @param string $registryId
     * @return OutcomeRegistry
     */
    public function getRegistry(string $registryId): OutcomeRegistry
    {
        return $this->getOutcomeRegistry->invoke($registryId);
    }


    /**
     * @param OutcomeRegistry $registry
     * @return bool
     */
    public function putRegistry(OutcomeRegistry $registry): bool
    {
        return $this->putOutcomeRegistry->invoke($registry);
    }
}
