<?php

namespace Gregwar\CaptchaBundle\Validator;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;

/**
 * Captcha validator
 *
 * @author Gregwar <g.passault@gmail.com>
 */
class CaptchaValidator
{
    /**
     * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
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

    /**
     * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
     * @param string $key
     * @param string $invalidMessage
     * @param string|null $bypassCode
     */
    public function __construct(SessionInterface $session, $key, $invalidMessage, $bypassCode)
    {
        $this->session          = $session;
        $this->key              = $key;
        $this->invalidMessage   = $invalidMessage;
        $this->bypassCode       = $bypassCode;
    }

    /**
     * @param FormEvent $event
     */
    public function validate(FormEvent $event)
    {
        $form = $form = $event->getForm();

        $code = $form->getData();
        $expectedCode = $this->getExpectedCode();

        if (!($code && is_string($code)
            && ($this->compare($code, $expectedCode) || $this->compare($code, $this->bypassCode)))) {
            $form->addError(new FormError($this->invalidMessage));
        }

        $this->session->remove($this->key);

        if ($this->session->has($this->key . '_fingerprint')) {
            $this->session->remove($this->key . '_fingerprint');
        }
    }

    /**
     * Retrieve the expected CAPTCHA code
     *
     * @return mixed|null
     */
    protected function getExpectedCode()
    {
        if ($this->session->has($this->key)) {
            return $this->session->get($this->key);
        }

        return null;
    }

    /**
     * Process the codes
     *
     * @param $code
     *
     * @return string
     */
    protected function niceize($code)
    {
        return strtr(strtolower($code), 'oil', '01l');
    }

    /**
     * Run a match comparison on the provided code and the expected code
     *
     * @param $code
     * @param $expectedCode
     *
     * @return bool
     */
    protected function compare($code, $expectedCode)
    {
        return ($expectedCode && is_string($expectedCode) && $this->niceize($code) == $this->niceize($expectedCode));
    }
}
