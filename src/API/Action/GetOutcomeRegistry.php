<?php

declare(strict_types=1);

namespace Gsu\CoreImpactsImport\API\Action;

use Brightspace\Api\Core\Action\ApiAction;
use Gadget\Http\ApiClient;
use Gadget\Io\Cast;
use Gsu\CoreImpactsImport\Model\OutcomeRegistry;
use Psr\Http\Message\ResponseInterface;

/** @extends ApiAction<OutcomeRegistry> */
final class GetOutcomeRegistry extends ApiAction
{
    /**
     * @param mixed $param
     * @return static
     */
    protected function initAction(...$param): static
    {
        $registryId = Cast::toString($param[0] ?? null);
        return $this
            ->setMethod('GET')
            ->setUri(sprintf(
                'https://lores-us-east-1.brightspace.com/api/lores/1.0/registries/%s',
                $registryId
            ))
            ->setOAuthToken(true)
            ;
    }


    /**
     * @param ResponseInterface $response
     * @return OutcomeRegistry
     */
    protected function parseResponse(ResponseInterface $response): mixed
    {
        return OutcomeRegistry::create(ApiClient::jsonResponse($response));
    }
}
