<?php

declare(strict_types=1);

namespace Gsu\CoreImpactsImport\API\Action;

use Brightspace\Api\Core\Action\ApiAction;
use Brightspace\Api\Core\Model\PagedResultSet;
use Gadget\Http\ApiClient;
use Gadget\Io\Cast;
use Psr\Http\Message\ResponseInterface;

/** @extends ApiAction<PagedResultSet<int>> */
final class GetOrgUnitDescendants extends ApiAction
{
    /**
     * @param mixed $param
     * @return static
     */
    protected function initAction(...$param): static
    {
        $orgUnitId = Cast::toInt($param[0] ?? null);
        $orgUnitType = Cast::toInt($param[1] ?? null);
        $bookmark = $param[2] ?? null;
        $bookmark = match (true) {
            is_int($bookmark) => $bookmark > 0 ? "{$orgUnitId}_{$bookmark}" : null,
            is_string($bookmark) => $bookmark,
            is_null($bookmark) => null,
            default => throw new \RuntimeException()
        };

        return $this
            ->setMethod('GET')
            ->setUri(sprintf(
                "d2l://lp/orgstructure/%s/descendants/paged/?%s",
                $orgUnitId,
                ApiClient::buildQuery([
                    'ouTypeId' => $orgUnitType, //3,
                    'bookmark' => $bookmark
                ])
            ));
    }


    /**
     * @param ResponseInterface $response
     * @return PagedResultSet<int>
     */
    protected function parseResponse(ResponseInterface $response): mixed
    {
        return PagedResultSet::create(
            ApiClient::jsonResponse($response),
            fn (mixed $v): int => Cast::toInt(is_array($v) ? ($v['Identifier'] ?? null) : $v)
        );
    }
}
