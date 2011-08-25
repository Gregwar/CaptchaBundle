<?php

namespace Gregwar\CaptchaBundle\Type;

use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Form\Exception\FormException;
use Symfony\Component\HttpFoundation\Session;

use Gregwar\CaptchaBundle\Validator\CaptchaValidator;

/**
 * Captcha type
 *
 * @author Gregwar <g.passault@gmail.com>
 */
class CaptchaType extends AbstractType
{
    private $key = 'captcha';

    protected $session;

    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->addValidator(
            new CaptchaValidator($this->session, $this->key)
        );
    }

    public function buildView(FormView $view, FormInterface $form)
    {
        $view->set('captcha_code', $this->generateCaptchaValue());
    }    

    public function getParent(array $options)
    {
        return 'text';
    }

    public function getName()
    {
        return 'captcha';
    }

    private function generateCaptchaValue()
    {
        $charset = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $value = '';
        $chars = str_split($charset);

        for ($i=0; $i<5; $i++) {
            $value.= $chars[array_rand($chars)];
        }

        $this->session->set($this->key, $value);

        return $value;
    }
}
