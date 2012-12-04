<?php

namespace Gregwar\CaptchaBundle\Generator;

use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Generates a CAPTCHA image
 */
class CaptchaGenerator
{
    /**
     * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
     */
    protected $session;

    /**
     * Name of the whitelist key
     * @var string
     */
    protected $whitelistKey;

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    protected $router;

    /**
     * Name of folder for captcha images
     * @var string
     */
    protected $imageFolder;

    /**
     * Absolute path to public web folder
     * @var string
     */
    protected $webPath;

    /**
     * Frequency of garbage collection in fractions of 1
     * @var int
     */
    protected $gcFreq;

    /**
     * Maximum age of images in minutes
     * @var int
     */
    protected $expiration;

    /**
     * The fingerprint used to generate the image details across requests
     * @var array|null
     */
    protected $fingerprint;

    /**
     * Whether this instance should use the fingerprint
     * @var bool
     */
    protected $useFingerprint;

    /**
     * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
     * @param \Symfony\Component\Routing\RouterInterface $router
     * @param string $imageFolder
     * @param string $webPath
     * @param int $gcFreq
     * @param int $expiration
     */
    public function __construct(SessionInterface $session, RouterInterface $router, $imageFolder, $webPath, $gcFreq, $expiration, $whitelistKey)
    {
        $this->session          = $session;
        $this->router           = $router;
        $this->imageFolder      = $imageFolder;
        $this->webPath          = $webPath;
        $this->gcFreq           = $gcFreq;
        $this->expiration       = $expiration;
        $this->whitelistKey     = $whitelistKey;
    }

    /**
     * Get the captcha URL, stream, or filename that will go in the image's src attribute
     *
     * @param $key
     * @param array $options
     *
     * @return array
     */
    public function getCaptchaCode($key, array $options)
    {
        // Randomly execute garbage collection and returns the image filename
        if ($options['as_file']) {
            if (mt_rand(1, $this->gcFreq) == 1) {
                $this->garbageCollection();
            }

            return $this->generate($key, $options);
        }

        // Returns the configured URL for image generation
        if ($options['as_url']) {
            $keys = $this->session->get($this->whitelistKey, array());
            if (!in_array($key, $keys)) {
                $keys[] = $key;
            }
            $this->session->set($this->whitelistKey, $keys);
            return $this->router->generate('gregwar_captcha.generate_captcha', array('key' => $key));
        }

        return 'data:image/jpeg;base64,' . base64_encode($this->generate($key, $options));
    }

    /**
     * Generate the image
     */
    public function generate($key, array $options)
    {
        $width  = $options['width'];
        $height = $options['height'];

        if ($options['keep_value'] && $this->session->has($key . '_fingerprint')) {
            $this->fingerprint = $this->session->get($key . '_fingerprint');
            $this->useFingerprint = true;
        } else {
            $this->fingerprint = null;
            $this->useFingerprint = false;
        }

        $captchaValue = $this->getCaptchaValue($key, $options['keep_value'], $options['charset'], $options['length']);

        $i = imagecreatetruecolor($width,$height);
        $col = imagecolorallocate($i, $this->rand(0,110), $this->rand(0,110), $this->rand(0,110));

        imagefill($i, 0, 0, 0xFFFFFF);

        // Draw random lines
        for ($t=0; $t<10; $t++) {
            $tcol = imagecolorallocate($i, 100+$this->rand(0,150), 100+$this->rand(0,150), 100+$this->rand(0,150));
            $Xa = $this->rand(0, $width);
            $Ya = $this->rand(0, $height);
            $Xb = $this->rand(0, $width);
            $Yb = $this->rand(0, $height);
            imageline($i, $Xa, $Ya, $Xb, $Yb, $tcol);
        }

        // Write CAPTCHA text
        $size       = $width / strlen($captchaValue);
        $font       = $options['font'];
        $box        = imagettfbbox($size, 0, $font, $captchaValue);
        $textWidth  = $box[2] - $box[0];
        $textHeight = $box[1] - $box[7];

        imagettftext($i, $size, 0, ($width - $textWidth) / 2, ($height - $textHeight) / 2 + $size, $col, $font, $captchaValue);

        // Distort the image
        $X     = $this->rand(0, $width);
        $Y     = $this->rand(0, $height);
        $phase = $this->rand(0, 10);
        $scale = 1.3 + $this->rand(0, 10000) / 30000;
        $out   = imagecreatetruecolor($width, $height);

        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {
                $Vx = $x - $X;
                $Vy = $y - $Y;
                $Vn = sqrt($Vx * $Vx + $Vy * $Vy);

                if ($Vn != 0) {
                    $Vn2 = $Vn + 4 * sin($Vn / 8);
                    $nX  = $X + ($Vx * $Vn2 / $Vn);
                    $nY  = $Y + ($Vy * $Vn2 / $Vn);
                } else {
                    $nX = $X;
                    $nY = $Y;
                }
                $nY = $nY + $scale * sin($phase + $nX * 0.2);

                $p = $this->bilinearInterpolate($nX - floor($nX), $nY - floor($nY),
                    $this->getCol($i, floor($nX), floor($nY)),
                    $this->getCol($i, ceil($nX), floor($nY)),
                    $this->getCol($i, floor($nX), ceil($nY)),
                    $this->getCol($i, ceil($nX), ceil($nY)));

                if ($p == 0) {
                    $p = 0xFFFFFF;
                }

                imagesetpixel($out, $x, $y, $p);
            }
        }

        if ($options['keep_value']) {
            $this->session->set($key . '_fingerprint', $this->fingerprint);
        }

        // Renders it
        if (!$options['as_file']) {
            ob_start();
            imagejpeg($out, null, $options['quality']);

            return ob_get_clean();
        }

        // Check if folder exists and create it if not
        if (!file_exists($this->webPath . '/' . $this->imageFolder)) {
            mkdir($this->webPath . '/' . $this->imageFolder, 0755);
        }
        
        $filename = md5(uniqid()) . '.jpg';
        $filepath = $this->webPath . '/' . $this->imageFolder . '/' . $filename;
        imagejpeg($out, $filepath, 15);

        return '/' . $this->imageFolder . '/' . $filename;
    }

    /**
     * Generate a new captcha value or pull the existing one from the session
     *
     * @param string $key
     * @param bool $keepValue
     * @param string $charset
     * @param int $length
     *
     * @return mixed|string
     */
    protected function getCaptchaValue($key, $keepValue, $charset, $length)
    {
        if ($keepValue && $this->session->has($key)) {
            return $this->session->get($key);
        }

        $value = '';
        $chars = str_split($charset);

        for ($i=0; $i < $length; $i++) {
            $value .= $chars[array_rand($chars)];
        }

        $this->session->set($key, $value);

        return $value;
    }

    /**
     * Deletes all images in the configured folder
     * that are older than the configured number of minutes
     *
     * @return void
     */
    protected function garbageCollection()
    {
        $finder = new Finder();
        $criteria = sprintf('<= now - %s minutes', $this->expiration);
        $finder->in($this->webPath . '/' . $this->imageFolder)
            ->date($criteria);

        foreach($finder->files() as $file) {
            unlink($file->getPathname());
        }
    }

    /**
     * Returns a random number or the next number in the
     * fingerprint
     */
    protected function rand($min, $max)
    {
        if (!is_array($this->fingerprint)) {
            $this->fingerprint = array();
        }

        if ($this->useFingerprint) {
            $value = current($this->fingerprint);
            next($this->fingerprint);
        } else {
            $value = mt_rand($min, $max);
            $this->fingerprint[] = $value;
        }

        return $value;
    }

    protected function bilinearInterpolate($x, $y, $nw, $ne, $sw, $se)
    {
        list($r0, $g0, $b0) = $this->getRGB($nw);
        list($r1, $g1, $b1) = $this->getRGB($ne);
        list($r2, $g2, $b2) = $this->getRGB($sw);
        list($r3, $g3, $b3) = $this->getRGB($se);

        $cx = 1.0 - $x;
        $cy = 1.0 - $y;

        $m0 = $cx * $r0 + $x * $r1;
        $m1 = $cx * $r2 + $x * $r3;
        $r = (int)($cy * $m0 + $y * $m1);

        $m0 = $cx * $g0 + $x * $g1;
        $m1 = $cx * $g2 + $x * $g3;
        $g = (int)($cy * $m0 + $y * $m1);

        $m0 = $cx * $b0 + $x * $b1;
        $m1 = $cx * $b2 + $x * $b3;
        $b = (int)($cy * $m0 + $y * $m1);

        return ($r << 16) | ($g << 8) | $b;
    }

    protected function getCol($image, $x, $y)
    {
        $L = imagesx($image);
        $H = imagesy($image);
        if ($x < 0 || $x >= $L || $y < 0 || $y >= $H) {
            return 0xFFFFFF;
        }

        return imagecolorat($image, $x, $y);
    }

    protected function getRGB($col) {
        return array(
            (int)($col >> 16) & 0xff,
            (int)($col >> 8) & 0xff,
            (int)($col) & 0xff,
        );
    }
}

