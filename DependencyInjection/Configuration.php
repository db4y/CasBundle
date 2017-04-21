<?php

namespace db4y\CasBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('db4y_cas');

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('cas')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('host')
                            ->defaultValue('cas.unistra.fr')
                            ->end()
                        ->integerNode('port')
                            ->defaultValue(443)
                            ->end()
                        ->scalarNode('context')
                            ->defaultValue('/cas')
                            ->end()
                    ->end()
                ->end() // cas
                ->scalarNode('restricted')->defaultValue('db4y_cas.restricted')->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
