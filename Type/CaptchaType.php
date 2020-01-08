<?php

declare(strict_types=1);

namespace Gregwar\CaptchaBundle\Type;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvents;
use Symfony\Contracts\Translation\TranslatorInterface;
use Gregwar\CaptchaBundle\Validator\CaptchaValidator;
use Gregwar\CaptchaBundle\Generator\CaptchaGenerator;

/**
 * Captcha type.
 *
 * @author Gregwar <g.passault@gmail.com>
 */
class CaptchaType extends AbstractType
{
    const SESSION_KEY_PREFIX = '_captcha_';

    /** @var SessionInterface */
    protected $session;

    /** @var CaptchaGenerator */
    protected $generator;

    /** @var TranslatorInterface */
    protected $translator;

    /** @var array */
    private $options = array();

    /**
     * @param SessionInterface    $session
     * @param CaptchaGenerator    $generator
     * @param TranslatorInterface $translator
     * @param array               $options
     */
    public function __construct(SessionInterface $session, CaptchaGenerator $generator, TranslatorInterface $translator, $options)
    {
        $this->session = $session;
        $this->generator = $generator;
        $this->translator = $translator;
        $this->options = $options;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $validator = new CaptchaValidator(
            $this->translator,
            $this->session,
            sprintf('%s%s', self::SESSION_KEY_PREFIX, $options['session_key']),
            $options['invalid_message'],
            $options['bypass_code'],
            $options['humanity']
        );

        $builder->addEventListener(FormEvents::POST_SUBMIT, array($validator, 'validate'));
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if ($options['reload'] && !$options['as_url']) {
            throw new \InvalidArgumentException('GregwarCaptcha: The reload option cannot be set without as_url, see the README for more information');
        }

        $sessionKey = sprintf('%s%s', self::SESSION_KEY_PREFIX, $options['session_key']);
        $isHuman = false;

        if ($options['humanity'] > 0) {
            $humanityKey = sprintf('%s_humanity', $sessionKey);
            if ($this->session->get($humanityKey, 0) > 0) {
                $isHuman = true;
            }
        }

        if ($options['as_url']) {
            $keys = $this->session->get($options['whitelist_key'], array());
            if (!in_array($sessionKey, $keys)) {
                $keys[] = $sessionKey;
            }
            $this->session->set($options['whitelist_key'], $keys);
            $options['session_key'] = $sessionKey;
        }

        $view->vars = array_merge($view->vars, array(
            'captcha_width' => $options['width'],
            'captcha_height' => $options['height'],
            'reload' => $options['reload'],
            'image_id' => uniqid('captcha_'),
            'captcha_code' => $this->generator->getCaptchaCode($options),
            'value' => '',
            'is_human' => $isHuman,
        ));

        $persistOptions = array();
        foreach (array('phrase', 'width', 'height', 'distortion', 'length',
        'quality', 'background_color', 'background_images', 'text_color', ) as $key) {
            $persistOptions[$key] = $options[$key];
        }

        $this->session->set($sessionKey, $persistOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $this->options['mapped'] = false;
        $resolver->setDefaults($this->options);
    }

    public function getParent(): string
    {
        return TextType::class;
    }

    public function getName(): string
    {
        return $this->getBlockPrefix();
    }

    public function getBlockPrefix(): string
    {
        return 'captcha';
    }
}
