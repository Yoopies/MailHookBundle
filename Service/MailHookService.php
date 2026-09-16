<?php

namespace Swm\Bundle\MailHookBundle\Service;

use Swm\Bundle\MailHookBundle\Hook\HookInterface;
use Swm\Bundle\MailHookBundle\Provider\ProviderInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class MailHookService
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly ProviderInterface $apiServiceProvider,
    ) {
    }

    /**
     * @return array<HookInterface>
     */
    public function getHooksForService(string $serviceName): array
    {
        $apiService = $this->apiServiceProvider->get($serviceName)->setRequest($this->requestStack->getCurrentRequest());

        return $apiService->bind();
    }
}
