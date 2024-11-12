<?php

declare(strict_types=1);

namespace Gsu\CoreImpactsImport\API\Action;

use Brightspace\Api\Core\Action\ApiAction;
use Gadget\Io\JSON;
use Gsu\CoreImpactsImport\Model\OutcomeRegistry;
use Psr\Http\Message\ResponseInterface;

/** @extends ApiAction<OutcomeRegistry> */
final class PutOutcomeRegistry extends ApiAction
{
    /**
     * @param mixed ...$param
     * @return static
     */
    protected function initAction(...$param): static
    {
        $registry = ($param[0] ?? null);
        if (!is_object($registry) || !$registry instanceof OutcomeRegistry) {
            throw new \RuntimeException();
        }
        $body = JSON::encode(['objectives' => $registry->objectives]);
        return $this
            ->setMethod('PUT')
            ->setUri(sprintf(
                'https://lores-us-east-1.brightspace.com/api/lores/1.0/registries/%s',
                $registry->id
            ))
            ->setHeaders([
                'Host' => 'lores-us-east-1.brightspace.com',
                'Content-Type' => 'application/json',
                'Content-Length' => strlen($body)
            ])
            ->setBody($body)
            ->setOAuthToken(true)
            ;
    }


    /**
     * @param ResponseInterface $response
     * @return bool
     */
    protected function parseResponse(ResponseInterface $response): mixed
    {
        return $response->getStatusCode() === 200;
    }
}
