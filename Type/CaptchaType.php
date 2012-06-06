<?php

namespace Gregwar\CaptchaBundle\Type;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\Exception\FormException;

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

    public function buildForm(FormBuilder $builder, array $options)
    {
        $this->key = $builder->getForm()->getName();

        $builder->addValidator(
            new CaptchaValidator($this->session, $this->key)
        );
    }

    public function buildView(FormView $view, FormInterface $form)
    {
        $fingerprint = null;

        if ($this->options['keep_value'] && $this->session->has($this->key.'_fingerprint')) {
            $fingerprint = $this->session->get($this->key.'_fingerprint');
        }

        $generator = new CaptchaGenerator($this->generateCaptchaValue(), 
                                          $this->options['image_folder'], 
                                          $this->options['web_path'], 
                                          $this->options['gc_freq'], 
                                          $this->options['expiration'], 
                                          $this->options['font'], 
                                          $fingerprint, 
                                          $this->options['quality']);

        if ($this->options['as_file']) {
            $view->set('captcha_code', $generator->getFile($this->options['width'], $this->options['height']));
        } else {
            $view->set('captcha_code', $generator->getCode($this->options['width'], $this->options['height']));
        }
        $view->set('captcha_width', $this->options['width']);
        $view->set('captcha_height', $this->options['height']);

        if ($this->options['keep_value']) {
            $this->session->set($this->key.'_fingerprint', $generator->getFingerprint());
        }
        
        $view->set('value', '');
    }

    public function getDefaultOptions()
    {
        $this->options = array_replace($this->options, $options);
        $this->options['property_path'] = false;

        return $this->options;
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
