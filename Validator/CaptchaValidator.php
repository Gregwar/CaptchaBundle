<?php

namespace Gregwar\CaptchaBundle\Validator;

use Symfony\Component\Form\FormValidatorInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormError;

/**
 * Captcha validator
 *
 * @author Gregwar <g.passault@gmail.com>
 */
class CaptchaValidator implements FormValidatorInterface
{
    /**
     * Session
     */
    private $session;

    /**
     * Session key to store the code
     */
    private $key;

    /**
     * Error message text for non-matching submissions
     */
    private $invalidMessage;

    /**
     * Configuration parameter used to bypass a required code match
     */
    private $bypassCode;

    public function __construct(Session $session, $key, $invalidMessage, $bypassCode)
    {
        $this->session = $session;
        $this->key = $key;
        $this->invalidMessage = $invalidMessage;
        $this->bypassCode = $bypassCode;
    }

    public function validate(FormInterface $form)
    {
        $code = $form->getData();
        $expectedCode = $this->getExpectedCode();

        if (!($code && is_string($code)
            && ($this->compare($code, $expectedCode) || $this->compare($code, $this->bypassCode)))) {
            $form->addError(new FormError($this->invalidMessage));
        }

        $this->session->remove($this->key);

        if ($this->session->has($this->key.'_fingerprint')) {
            $this->session->remove($this->key.'_fingerprint');
        }
    }

    /**
     * Retrieve the expected CAPTCHA code
     */
    private function getExpectedCode()
    {
        if ($this->session->has($this->key)) {
            return $this->session->get($this->key);
        }
        return null;
    }

    /**
     * Process the codes
     */
    private function niceize($code)
    {
        return strtr(strtolower($code), 'oil', '01l');
    }

    /**
     * Run a match comparison on the provided code and the expected code
     */
    private function compare($code, $expectedCode)
    {
        return ($expectedCode && is_string($expectedCode) && $this->niceize($code) == $this->niceize($expectedCode));
    }
}
