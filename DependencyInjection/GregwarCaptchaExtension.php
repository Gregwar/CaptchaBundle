<?php

namespace Gregwar\CaptchaBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;

class GregwarCaptchaExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('gregwar_captcha.length', $config['length']);
        $container->setParameter('gregwar_captcha.height', $config['height']);
        $container->setParameter('gregwar_captcha.width', $config['width']);
        $container->setParameter('gregwar_captcha.as_file', $config['as_file']);
        $container->setParameter('gregwar_captcha.image_folder', $config['image_folder']);
        $container->setParameter('gregwar_captcha.web_path', $config['web_path']);

        $resources = $container->getParameter('twig.form.resources');
        $container->setParameter('twig.form.resources',array_merge(array('GregwarCaptchaBundle::captcha.html.twig'), $resources));

    }

}

