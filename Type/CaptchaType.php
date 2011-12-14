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
use Gregwar\CaptchaBundle\DataTransformer\EmptyTransformer;

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
     * Generate image or data
     * @var boolean
     */
    protected $asFile;

    /**
     * Keep value between two requests ?
     * @var boolean
     */
    protected $keepValue;

    /**
     * Charset used
     * @var string
     */
    protected $charset;

    /**
     * Folder to save captcha images in,
     * relative to public web folder
     * @var string
     */
    protected $imageFolder;

    /**
     * Public web folder
     * @var string
     */
    protected $webPath;

    /**
     * Frequence of garbage collection in fractions of 1
     * @var int
     */
    protected $gcFreq;

    /**
     * Captcha font
     * @var string
     */
    protected $font;

    /**
     * Captcha quality
     * @var int
     */
    protected $quality;

    /**
     * Maximum age of images in minutes
     * @var int
     */
    protected $expiration;

    /**
     * The session
     * @var Symfony\Component\HttpFoundation\Session
     */
    protected $session;

    /**
     * Session key
     * @var string
     */
    private $key = 'captcha';


    public function __construct(Session $session, $config)
    {
        $this->session = $session;

        $this->width = $config['width'];
        $this->height = $config['height'];
        $this->length = $config['length'];
        $this->charset = $config['charset'];
        $this->keepValue = $config['keep_value'];
        $this->asFile = $config['as_file'];
        $this->imageFolder = $config['image_folder'];
        $this->webPath = $config['web_path'];
        $this->gcFreq = $config['gc_freq'];
        $this->expiration = $config['expiration'];
        $this->font = $config['font'];
        $this->quality = $config['quality'];
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

        if ($this->session->has($this->key.'_fingerprint')) {
            $fingerprint = $this->session->get($this->key.'_fingerprint');
        }

        $generator = new CaptchaGenerator($this->generateCaptchaValue(), $this->imageFolder, $this->webPath, $this->gcFreq, $this->expiration, $this->font, $fingerprint, $this->quality);

        if ($this->asFile) {
            $view->set('captcha_code', $generator->getFile($this->width, $this->height));
        } else {
            $view->set('captcha_code', $generator->getCode($this->width, $this->height));
        }
        $view->set('captcha_width', $this->width);
        $view->set('captcha_height', $this->height);

        if ($this->keepValue) {
            $this->session->set($this->key.'_fingerprint', $generator->getFingerprint());
        }
        
        $view->set('value', '');
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
        if (isset($options['as_file'])) {
            $this->asFile = $options['as_file'];
        }

        return array(
            'width' => $this->width,
            'height' => $this->height,
            'length' => $this->length,
            'as_file' => $this->asFile,
            'property_path' => false,
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
        if (!$this->keepValue || !$this->session->has($this->key)) {
            $value = '';
            $chars = str_split($this->charset);

            for ($i=0; $i<$this->length; $i++) {
                $value.= $chars[array_rand($chars)];
            }

            $this->session->set($this->key, $value);
        } else {
            $value = $this->session->get($this->key);
        }

        return $value;
    }
}
