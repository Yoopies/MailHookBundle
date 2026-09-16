<?php

namespace Swm\Bundle\MailHookBundle\Tests\Functional;

use Swm\Bundle\MailHookBundle\Controller\MailHookController;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class MailHookControllerTest extends WebTestCase
{
    protected static function getKernelClass(): string
    {
        return TestKernel::class;
    }

    public function testRouteIsRegistered(): void
    {
        $route = self::getContainer()->get('router')->getRouteCollection()->get('swm_mailhook_catcher_for_service');

        self::assertNotNull($route);
        self::assertSame('/webhook/{secretSalt}/{service}/catch', $route->getPath());
        self::assertSame(['POST', 'GET'], $route->getMethods());
        self::assertSame(MailHookController::class.'::catcherAction', $route->getDefault('_controller'));
    }

    public function testWrongSecretIsDenied(): void
    {
        $client = self::createClient();
        $client->request('POST', '/webhook/wrongSalt/mailjet/catch', content: '{}');

        self::assertResponseStatusCodeSame(403);
    }

    public function testHookIsDispatched(): void
    {
        $client = self::createClient();
        $client->request('POST', '/webhook/mySalt/mailjet/catch', content: json_encode([
            'event' => 'blocked',
            'email' => 'john@example.com',
        ]));

        self::assertResponseIsSuccessful();

        $events = self::getContainer()->get(RecordingListener::class)->events;
        self::assertCount(1, $events);
        self::assertSame('john@example.com', $events[0]->getEmail());
        self::assertSame('mailjet', $events[0]->getName());
    }
}
