<?php

declare(strict_types=1);

namespace Gsu\CoreImpactsImport\Command;

use Gadget\Cache\CacheItemPool;
use Gadget\Console\Command\Command;
use Gsu\CoreImpactsImport\Model\ImpactsArea;
use Gsu\CoreImpactsImport\Model\ImpactsCourse;
use Gsu\CoreImpactsImport\Repository\ImpactsAreaRepository;
use Gsu\CoreImpactsImport\Repository\ImpactsCourseRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('app:main')]
final class CourseOutcomeImport extends Command
{
    /**
     * @param ImpactsAreaRepository $areaRepository
     * @param ImpactsCourseRepository $courseRepository
     * @param CacheItemPool $cache
     */
    public function __construct(
        private ImpactsAreaRepository $areaRepository,
        private ImpactsCourseRepository $courseRepository,
        private CacheItemPool $cache
    ) {
        parent::__construct();
        $this->cache = $cache->withNamespace(self::class);
    }


    /** @inheritdoc */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        /** @var ImpactsArea[] $areas */
        $areas = $this->areaRepository->fetch();

        /** @var array<int,ImpactsCourse> $courses */
        $courses = $this->cache->get('courses')->get() ?? [];

        $this
            ->merge($areas, $courses)
            ->save($areas, $courses)
            ;

        return self::SUCCESS;
    }


    /**
     * @param ImpactsArea[] $areas
     * @param array<int,ImpactsCourse> $courses
     * @return self
     */
    private function merge(
        array &$areas,
        array &$courses
    ): self {
        foreach ($areas as $area) {
            $endCourse = $area->startCourse;

            $merged = $total = 0;
            foreach ($this->courseRepository->fetch($area) as $course) {
                if ($course->outcomes->merge($area->outcomes)) {
                    if (count($courses) >= 1000 && !isset($course[$course->id])) {
                        break;
                    }

                    $courses[$course->id] = $course;
                    $merged++;
                }

                $endCourse = (int) max($endCourse, $course->id);
                $total++;
            }
            $this->info(sprintf(
                "Area: '%s';  Courses: [%d, %d];  Merged: %d;  Total: %d",
                $area->name,
                $area->startCourse,
                $endCourse,
                $merged,
                $total
            ));

            $area->startCourse = $endCourse;
        }

        return $this;
    }


    /**
     * @param InpactsArea[] $areas
     * @param array<int,ImpactsCourse> $courses
     * @return void
     */
    private function save(
        array &$areas,
        array &$courses
    ): self {
        $course = null;
        $updated = 0;

        try {
            while (count($courses) > 0) {
                $course = array_shift($courses);
                $this->courseRepository->store($course);
                $updated++;
            }

            $this->areaRepository->store($areas);
            $this->cache->delete($this->cache->get('courses'));
        } catch (\Throwable $t) {
            if ($course !== null) {
                array_unshift($courses, $course);
                $this->cache->save($this->cache->get('courses')->set($courses));

                foreach ($areas as $area) {
                    $area->startCourse = min($area->startCourse, $course->id);
                }
                $this->areaRepository->store($areas);
            }

            throw new \RuntimeException("Error updating course: {$course?->id}", 0, $t);
        } finally {
            $this->info(sprintf(
                "Saved: %d;  Remaining: %d",
                $updated,
                count($courses)
            ));
        }

        return $this;
    }
}
