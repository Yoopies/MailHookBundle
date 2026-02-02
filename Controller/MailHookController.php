<?php

namespace Swm\Bundle\MailHookBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Swm\Bundle\MailHookBundle\Hydrator\DefaultHydrator;
use Swm\Bundle\MailHookBundle\Hydrator\FosUserHydrator;
use Swm\Bundle\MailHookBundle\Service\MailHookService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * @Route("/webhook")
 */
class MailHookController extends AbstractController
{
    private EventDispatcherInterface $eventDispatcher;
    private MailHookService $mailHookService;
    private DefaultHydrator $defaultHydrator;
    private FosUserHydrator $fosUserHydrator;
    private ParameterBagInterface $parameterBag;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        MailHookService $mailHookService,
        DefaultHydrator $defaultHydrator,
        FosUserHydrator $fosUserHydrator,
        ParameterBagInterface $parameterBag
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->mailHookService = $mailHookService;
        $this->defaultHydrator = $defaultHydrator;
        $this->fosUserHydrator = $fosUserHydrator;
        $this->parameterBag = $parameterBag;
    }

    /**
     * @Route("/{secretSalt}/{service}/catch", name="swm_mailhook_catcher_for_service")
     * @Method({"POST","GET"})
     */
    public function catcherAction($secretSalt, $service = null)
    {
        // check if request is granted
        $this->checkSecret($secretSalt);

        // get hooks
        $hooks = $this->mailHookService->getHooksForService($service);

        foreach ($hooks as $hook) {
            $event = $this->defaultHydrator->hydrate($hook, 'Swm\Bundle\MailHookBundle\Event\MailHookEvent');
            $this->eventDispatcher->dispatch($event, $hook->getEventDispatched());
        }

        return new Response();
    }

    /**
     * @Route("/{secretSalt}/{service}/catchuser", name="swm_mailhook_user_catcher_for_service")
     * @Method({"POST","GET"})
     */
    public function catchUserAction($secretSalt, $service = null)
    {
        // check if request is granted
        $this->checkSecret($secretSalt);

        // get hooks
        $hooks = $this->mailHookService->getHooksForService($service);

        foreach ($hooks as $hook) {
            $event = $this->fosUserHydrator->hydrate($hook, 'Swm\Bundle\MailHookBundle\Event\UserMailHookEvent');
            $this->eventDispatcher->dispatch($event, $hook->getEventDispatched());
        }

        return new Response();
    }

    private function checkSecret($secretSalt)
    {
        if ($this->parameterBag->get('swm_mailhook.secretsalt') !== $secretSalt) {
            throw new AccessDeniedHttpException("You are not welcome here");
        }
    }
}
