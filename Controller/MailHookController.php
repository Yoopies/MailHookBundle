<?php

namespace Swm\Bundle\MailHookBundle\Controller;

use Swm\Bundle\MailHookBundle\Event\MailHookEvent;
use Swm\Bundle\MailHookBundle\Hydrator\HydratorInterface;
use Swm\Bundle\MailHookBundle\Service\MailHookService;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[Route('/webhook')]
class MailHookController
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly MailHookService $mailHookService,
        private readonly HydratorInterface $defaultHydrator,
        private readonly ParameterBagInterface $parameterBag,
    ) {
    }

    #[Route('/{secretSalt}/{service}/catch', name: 'swm_mailhook_catcher_for_service', methods: ['POST', 'GET'])]
    public function catcherAction(string $secretSalt, string $service): Response
    {
        // check if request is granted
        $this->checkSecret($secretSalt);

        // get hooks
        $hooks = $this->mailHookService->getHooksForService($service);

        foreach ($hooks as $hook) {
            $event = $this->defaultHydrator->hydrate($hook, MailHookEvent::class);
            $this->eventDispatcher->dispatch($event, $hook->getEventDispatched());
        }

        return new Response();
    }

    private function checkSecret(string $secretSalt): void
    {
        if ($this->parameterBag->get('swm_mailhook.secretsalt') !== $secretSalt) {
            throw new AccessDeniedHttpException('You are not welcome here');
        }
    }
}
