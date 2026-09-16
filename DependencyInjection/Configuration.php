<?php

namespace Swm\Bundle\MailHookBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('swm_mail_hook');

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('secretsalt')->defaultValue('notSecret')->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
