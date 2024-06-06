<?php

declare(strict_types=1);

namespace Gregwar\CaptchaBundle\Type;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvents;
use Symfony\Contracts\Translation\TranslatorInterface;
use Gregwar\CaptchaBundle\Validator\CaptchaValidator;
use Gregwar\CaptchaBundle\Generator\CaptchaGenerator;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Captcha type.
 *
 * @author Gregwar <g.passault@gmail.com>
 */
class CaptchaType extends AbstractType
{
    public const SESSION_KEY_PREFIX = '_captcha_';

    protected SessionInterface $session;

    protected CaptchaGenerator $generator;

    protected TranslatorInterface $translator;

    /** @var array<mixed> */
    private array $options;

    /**
     * @param array<mixed> $options
     */
    public function __construct(RequestStack $requestStack, CaptchaGenerator $generator, TranslatorInterface $translator, array $options)
    {
        $this->session = $requestStack->getSession();
        $this->generator = $generator;
        $this->translator = $translator;
        $this->options = $options;
    }

    /**
     * @param FormBuilderInterface<mixed> $builder
     * @param array<mixed> $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
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
     * @param FormView<mixed> $view
     * @param FormInterface<mixed> $form
     * @param array<mixed> $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options): void
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
    public function configureOptions(OptionsResolver $resolver): void
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
