<?php

namespace Gregwar\CaptchaBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;

/**
 * Extension used to load the configuration, set parameters, and initialize the captcha view
 *
 * @author Gregwar <g.passault@gmail.com>
 */
class GregwarCaptchaExtension extends Extension
{
    /**
     * @param array $configs
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('gregwar_captcha.config', $config);
        $container->setParameter('gregwar_captcha.config.image_folder', $config['image_folder']);
        $container->setParameter('gregwar_captcha.config.web_path', $config['web_path']);
        $container->setParameter('gregwar_captcha.config.gc_freq', $config['gc_freq']);
        $container->setParameter('gregwar_captcha.config.expiration', $config['expiration']);
        $container->setParameter('gregwar_captcha.config.whitelist_key', $config['whitelist_key']);

        if ($config['reload'] && !$config['as_url']) {
            throw new \InvalidArgumentException('GregwarCaptcha: The reload option cannot be set without as_url, see the README for more information');
        }

        $resources = $container->getParameter('twig.form.resources');
        $container->setParameter('twig.form.resources', array_merge(array('GregwarCaptchaBundle::captcha.html.twig'), $resources));
    }
}
