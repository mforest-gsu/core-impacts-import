<?php

declare(strict_types=1);

namespace Gsu\CoreImpactsImport\Model;

final class ImpactsCourse
{
    /**
     * @param int $id
     * @param OutcomeRegistry $outcomes
     * @param int[] $areas
     */
    public function __construct(
        public int $id,
        public OutcomeRegistry $outcomes,
        public array $areas = []
    ) {
    }
}
