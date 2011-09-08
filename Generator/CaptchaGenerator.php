<?php

namespace Gregwar\CaptchaBundle\Generator;

/**
 * Generates a CAPTCHA image
 */
class CaptchaGenerator {

    public $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function getCode($width = 120, $height = 40)
    {
        return 'data:image/jpeg;base64,'.base64_encode($this->generate($width, $height));
    }
    
    /**
     * Generate the image
     */
    public function generate($width, $height)
    {
        $i = imagecreatetruecolor($width,$height);

        $col = imagecolorallocate($i, mt_rand(0,110), mt_rand(0,110), mt_rand(0,110));

        imagefill($i, 0, 0, 0xFFFFFF);

        // Draw random lines
        for ($t=0; $t<10; $t++) {
            $tcol = imagecolorallocate($i, 100+mt_rand(0,150), 100+mt_rand(0,150), 100+mt_rand(0,150));
            $Xa = mt_rand(0, 120);
            $Ya = mt_rand(0, 40);
            $Xb = mt_rand(0, 120);
            $Yb = mt_rand(0, 40);
            imageline($i, $Xa, $Ya, $Xb, $Yb, $tcol);
        }

        // Write CAPTCHA text
        imagettftext($i, 28, 0, 5, 32, $col, dirname(__FILE__).'/Font/captcha.ttf', $this->value);

        // Distort the image
        $X = mt_rand(0, 120);
        $Y = mt_rand(0, 40);
        $Phase=mt_rand(0,10);
        $Scale = 1.3 + mt_rand(0,10000)/30000;
        $Amp=1+mt_rand(0,1000)/1000;
        $out = imagecreatetruecolor(120,40);

        for ($x=0; $x<120; $x++)
            for ($y=0; $y<40; $y++) {
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
        ob_start();
        imagejpeg($out, null, 15);
        return ob_get_clean();
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

