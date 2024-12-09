<?php

declare(strict_types=1);

namespace Gsu\CoreImpactsImport\Repository;

use Brightspace\Api\BrightspaceApi;
use Brightspace\Api\OrgUnit\Model\OrgUnitType;
use Gadget\Cache\CacheItemPool;
use Gsu\CoreImpactsImport\Model\ImpactsArea;
use Gsu\CoreImpactsImport\Model\ImpactsCourse;

final class ImpactsCourseRepository
{
    /** @var array<int,ImpactsCourse> */
    private array $courses = [];


    public function __construct(
        private CacheItemPool $cache,
        private BrightspaceApi $brightspaceApi
    ) {
        $this->cache = $cache->withNamespace(self::class);
    }


    /** @return array<int,ImpactsCourse> */
    public function fetchQueue(): array
    {
        /** @var array<int,ImpactsCourse> $courses */
        $courses = $this->cache->get('courses') ?? [];
        return $courses;
    }


    /**
     * @param array<int,ImpactsCourse> $courses
     * @return bool
     */
    public function storeQueue(array &$courses): bool
    {
        return $this->cache->set('courses', $courses);
    }


    /**
     * @param ImpactsArea $area
     * @return iterable<ImpactsCourse>
     */
    public function fetch(ImpactsArea $area): iterable
    {
        $courseOfferings = $this->brightspaceApi->orgUnit->listDescendants(
            orgUnitId: $area->id,
            orgUnitType: OrgUnitType::COURSE_OFFERING,
            bookmark: "{$area->id}_{$area->startCourse}"
        );

        foreach ($courseOfferings as $courseOffering) {
            if (!isset($this->courses[$courseOffering->Identifier])) {
                $this->courses[$courseOffering->Identifier] = new ImpactsCourse(
                    $courseOffering->Identifier,
                    $this->brightspaceApi->outcome->get($courseOffering->Identifier) ?? throw new \RuntimeException()
                );
            }
            yield $this->courses[$courseOffering->Identifier];
        }
    }


    /**
     * @param ImpactsCourse $course
     * @return bool
     */
    public function store(ImpactsCourse $course): bool
    {
        return $this->brightspaceApi->outcome->update($course->outcomes);
    }
}
