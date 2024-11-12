<?php

declare(strict_types=1);

namespace Gsu\CoreImpactsImport\Repository;

use Gsu\CoreImpactsImport\API\BrightspaceAPI;
use Gsu\CoreImpactsImport\Model\ImpactsArea;
use Gsu\CoreImpactsImport\Model\ImpactsCourse;

final class ImpactsCourseRepository
{
    /** @var array<int,ImpactsCourse> */
    private array $courses = [];


    /**
     * @param BrightspaceAPI $brightspaceAPI
     */
    public function __construct(private BrightspaceAPI $brightspaceAPI)
    {
    }


    /**
     * @param ImpactsArea $area
     * @return iterable<ImpactsCourse>
     */
    public function fetch(ImpactsArea $area): iterable
    {
        $courses = $this->brightspaceAPI->getCourses(
            orgUnitId: $area->id,
            orgUnitType: 3,
            firstChild: $area->startCourse
        );

        foreach ($courses as $courseId) {
            if (!isset($this->courses[$courseId])) {
                $this->courses[$courseId] = new ImpactsCourse(
                    $courseId,
                    $this->brightspaceAPI->getRegistry(
                        $this->brightspaceAPI->getRegistryId($courseId)
                    )
                );
            }
            yield $this->courses[$courseId];
        }
    }


    /**
     * @param ImpactsCourse $course
     * @return bool
     */
    public function store(ImpactsCourse $course): bool
    {
        return $this->brightspaceAPI->putRegistry($course->outcomes);
    }
}
