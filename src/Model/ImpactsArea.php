<?php

declare(strict_types=1);

namespace Gsu\CoreImpactsImport\Model;

use Gadget\Io\Cast;

final class ImpactsArea
{
    public static function create(mixed $values): self
    {
        $values = Cast::toArray($values);
        return new self(
            id: Cast::toInt($values['id'] ?? null),
            code: Cast::toString($values['code'] ?? null),
            name: Cast::toString($values['name'] ?? null),
            outcomes: OutcomeRegistry::create($values['outcomes'] ?? null),
            startCourse: Cast::toInt($values['startCourse'] ?? 0)
        );
    }


    /**
     * @param int $id
     * @param string $code
     * @param string $name
     * @param OutcomeRegistry $outcomes
     * @param int $startCourse
     */
    public function __construct(
        public int $id,
        public string $code,
        public string $name,
        public OutcomeRegistry $outcomes,
        public int $startCourse = 0
    ) {
    }
}
