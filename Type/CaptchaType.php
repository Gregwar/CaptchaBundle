<?php

namespace Gregwar\CaptchaBundle\Type;

use Symfony\Component\HttpFoundation\Session;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\Exception\FormException;

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
     * The image width
     * @var integer
     */
    protected $width;

    /**
     * The image height
     * @var integer
     */
    protected $height;

    /**
     * The code length
     * @var integer
     */
    protected $length;

    /** 
     * The session
     * @var Symfony\Component\HttpFoundation\Session
     */
    protected $session;
    
    private $key = 'captcha';


    public function __construct(Session $session, $width, $height, $length)
    {
        $this->session = $session;
        $this->width = $width;
        $this->height = $height;
        $this->length = $length;
    }

    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->addValidator(
            new CaptchaValidator($this->session, $this->key)
        );
    }

    public function buildView(FormView $view, FormInterface $form)
    {
        $generator = new CaptchaGenerator($this->generateCaptchaValue());

        $view->set('captcha_code', $generator->getCode($this->width, $this->height));
        $view->set('captcha_width', $this->width);
        $view->set('captcha_height', $this->height);
    }

    public function getDefaultOptions(array $options = array()) 
    {
        if (isset($options['width'])) {
            $this->width = $options['width'];
        }
        if (isset($options['height'])) {
            $this->height = $options['height'];
        }
        if (isset($options['length'])) {
            $this->length = $options['length'];
        }

        return array(
            'width' => $this->width,
            'height' => $this->height,
            'length' => $this->length
        );
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

        for ($i=0; $i<$this->length; $i++) {
            $value.= $chars[array_rand($chars)];
        }

        $this->session->set($this->key, $value);

        return $value;
    }
}
