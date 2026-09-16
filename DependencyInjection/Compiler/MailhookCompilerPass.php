<?php

namespace Swm\Bundle\MailHookBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Reference;

class MailhookCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $provider = $container->getDefinition('swm.mail_hook.provider.api_service');

        foreach ($container->findTaggedServiceIds('swm.mailhook') as $id => $tags) {
            $tag = array_pop($tags);

            if (!isset($tag['alias'])) {
                throw new LogicException('You should define an alias for all "swm.mailhook" tagged services');
            }

            $provider->addMethodCall('setApiService', [$tag['alias'], new Reference($id)]);
        }
    }
}
