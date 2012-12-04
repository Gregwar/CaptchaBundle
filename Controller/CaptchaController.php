<?php

namespace Gregwar\CaptchaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

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
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string $key
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function generateCaptchaAction(Request $request, $key)
    {
        $options = $this->container->getParameter('gregwar_captcha.config');
        if (!$options['as_url'] || !in_array($key, $options['valid_keys'])) {
            return $this->createNotFoundException('Unable to generate a captcha via a URL without the proper configuration.');
        }

        /* @var \Gregwar\CaptchaBundle\Generator\CaptchaGenerator $generator */
        $generator = $this->container->get('gregwar_captcha.generator');

        $response = new Response($generator->generate($key, $options));
        $response->headers->set('Content-type', 'image/jpeg');

        return $response;
    }
}

