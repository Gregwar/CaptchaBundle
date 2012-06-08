<?php

namespace Gregwar\CaptchaBundle\Validator;

use Symfony\Component\Form\FormValidatorInterface;
use Symfony\Component\HttpFoundation\Session;
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

    public function __construct(Session $session, $key)
    {
        $this->session = $session;
        $this->key = $key;
    }

    public function validate(FormInterface $form)
    {
        $code = $form->getData();
        $excepted_code = $this->getExceptedCode();

        if (!($code && $excepted_code && is_string($code) && is_string($excepted_code)
            && $this->niceize($code) == $this->niceize($excepted_code))) {
            $form->addError(new FormError('Bad code value'));
        }

        $this->session->remove($this->key);

        if ($this->session->has($this->key.'_fingerprint')) {
            $this->session->remove($this->key.'_fingerprint');
        }
    }

    /**
     * Retrieve the excepted CAPTCHA code
     */
    private function getExceptedCode()
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
}
