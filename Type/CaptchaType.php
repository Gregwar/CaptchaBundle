<?php

namespace Gregwar\CaptchaBundle\Type;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Exception\FormException;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormViewInterface;

use Gregwar\CaptchaBundle\Validator\CaptchaValidator;
use Gregwar\CaptchaBundle\Generator\CaptchaGenerator;
use Gregwar\CaptchaBundle\DataTransformer\EmptyTransformer;

/**
 * Captcha type
 *
 * @author Gregwar <g.passault@gmail.com>
 */
class CaptchaType extends AbstractType
{
    /**
     * Options
     * @var array
     */
    private $options = array();

    /**
     * Session key
     * @var string
     */
    private $key = 'captcha';

    public function __construct(Session $session, $config)
    {
        $this->session = $session;
        $this->options = $config;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->key = $builder->getForm()->getName();

        $builder->addValidator(
            new CaptchaValidator($this->session,
                                 $this->key,
                                 $options['invalid_message'],
                                 $options['bypass_code'])
        );
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $fingerprint = null;

        if ($options['keep_value'] && $this->session->has($this->key.'_fingerprint')) {
            $fingerprint = $this->session->get($this->key.'_fingerprint');
        }

        $generator = new CaptchaGenerator($this->generateCaptchaValue(),
                                          $options['image_folder'],
                                          $options['web_path'],
                                          $options['gc_freq'],
                                          $options['expiration'],
                                          $options['font'],
                                          $fingerprint,
                                          $options['quality']);

        if ($options['as_file']) {
            $captchaCode = $generator->getFile($options['width'], $options['height']);
        } else {
            $captchaCode = $generator->getCode($options['width'], $options['height']);
        }

        if ($options['keep_value']) {
            $this->session->set($this->key.'_fingerprint', $generator->getFingerprint());
        }

        $view->vars['captcha_width'] = $options['width'];
        $view->vars['captcha_height'] = $options['height'];
        $view->vars['captcha_code'] = $captchaCode;
        $view->vars['value'] = '';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $this->options['property_path'] = false;
        $resolver->setDefaults($this->options);
    }

    public function getParent()
    {
        return 'text';
    }

    public function getName()
    {
        return 'captcha';
    }

    private function generateCaptchaValue()
    {
        if (!$this->options['keep_value'] || !$this->session->has($this->key)) {
            $value = '';
            $chars = str_split($this->options['charset']);

            for ($i=0; $i<$this->options['length']; $i++) {
                $value.= $chars[array_rand($chars)];
            }

            $this->session->set($this->key, $value);
        } else {
            $value = $this->session->get($this->key);
        }

        return $value;
    }
}