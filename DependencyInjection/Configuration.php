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
                ->scalarNode('width')->defaultValue(130)->end()
                ->scalarNode('height')->defaultValue(50)->end()
                ->scalarNode('font')->defaultValue(__DIR__.'/../Generator/Font/captcha.ttf')->end()
                ->scalarNode('keep_value')->defaultValue(false)->end()
                ->scalarNode('charset')->defaultValue('abcdefhjkmnprstuvwxyz23456789')->end()
                ->scalarNode('as_file')->defaultValue(false)->end()
                ->scalarNode('as_url')->defaultValue(false)->end()
                ->scalarNode('reload')->defaultValue(false)->end()
                ->scalarNode('image_folder')->defaultValue('captcha')->end()
                ->scalarNode('web_path')->defaultValue('%kernel.root_dir%/../web')->end()
                ->scalarNode('gc_freq')->defaultValue(100)->end()
                ->scalarNode('expiration')->defaultValue(60)->end()
                ->scalarNode('quality')->defaultValue(30)->end()
                ->scalarNode('invalid_message')->defaultValue('Bad code value')->end()
                ->scalarNode('bypass_code')->defaultValue(null)->end()
                ->scalarNode('whitelist_key')->defaultValue('captcha_whitelist_key')->end()
                ->scalarNode('humanity')->defaultValue(0)->end()
                ->scalarNode('distortion')->defaultValue(true)->end()
                ->scalarNode('max_front_lines')->defaultValue(null)->end()
                ->scalarNode('max_behind_lines')->defaultValue(null)->end()
                ->scalarNode('interpolation')->defaultValue(true)->end()
                ->arrayNode('text_color')->prototype('scalar')->end()->end()
                ->arrayNode('background_color')->prototype('scalar')->end()->end()
                ->scalarNode('disabled')->defaultValue(false)->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
