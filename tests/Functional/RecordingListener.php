<?php

namespace Swm\Bundle\MailHookBundle\Tests\Functional;

use Swm\Bundle\MailHookBundle\Event\MailHookEvent;

final class RecordingListener
{
    /** @var list<MailHookEvent> */
    public array $events = [];

    public function onEvent(MailHookEvent $event): void
    {
        $this->events[] = $event;
    }
}
