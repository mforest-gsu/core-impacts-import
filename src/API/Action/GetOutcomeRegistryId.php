<?php

declare(strict_types=1);

namespace Gsu\CoreImpactsImport\API\Action;

use Brightspace\Api\Core\Action\ApiAction;
use Gadget\Io\Cast;
use Psr\Http\Message\ResponseInterface;

/** @extends ApiAction<string> */
final class GetOutcomeRegistryId extends ApiAction
{
    /**
     * @param mixed $param
     * @return static
     */
    protected function initAction(...$param): static
    {
        $orgUnitId = Cast::toInt($param[0] ?? null);
        return $this
            ->setMethod('GET')
            ->setUri(sprintf(
                "d2l://web/d2l/le/lo/%s/outcomes-management",
                $orgUnitId
            ))
            ->setLoginToken(true)
            ;
    }


    /**
     * @param ResponseInterface $response
     * @return string
     */
    protected function parseResponse(ResponseInterface $response): mixed
    {
        $document = new \DOMDocument();
        if (!@$document->loadHTML($response->getBody()->getContents())) {
            throw new \RuntimeException("Unable to parse contents");
        }

        $attributes = $document
            ->getElementsByTagName("d2l-outcomes-management")
            ->item(0)
            ?->attributes
            ?? throw new \RuntimeException("Element 'd2l-outcomes-management' not found");

        for ($i = 0; $i < $attributes->length; $i++) {
            $attribute = $attributes->item($i);
            if ($attribute !== null && $attribute->nodeName === 'registry-id' && is_string($attribute->nodeValue)) {
                return $attribute->nodeValue;
            }
        }

        throw new \RuntimeException("Attribute 'registry-id' not found");
    }
}
