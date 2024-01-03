<?php

declare(strict_types=1);

namespace Gregwar\CaptchaBundle\Validator;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Captcha validator.
 *
 * @author Gregwar <g.passault@gmail.com>
 */
class CaptchaValidator
{
    private SessionInterface $session;

    /**
     * Session key to store the code.
     */
    private string $key;

    /**
     * Error message text for non-matching submissions.
     */
    private string $invalidMessage;

    /**
     * Configuration parameter used to bypass a required code match.
     */
    private ?string $bypassCode;

    /**
     * Number of form that the user can submit without captcha.
     */
    private int $humanity;

    /**
     * Translator.
     */
    private TranslatorInterface $translator;

    public function __construct(
        TranslatorInterface $translator,
        SessionInterface $session,
        string $key,
        string $invalidMessage,
        ?string $bypassCode,
        int $humanity
    ) {
        $this->translator = $translator;
        $this->session = $session;
        $this->key = $key;
        $this->invalidMessage = $invalidMessage;
        $this->bypassCode = $bypassCode;
        $this->humanity = $humanity;
    }

    public function validate(FormEvent $event): void
    {
        $form = $event->getForm();

        $code = $form->getData();
        $expectedCode = $this->getExpectedCode();

        if ($this->humanity > 0) {
            $humanity = $this->getHumanity();
            if ($humanity > 0) {
                $this->updateHumanity($humanity - 1);

                return;
            }
        }

        if (!(is_string($code) && ($this->compare($code, $expectedCode) || $this->compare($code, $this->bypassCode)))) {
            $form->addError(new FormError($this->translator->trans($this->invalidMessage, array(), 'validators')));
        } else {
            if ($this->humanity > 0) {
                $this->updateHumanity($this->humanity);
            }
        }

        $this->session->remove($this->key);

        if ($this->session->has($this->key.'_fingerprint')) {
            $this->session->remove($this->key.'_fingerprint');
        }
    }

    /**
     * Retrieve the expected CAPTCHA code.
     */
    protected function getExpectedCode(): ?string
    {
        $options = $this->session->get($this->key, array());

        if (is_array($options) && isset($options['phrase'])) {
            return $options['phrase'];
        }

        return null;
    }

    /**
     * Retrieve the humanity.
     */
    protected function getHumanity(): int
    {
        return (int) $this->session->get($this->key.'_humanity', 0);
    }

    protected function updateHumanity(int $newValue): void
    {
        if ($newValue > 0) {
            $this->session->set($this->key.'_humanity', $newValue);
        } else {
            $this->session->remove($this->key.'_humanity');
        }
    }

    protected function niceize(string $code): string
    {
        return strtr(strtolower($code), 'oil', '01l');
    }

    /**
     * Run a match comparison on the provided code and the expected code.
     */
    protected function compare(string $code, ?string $expectedCode): bool
    {
        return is_string($expectedCode) && $this->niceize($code) == $this->niceize($expectedCode);
    }
}
