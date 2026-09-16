<?php

namespace Swm\Bundle\MailHookBundle\Tests\Functional;

use Swm\Bundle\MailHookBundle\SwmMailHookBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

final class TestKernel extends Kernel
{
    use MicroKernelTrait;

    public function registerBundles(): iterable
    {
        return [new FrameworkBundle(), new SwmMailHookBundle()];
    }

    public function getProjectDir(): string
    {
        return __DIR__;
    }

    public function getCacheDir(): string
    {
        return \dirname(__DIR__).'/var/cache/'.$this->environment;
    }

    public function getLogDir(): string
    {
        return \dirname(__DIR__).'/var/log';
    }

    private function configureContainer(ContainerConfigurator $container): void
    {
        $container->extension('framework', [
            'test' => true,
            'secret' => 'test',
            'http_method_override' => false,
            'handle_all_throwables' => true,
            'php_errors' => ['log' => true],
            'router' => ['utf8' => true],
        ]);

        $container->extension('swm_mail_hook', ['secretsalt' => 'mySalt']);

        $container->services()
            ->set(RecordingListener::class)
            ->public()
            ->tag('kernel.event_listener', ['event' => 'swm.mail_hook.event.hard_bounce', 'method' => 'onEvent']);
    }

    private function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->import('@SwmMailHookBundle/Controller/', 'attribute');
    }
}
