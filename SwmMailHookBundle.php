<?php

namespace Swm\Bundle\MailHookBundle;

use Swm\Bundle\MailHookBundle\DependencyInjection\Compiler\MailhookCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class SwmMailHookBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new MailhookCompilerPass());
    }
}
