<?php

namespace Gregwar\CaptchaBundle\Generator;

use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;

use Gregwar\Captcha\CaptchaBuilderInterface;
use Gregwar\Captcha\PhraseBuilderInterface;

/**
 * Uses configuration parameters to call the services that generate captcha images
 *
 * @author Gregwar <g.passault@gmail.com>
 * @author Jeremy Livingston <jeremy.j.livingston@gmail.com>
 */
class CaptchaGenerator
{
    /**
     * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
     */
    protected $session;

    /**
     * Name of the whitelist key
     * @var string
     */
    protected $whitelistKey;

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    protected $router;

    /**
     * @var CaptchaBuilder
     */
    protected $builder;

    /**
     * @var PhraseBuilder
     */
    protected $phraseBuilder;

    /**
     * @var ImageFileHandler
     */
    protected $imageFileHandler;

    /**
     * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
     * @param \Symfony\Component\Routing\RouterInterface $router
     * @param CaptchaBuilderInterface $builder
     * @param ImageFileHandlerInterface $imageFileHandler
     * @param string $whitelistKey
     */
    public function __construct(SessionInterface $session, RouterInterface $router, CaptchaBuilderInterface $builder, PhraseBuilderInterface $phraseBuilder, ImageFileHandler $imageFileHandler, $whitelistKey)
    {
        $this->session          = $session;
        $this->router           = $router;
        $this->builder          = $builder;
        $this->phraseBuilder    = $phraseBuilder;
        $this->imageFileHandler = $imageFileHandler;
        $this->whitelistKey     = $whitelistKey;
    }

    /**
     * Get the captcha URL, stream, or filename that will go in the image's src attribute
     *
     * @param $key
     * @param array $options
     *
     * @return array
     */
    public function getCaptchaCode($key, array $options)
    {
        // Randomly execute garbage collection and returns the image filename
        if ($options['as_file']) {
            $this->imageFileHandler->collectGarbage();

            return $this->generate($key, $options);
        }

        // Returns the image generation URL
        if ($options['as_url']) {
            $keys = $this->session->get($this->whitelistKey, array());
            if (!in_array($key, $keys)) {
                $keys[] = $key;
            }
            $this->session->set($this->whitelistKey, $keys);
            return $this->router->generate('gregwar_captcha.generate_captcha', array('key' => $key));
        }

        return 'data:image/jpeg;base64,' . base64_encode($this->generate($key, $options));
    }

    /**
     * @param string $key
     * @param array $options
     *
     * @return string
     */
    public function generate($key, array $options)
    {
        $fingerprint = $this->getFingerprint($key, $options);

        $this->builder->setDistortion($options['distortion']);

        $content = $this->builder->build(
            $options['width'],
            $options['height'],
            $options['font'],
            $fingerprint
        )->getGd();

        if ($options['keep_value']) {
            $this->session->set($key . '_fingerprint', $this->builder->getFingerprint());
        }

        if (!$options['as_file']) {
            ob_start();
            imagejpeg($content, null, $options['quality']);

            return ob_get_clean();
        }

        return $this->imageFileHandler->saveAsFile($content);
    }

    /**
     * @param string $key
     * @param array $options
     *
     * @return string
     */
    protected function getPhrase($key, array $options)
    {
        // Get the phrase that we'll use for this image
        if ($options['keep_value'] && $this->session->has($key)) {
            return $this->session->get($key);
        }

        $phrase = $this->phraseBuilder->build($options['length'], $options['charset']);
        $this->session->set($key, $phrase);
        $this->captchaBuilder->setPhrase($phrase);

        return $phrase;
    }

    /**
     * @param string $key
     * @param array $options
     *
     * @return array|null
     */
    protected function getFingerprint($key, array $options)
    {
        if ($options['keep_value'] && $this->session->has($key . '_fingerprint')) {
            return $this->session->get($key . '_fingerprint');
        }

        return null;
    }
}

