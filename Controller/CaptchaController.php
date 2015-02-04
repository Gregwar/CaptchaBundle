<?php

namespace Gregwar\CaptchaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Generates a captcha via a URL
 *
 * @author Jeremy Livingston <jeremy.j.livingston@gmail.com>
 */
class CaptchaController extends Controller
{
    /**
     * Action that is used to generate the captcha, save its code, and stream the image
     *
     * @param string $key
     *
     * @return Response
     *
     * @throws NotFoundHttpException
     */
    public function generateCaptchaAction($key)
    {
        $options = $this->container->getParameter('gregwar_captcha.config');
        $session = $this->get('session');
        $whitelistKey = $options['whitelist_key'];
        $isOk = false;

        if ($session->has($whitelistKey)) {
            $keys = $session->get($whitelistKey);
            if (is_array($keys) && in_array($key, $keys)) {
                $isOk = true;
            }
        }


        if (!$isOk) {
            return $this->error($options);
        }

        /* @var \Gregwar\CaptchaBundle\Generator\CaptchaGenerator $generator */
        $generator = $this->container->get('gregwar_captcha.generator');

        $persistedOptions = $session->get($key, array());
        $options = array_merge($options, $persistedOptions);

        $phrase = $generator->getPhrase($options);
        $generator->setPhrase($phrase);
        $persistedOptions['phrase'] = $phrase;
        $session->set($key, $persistedOptions);

        $response = new Response($generator->generate($options));
        $response->headers->set('Content-type', 'image/jpeg');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Cache-Control', 'no-cache');

        return $response;
    }

    /**
     * Returns an empty image with status code 428 Precondition Required
     *
     * @param array $options
     *
     * @return Response
     */
    protected function error($options)
    {
        /* @var \Gregwar\CaptchaBundle\Generator\CaptchaGenerator $generator */
        $generator = $this->container->get('gregwar_captcha.generator');
        $generator->setPhrase('');

        $response = new Response($generator->generate($options));
        $response->setStatusCode(428);
        $response->headers->set('Content-type', 'image/jpeg');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Cache-Control', 'no-cache');

        return $response;
    }
}
