<?php

namespace Gregwar\CaptchaBundle\Generator;

use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Generates random phrase
 *
 * @author Gregwar <g.passault@gmail.com>
 */
class PhraseBuilder
{
    public function build($length = 5, $charset = 'abcdefghijklmnopqrstuvwxyz0123456789')
    {
        $phrase = '';
        $chars = str_split($charset);

        for ($i = 0; $i < $length; $i++) {
            $phrase .= $chars[array_rand($chars)];
        }

        return $phrase;
    }
}
