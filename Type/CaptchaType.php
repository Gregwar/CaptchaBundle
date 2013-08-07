<?php

namespace Gregwar\CaptchaBundle\Type;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Translation\TranslatorInterface;

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
     * @var TranslatorInterface
     */
    protected $translator;

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
    public function __construct(SessionInterface $session, CaptchaGenerator $generator, TranslatorInterface $translator, $options)
    {
        $this->session      = $session;
        $this->generator    = $generator;
        $this->translator = $translator;
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
            $this->translator,
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

        if ($options['reload'] && !$options['as_url']) {
            throw new \InvalidArgumentException('GregwarCaptcha: The reload option cannot be set without as_url, see the README for more information');
        }

        if ($options['humanity'] > 0) {
            $humanityKey = $this->key.'_humanity';
            if ($this->session->get($humanityKey, 0) > 0) {
                $isHuman = true;
            }
        }

        if ($options['as_url']) {
            $key = $this->key;
            $keys = $this->session->get($options['whitelist_key'], array());
            if (!in_array($key, $keys)) {
                $keys[] = $key;
            }
            $this->session->set($options['whitelist_key'], $keys);
            $options['session_key'] = $this->key;
        }

        $view->vars = array_merge($view->vars, array(
            'captcha_width'     => $options['width'],
            'captcha_height'    => $options['height'],
            'reload'            => $options['reload'],
            'image_id'          => uniqid('captcha_'),
            'captcha_code'      => $this->generator->getCaptchaCode($options),
            'value'             => '',
            'is_human'          => $isHuman
        ));

        $persistOptions = array();
        foreach (array('phrase', 'width', 'height', 'distortion', 'length', 'quality') as $key) {
            $persistOptions[$key] = $options[$key];
        }

        $this->session->set($this->key, $persistOptions);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $this->options['mapped'] = false;
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
