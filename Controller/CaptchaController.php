<?php

declare(strict_types=1);

namespace Gregwar\CaptchaBundle\Controller;

use Gregwar\CaptchaBundle\Generator\CaptchaGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Generates a captcha via a URL.
 *
 * @author Jeremy Livingston <jeremy.j.livingston@gmail.com>
 */
class CaptchaController extends AbstractController
{
    /** @var CaptchaGenerator */
    private $captchaGenerator;

    /** @var array */
    private $config;

    public function __construct(CaptchaGenerator $captchaGenerator, array $config)
    {
        $this->captchaGenerator = $captchaGenerator;
        $this->config = $config;
    }

    public function generateCaptchaAction(Request $request, string $key): Response
    {
        $session = $request->getSession();
        $whitelistKey = $this->config['whitelist_key'];
        $isOk = false;

        if ($session->has($whitelistKey)) {
            $keys = $session->get($whitelistKey);
            if (is_array($keys) && in_array($key, $keys)) {
                $isOk = true;
            }
        }

        if (!$isOk) {
            return $this->error($this->config);
        }

        $persistedOptions = $session->get($key, array());
        $options = array_merge($this->config, $persistedOptions);

        $phrase = $this->captchaGenerator->getPhrase($options);
        $this->captchaGenerator->setPhrase($phrase);
        $persistedOptions['phrase'] = $phrase;
        $session->set($key, $persistedOptions);

        $response = new Response($this->captchaGenerator->generate($options));
        $response->headers->set('Content-type', 'image/jpeg');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Cache-Control', 'no-cache');

        return $response;
    }

    private function error(array $options): Response
    {
        $this->captchaGenerator->setPhrase('');

        $response = new Response($this->captchaGenerator->generate($options));
        $response->setStatusCode(428);
        $response->headers->set('Content-type', 'image/jpeg');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Cache-Control', 'no-cache');

        return $response;
    }
}
