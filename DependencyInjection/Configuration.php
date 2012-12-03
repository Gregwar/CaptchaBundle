<?php

namespace Gregwar\CaptchaBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree.
     *
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('gregwar_captcha');

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('length')->defaultValue(5)->end()
                ->scalarNode('width')->defaultValue(120)->end()
                ->scalarNode('height')->defaultValue(40)->end()
                ->scalarNode('font')->defaultValue(__DIR__.'/../Generator/Font/captcha.ttf')->end()
                ->scalarNode('keep_value')->defaultValue(true)->end()
                ->scalarNode('charset')->defaultValue('abcdefhjkmnprstuvwxyz23456789')->end()
                ->scalarNode('as_file')->defaultValue(false)->end()
                ->scalarNode('as_url')->defaultValue(false)->end()
                ->scalarNode('image_folder')->defaultValue('captcha')->end()
                ->scalarNode('web_path')->defaultValue('%kernel.root_dir%/../web')->end()
                ->scalarNode('gc_freq')->defaultValue(100)->end()
                ->scalarNode('expiration')->defaultValue(60)->end()
                ->scalarNode('quality')->defaultValue(15)->end()
                ->scalarNode('invalid_message')->defaultValue('Bad code value')->end()
                ->scalarNode('bypass_code')->defaultValue(null)->end()
                ->arrayNode('valid_keys')->defaultValue(array('captcha'))->prototype('scalar')->end()
            ->end()
        ;

        return $treeBuilder;
    }
}

