<?php

namespace Swm\Bundle\MailHookBundle\Tests\ApiService;

use PHPUnit\Framework\TestCase;
use Swm\Bundle\MailHookBundle\ApiService\CampainmonitorApiService;
use Swm\Bundle\MailHookBundle\ApiService\MandrillApiService;
use Swm\Bundle\MailHookBundle\ApiService\SendgridApiService;
use Swm\Bundle\MailHookBundle\ApiService\SesApiService;
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

    public function testSesRawMessage(): void
    {
        $request = Request::create('/', 'POST', content: json_encode(self::sesBounce()));

        $hooks = (new SesApiService())->setRequest($request)->bind();

        self::assertCount(1, $hooks);
        self::assertSame('f@example.com', $hooks[0]->getEmail());
        self::assertSame(SwmMailHookEvent::MAILHOOK_SOFTBOUNCE, $hooks[0]->getEventDispatched());
    }

    public function testSesSnsEnvelope(): void
    {
        $request = Request::create('/', 'POST', content: json_encode([
            'Type' => 'Notification',
            'MessageId' => 'd0c99fd0-c36b-5e4e-94f5-fc9af6d5195c',
            'Message' => json_encode(self::sesBounce()),
        ]));

        $hooks = (new SesApiService())->setRequest($request)->bind();

        self::assertCount(1, $hooks);
        self::assertSame('f@example.com', $hooks[0]->getEmail());
        self::assertSame(SwmMailHookEvent::MAILHOOK_SOFTBOUNCE, $hooks[0]->getEventDispatched());
        self::assertSame('Bounce', $hooks[0]->getMetaData()['notificationType']);
    }

    public function testSesUnknownPayloadReturnsNoHook(): void
    {
        $request = Request::create('/', 'POST', content: json_encode(['foo' => 'bar']));

        self::assertSame([], (new SesApiService())->setRequest($request)->bind());
    }

    private static function sesBounce(): array
    {
        return [
            'notificationType' => 'Bounce',
            'bounce' => [
                'bounceType' => 'Transient',
                'bouncedRecipients' => [['emailAddress' => 'f@example.com']],
            ],
        ];
    }
}
