<?php

declare(strict_types=1);

namespace Gsu\CoreImpactsImport\Cache;

use Brightspace\Api\Auth\Factory\AuthCodeFactory;
use Brightspace\Api\Auth\Factory\LoginTokenFactory;
use Brightspace\Api\Auth\Model\Config;
use Gadget\Http\OAuth\Cache\TokenCache;
use Gadget\Http\OAuth\Factory\TokenFactory;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

final class AuthCacheWarmer implements CacheWarmerInterface
{
    /**
     * @param Config $config
     * @param LoginTokenFactory $loginTokenFactory
     * @param AuthCodeFactory $authCodeFactory
     * @param TokenFactory $tokenFactory
     * @param TokenCache $tokenCache
     */
    public function __construct(
        private Config $config,
        private LoginTokenFactory $loginTokenFactory,
        private AuthCodeFactory $authCodeFactory,
        private TokenFactory $tokenFactory,
        private TokenCache $tokenCache
    ) {
    }


    /** @inheritdoc */
    public function warmUp(
        string $cacheDir,
        ?string $buildDir = null
    ): array {
        $loginToken = $this->loginTokenFactory->create();
        $authCode = $this->authCodeFactory->createFromLoginToken($loginToken);
        $token = $this->tokenFactory->createFromAuthCode($authCode);
        $this->tokenCache->set(
            $this->config->tokenCacheKey,
            $token
        );

        return [];
    }


    /**
     * @return bool
     */
    public function isOptional(): bool
    {
        return true;
    }
}
