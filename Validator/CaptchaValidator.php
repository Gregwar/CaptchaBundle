<?php

namespace Gregwar\CaptchaBundle\Validator;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Captcha validator
 *
 * @author Gregwar <g.passault@gmail.com>
 */
class CaptchaValidator
{
    /**
     * @var SessionInterface
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
     * Number of form that the user can submit without captcha
     * @var int
     */
    private $humanity;

    /**
     * Translator
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @param TranslatorInterface $translator
     * @param SessionInterface    $session
     * @param string              $key
     * @param string              $invalidMessage
     * @param string              $bypassCode
     * @param int                 $humanity
     */
    public function __construct(TranslatorInterface $translator, SessionInterface $session, $key, $invalidMessage, $bypassCode, $humanity)
    {
        $this->translator       = $translator;
        $this->session          = $session;
        $this->key              = $key;
        $this->invalidMessage   = $invalidMessage;
        $this->bypassCode       = (string)$bypassCode;
        $this->humanity         = $humanity;
    }

    /**
     * @param FormEvent $event
     */
    public function validate(FormEvent $event)
    {
        $form = $event->getForm();

        $code = $form->getData();
        $expectedCode = $this->getExpectedCode();

        if ($this->humanity > 0) {
            $humanity = $this->getHumanity();
            if ($humanity > 0) {
                $this->updateHumanity($humanity-1);
                return;
            }
        }

        if (!($code !== null && is_string($code) && ($this->compare($code, $expectedCode) || $this->compare($code, $this->bypassCode)))) {
            $form->addError(new FormError($this->translator->trans($this->invalidMessage, array(), 'validators')));
        } else {
            if ($this->humanity > 0) {
                $this->updateHumanity($this->humanity);
            }
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
        $options = $this->session->get($this->key, array());

        if (is_array($options) && isset($options['phrase'])) {
            return $options['phrase'];
        }

        return null;
    }

    /**
     * Retrieve the humanity
     *
     * @return mixed|null
     */
    protected function getHumanity()
    {
        return $this->session->get($this->key . '_humanity', 0);
    }

    /**
     * Updates the humanity
     */
    protected function updateHumanity($newValue)
    {
        if ($newValue > 0) {
            $this->session->set($this->key . '_humanity', $newValue);
        } else {
            $this->session->remove($this->key . '_humanity');
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
        return ($expectedCode !== null && is_string($expectedCode) && $this->niceize($code) == $this->niceize($expectedCode));
    }
}
