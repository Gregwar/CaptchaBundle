<?php

namespace Gregwar\CaptchaBundle\Generator;

use Symfony\Component\Finder\Finder;

/**
 * Generates a CAPTCHA image
 */
class CaptchaGenerator {

    /**
     * Name of folder for captcha images
     * @var string
     */
    public $imageFolder;

    /**
     * Absolute path to public web folder
     * @var string
     */
    public $webPath;

    /**
     * Frequence of garbage collection in fractions of 1
     * @var int
     */
    public $gcFreq;

    /**
     * Captcha Font
     * @var string
     */
    public $font;

    /**
     * Maximum age of images in minutes
     * @var int
     */
    public $expiration;

    /**
     * Random fingerprint
     * Useful to be able to regenerate exactly the same image
     * @var array
     */
    public $fingerprint;

    /**
     * Should fingerprint be used ?
     * @var boolean
     */
    public $use_fingerprint;

    /**
     * The captcha code
     * @var string
     */
    public $value;

    /**
     * Captcha quality
     * @var int
     */
    public $quality;

    public function __construct($value, $imageFolder, $webPath, $gcFreq, $expiration, $font, $fingerprint, $quality)
    {
        $this->value = $value;
        $this->imageFolder = $imageFolder;
        $this->webPath = $webPath;
        $this->gcFreq = intval($gcFreq);
        $this->expiration = intval($expiration);
        $this->font = $font;
        $this->fingerprint = $fingerprint;
        $this->use_fingerprint = (bool)$fingerprint;
        $this->quality = intval($quality);
    }

    /**
     * Get the captcha embeded code
     */
    public function getCode($width = 120, $height = 40)
    {
        return 'data:image/jpeg;base64,'.base64_encode($this->generate($width, $height));
    }

    /**
     * Creates a captcha image with provided dimensions
     * and randomly executes a garbage collection
     *
     * @param int $width
     * @param int $height
     * @return string Web path to the created image
     */
    public function getFile($width = 120, $height = 40)
    {
        if (mt_rand(1, $this->gcFreq) == 1) {
            $this->garbageCollection();
        }

        return $this->generate($width, $height, true);
    }

    /**
     * Returns a random number or the next number in the
     * fingerprint
     */
    public function rand($min, $max) 
    {
        if (!is_array($this->fingerprint)) {
            $this->fingerprint = array();
        }

        if ($this->use_fingerprint) {
            $value = current($this->fingerprint);
            next($this->fingerprint);
        } else {
            $value = mt_rand($min, $max);
            $this->fingerprint[] = $value;
        }
        return $value;
    }

    /**
     * Get the CAPTCHA fingerprint
     */
    public function getFingerprint()
    {
        return $this->fingerprint;
    }

    /**
     * Deletes all images in the configured folder
     * that are older than 10 minutes
     *
     * @return void
     */
    public function garbageCollection()
    {
        $finder = new Finder();
        $criteria = sprintf('<= now - %s minutes', $this->expiration);
        $finder->in($this->webPath . '/' . $this->imageFolder)
               ->date($criteria);

        foreach($finder->files() as $file)
        {
            unlink($file->getPathname());
        }
    }

    /**
     * Generate the image
     */
    public function generate($width, $height, $createFile = false)
    {
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
        $size = $width/strlen($this->value);
        $font = $this->font;
        $box = imagettfbbox($size, 0, $font, $this->value);
        $txt_width = $box[2] - $box[0];
        $txt_height = $box[1] - $box[7];

        imagettftext($i, $size, 0, ($width-$txt_width)/2, ($height-$txt_height)/2+$size, $col, $font, $this->value);

        // Distort the image
        $X = $this->rand(0, $width);
        $Y = $this->rand(0, $height);
        $Phase=$this->rand(0,10);
        $Scale = 1.3 + $this->rand(0,10000)/30000;
        $Amp=1+$this->rand(0,1000)/1000;
        $out = imagecreatetruecolor($width, $height);

        for ($x=0; $x<$width; $x++)
            for ($y=0; $y<$height; $y++) {
                $Vx=$x-$X;
                $Vy=$y-$Y;
                $Vn=sqrt($Vx*$Vx+$Vy*$Vy);

                if ($Vn!=0) {
                    $Vn2=$Vn+4*sin($Vn/8);
                    $nX=$X+($Vx*$Vn2/$Vn);
                    $nY=$Y+($Vy*$Vn2/$Vn);
                } else {
                    $nX=$X;
                    $nY=$Y;
                }
                $nY = $nY+$Scale*sin($Phase + $nX*0.2);

                $p = $this->bilinearInterpolate($nX-floor($nX), $nY-floor($nY),
                    $this->getCol($i,floor($nX),floor($nY)),
                    $this->getCol($i,ceil($nX),floor($nY)),
                    $this->getCol($i,floor($nX),ceil($nY)),
                    $this->getCol($i,ceil($nX),ceil($nY)));

                if ($p==0) {
                    $p=0xFFFFFF;
                }

                imagesetpixel($out, $x, $y, $p);
            }

        // Renders it
        if (!$createFile) {
            ob_start();
            imagejpeg($out, null, $this->quality);
            return ob_get_clean();
        } else {
            // Check if folder exists and create it if not
            if (!file_exists($this->webPath . '/' . $this->imageFolder)) {
                mkdir($this->webPath . '/' . $this->imageFolder, 0755);
            }
            $filename = md5(uniqid()) . '.jpg';
            $filepath = $this->webPath . '/' . $this->imageFolder . '/' . $filename;
            imagejpeg($out, $filepath, 15);
            return '/' . $this->imageFolder . '/' . $filename;
        }
    }

    protected function getCol($image, $x, $y)
    {
        $L = imagesx($image);
        $H = imagesy($image);
        if ($x<0 || $x>=$L || $y<0 || $y>=$H)
            return 0xFFFFFF;
        else return imagecolorat($image, $x, $y);
    }

    protected function getRGB($col) {
        return array(
            (int)($col >> 16) & 0xff,
            (int)($col >> 8) & 0xff,
            (int)($col) & 0xff,
        );
    }

    function bilinearInterpolate($x, $y, $nw, $ne, $sw, $se)
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
}

