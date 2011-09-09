<?php

namespace Gregwar\CaptchaBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree.
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('gregwar_captcha', 'array');
        
        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('length')->defaultValue(5)->end()
                ->scalarNode('width')->defaultValue(120)->end()
                ->scalarNode('height')->defaultValue(40)->end()
            ->end()
        ;
        return $treeBuilder;
    }
}

