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
     * The image height
     * @var integer
     */
    protected $height;
    
    /**
     * The image width
     * @var integer
     */
    protected $width;

    /** 
     * The session
     * @var Symfony\Component\HttpFoundation\Session
     */
    protected $session;
    
    private $key = 'captcha';


    public function __construct(Session $session, ContainerInterface $container)
    {
        $this->session = $session;
        $this->height = $container->getParameter('gregwar_captcha.image.height');
        $this->width  = $container->getParameter('gregwar_captcha.image.width');
        
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
