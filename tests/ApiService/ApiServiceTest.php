<?php

namespace Swm\Bundle\MailHookBundle\Tests\ApiService;

use PHPUnit\Framework\TestCase;
use Swm\Bundle\MailHookBundle\ApiService\CampainmonitorApiService;
use Swm\Bundle\MailHookBundle\ApiService\MandrillApiService;
use Swm\Bundle\MailHookBundle\ApiService\SendgridApiService;
use Swm\Bundle\MailHookBundle\ApiService\SparkpostApiService;
use Swm\Bundle\MailHookBundle\Hook\HookInterface;
use Swm\Bundle\MailHookBundle\SwmMailHookEvent;
use Symfony\Component\HttpFoundation\Request;

final class ApiServiceTest extends TestCase
{
    public function testMandrill(): void
    {
        $request = Request::create('/', 'POST', [
            'mandrill_events' => json_encode([['event' => 'hard_bounce', 'msg' => ['email' => 'a@example.com']]]),
        ]);

        $hooks = (new MandrillApiService())->setRequest($request)->bind();

        self::assertCount(1, $hooks);
        self::assertSame('a@example.com', $hooks[0]->getEmail());
        self::assertSame(SwmMailHookEvent::MAILHOOK_HARDBOUNCE, $hooks[0]->getEventDispatched());
    }

    public function testMandrillWithoutDataThrows(): void
    {
        $this->expectException(\Exception::class);

        (new MandrillApiService())->setRequest(Request::create('/', 'POST'))->bind();
    }

    public function testSendgridDispatchesOtherEvent(): void
    {
        $request = Request::create('/', 'POST', content: json_encode([['event' => 'processed', 'email' => 'b@example.com']]));

        $hooks = (new SendgridApiService())->setRequest($request)->bind();

        self::assertCount(1, $hooks);
        self::assertSame(SwmMailHookEvent::MAILHOOK_OTHER, $hooks[0]->getEventDispatched());
    }

    public function testSparkpostSkipsUnknownEvents(): void
    {
        $request = Request::create('/', 'POST', content: json_encode([
            ['msys' => ['message_event' => ['type' => 'open', 'rcpt_to' => 'c@example.com']]],
            ['msys' => ['message_event' => ['type' => 'unknown', 'rcpt_to' => 'd@example.com']]],
        ]));

        $hooks = (new SparkpostApiService())->setRequest($request)->bind();

        self::assertCount(1, $hooks);
        self::assertContainsOnlyInstancesOf(HookInterface::class, $hooks);
        self::assertSame('c@example.com', $hooks[0]->getEmail());
    }

    public function testCampainmonitor(): void
    {
        $request = Request::create('/', 'POST', content: json_encode([
            'Events' => json_encode([['Type' => 'Deactivate', 'EmailAddress' => 'e@example.com']]),
        ]));

        $hooks = (new CampainmonitorApiService())->setRequest($request)->bind();

        self::assertCount(1, $hooks);
        self::assertSame(SwmMailHookEvent::MAILHOOK_UNSUB, $hooks[0]->getEventDispatched());
    }
}
