<?php

declare(strict_types=1);

namespace Gsu\CoreImpactsImport\Command;

use Gadget\Console\Command\Command;
use Gsu\CoreImpactsImport\Model\ImpactsArea;
use Gsu\CoreImpactsImport\Model\ImpactsCourse;
use Gsu\CoreImpactsImport\Repository\ImpactsAreaRepository;
use Gsu\CoreImpactsImport\Repository\ImpactsCourseRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('app:main')]
final class CourseOutcomeImportCommand extends Command
{
    /**
     * @param ImpactsAreaRepository $areaRepository
     * @param ImpactsCourseRepository $courseRepository
     */
    public function __construct(
        private ImpactsAreaRepository $areaRepository,
        private ImpactsCourseRepository $courseRepository
    ) {
        parent::__construct();
    }


    /** @inheritdoc */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $areas = $this->areaRepository->fetch();
        $courses = $this->courseRepository->fetchQueue();

        $this
            ->merge($areas, $courses)
            ->store($areas, $courses)
            ;

        return self::SUCCESS;
    }


    /**
     * @param ImpactsArea[] $areas
     * @param array<int,ImpactsCourse> $courses
     * @return $this
     */
    private function merge(
        array &$areas,
        array &$courses
    ): self {
        foreach ($areas as $area) {
            $endCourse = $area->startCourse;
            $merged = $total = 0;
            $now = time();
            $log = $this->createMergeLogger($area, $endCourse, $merged, $total);

            $log();
            foreach ($this->courseRepository->fetch($area) as $course) {
                if ($course->outcomes->merge($area->outcomes)) {
                    $courses[$course->id] = $course;
                    $merged++;
                }

                $endCourse = max($endCourse, $course->id);
                $total++;

                if (time() - $now >= 60) {
                    $now = time();
                    $log();
                }
            }
            $log();

            $area->startCourse = $endCourse;
        }

        return $this;
    }


    /**
     * @param ImpactsArea[] $areas
     * @param array<int,ImpactsCourse> $courses
     * @return $this
     */
    private function store(
        array &$areas,
        array &$courses
    ): self {
        $course = null;
        $updated = 0;
        $now = time();
        $log = $this->createStoreLogger($updated, $courses);

        try {
            $log();

            while (count($courses) > 0) {
                $course = array_shift($courses);
                if (!$this->courseRepository->store($course)) {
                    throw new \RuntimeException("Unable to store objectives: id=>{$course->id}");
                }

                if (++$updated >= 1000) {
                    break;
                }

                if (time() - $now >= 60) {
                    $now = time();
                    $log();
                }
            }
        } catch (\Throwable $t) {
            if ($course !== null) {
                array_unshift($courses, $course);
            }
            throw $t;
        } finally {
            $this->areaRepository->store($areas);
            $this->courseRepository->storeQueue($courses);

            $log();
        }

        return $this;
    }


    /**
     * @param ImpactsArea $area
     * @param int $endCourse
     * @param int $merged
     * @param int $total
     * @return (callable():void)
     */
    private function createMergeLogger(
        ImpactsArea &$area,
        int &$endCourse,
        int &$merged,
        int &$total
    ): callable {
        return function () use (&$area, &$endCourse, &$merged, &$total): void {
            $this->info(sprintf(
                "Area: '%s';  Courses: [%d, %d];  Merged: %d;  Total: %d",
                $area->name,
                $area->startCourse,
                $endCourse,
                $merged,
                $total
            ));
        };
    }


    /**
     * @param int $updated
     * @param array<int,ImpactsCourse> $courses
     * @return (callable():void)
     */
    private function createStoreLogger(int &$updated, array &$courses): callable
    {
        return function () use (&$updated, &$courses): void {
            $this->info(sprintf("Stored: %d;  Remaining: %d", $updated, count($courses)));
        };
    }
}
