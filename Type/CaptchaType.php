<?php

namespace Gregwar\CaptchaBundle\Type;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvents;

use Gregwar\CaptchaBundle\Validator\CaptchaValidator;
use Gregwar\CaptchaBundle\Generator\CaptchaGenerator;

/**
 * Captcha type
 *
 * @author Gregwar <g.passault@gmail.com>
 */
class CaptchaType extends AbstractType
{
    /**
     * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
     */
    protected $session;

    /**
     * The session key
     * @var string
     */
    protected $key = null;

    /**
     * @var \Gregwar\CaptchaBundle\Generator\CaptchaGenerator
     */
    protected $generator;

    /**
     * Options
     * @var array
     */
    private $options = array();

    /**
     * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
     * @param \Gregwar\CaptchaBundle\Generator\CaptchaGenerator $generator
     * @param array $options
     */
    public function __construct(SessionInterface $session, CaptchaGenerator $generator, $options)
    {
        $this->session      = $session;
        $this->generator    = $generator;
        $this->options      = $options;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->key = 'gcb_'.$builder->getForm()->getName();

        $validator = new CaptchaValidator(
            $this->session,
            $this->key,
            $options['invalid_message'],
            $options['bypass_code'],
            $options['humanity']
        );

        $builder->addEventListener(FormEvents::POST_BIND, array($validator, 'validate'));
    }

    /**
     * @param \Symfony\Component\Form\FormView $view
     * @param \Symfony\Component\Form\FormInterface $form
     * @param array $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $isHuman = false;

        if ($options['humanity'] > 0) {
            $humanityKey = $this->key.'_humanity';
            if ($this->session->get($humanityKey, 0) > 0) {
                $isHuman = true;
            }
        }

        $view->vars = array_merge($view->vars, array(
            'captcha_width'     => $options['width'],
            'captcha_height'    => $options['height'],
            'reload'            => $options['reload'],
            'id'                => uniqid('captcha_'),
            'captcha_code'      => $this->generator->getCaptchaCode($this->key, $options),
            'value'             => '',
            'is_human'          => $isHuman
        ));
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $this->options['property_path'] = false;
        $resolver->setDefaults($this->options);
    }

    /**
     * @return string
     */
    public function getParent()
    {
        return 'text';
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'captcha';
    }
}
