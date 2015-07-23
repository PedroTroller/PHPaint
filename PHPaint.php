<?php

namespace PHPaint;

class PHPaint
{
    /*
     * @var Image
     */
    public $img;

    /**
     * Import data from picture at the parameter path.
     *
     * @param String $path Image path
     *
     * @return PHPaint Image data
     */
    public static function create($path)
    {
        if (file_exists($path)) {
            $tmpImg = null;
            $parts = preg_split('/\./', $path);
            if (count($parts) > 1) {
                switch (strtolower($parts[count($parts) - 1])) {
                case 'jpg':
                    try {
                        $tmpImg = imagecreatefromjpeg($path);
                    } catch (Exception $e) {
                        $this->img = null;
                    }
                    break;
                case 'jpeg':
                    try {
                        $tmpImg = imagecreatefromjpeg($path);
                    } catch (Exception $e) {
                        $this->img = null;
                    }
                    break;
                case 'png':
                    try {
                        $tmpImg = imagecreatefrompng($path);
                    } catch (Exception $e) {
                        $this->img = null;
                    }
                    break;
                case 'gif':
                    try {
                        $tmpImg = imagecreatefromgif($path);
                    } catch (Exception $e) {
                        $this->img = null;
                    }
                    break;
                }
            }

            return (new self($tmpImg));
        } else {
            throw new Exception('Fichier '.$path.' introuvable.');
        }
    }

    /**
     * Create an empty picture painted with colors in parameters.
     *
     * @param Integer $r Red level
     * @param Integer $g Green level
     * @param Integer $b Blue level
     * @param Integer $x Picture width
     * @param Integer $y Picture heigth
     *
     * @return PHPaint Image data
     */
    public static function createBackground($r, $g, $b, $x, $y)
    {
        $img = imagecreatetruecolor($x, $y);
        imagefill($img, 0, 0, imagecolorallocate($img, $r, $g, $b)); //backgroung creation
        return (new self($img)); //return a new PHPaint object
    }

    /**
     * @param Image $img Image data
     */
    public function __construct($img)
    {
        $this->img = $img;
    }

    /**
     * Get image data from current PHPaint object.
     *
     * @return Image Image data
     */
    public function getStream()
    {
        return $this->img;
    }

    /**
     * Get image width from current PHPaint object.
     *
     * @return Integer Image width
     */
    public function getX()
    {
        return imagesx($this->img);
    }

    /**
     * Get image height from current PHPaint object.
     *
     * @return Integer Image height
     */
    public function getY()
    {
        return imagesy($this->img);
    }

    /**
     * Get transparence from current PHPaint object.
     *
     * @return PHPaint Transparent PHPaint object
     */
    public function getTransparent()
    {
        $res = imagecreatetruecolor($this->getX(), $this->getY());
        $transparent = imagecolorallocate($res, 255, 255, 255);
        imagecopymerge($res, $this->getStream(), 0, 0, 0, 0, $this->getX(), $this->getY(), 100);
        imagecolortransparent($res, $transparent);

        return (new self($res));
    }

    /**
     * Resize PHPaint object at the following maximum dimensions.
     *
     * @param Integer $x Maximum width
     * @param Integer $y Maximum height
     *
     * @return PHPaint PHPaint resized object
     */
    public function resizeIn($xdst, $ydst)
    {
        $xres = $xdst;
        $yres = $ydst;
        if ($yres < (($xdst * $this->getY()) / $this->getX())) {
            $xres = (($ydst * $this->getX()) / $this->getY());
        } else {
            $yres = (($xdst * $this->getY()) / $this->getX());
        }
        $res = imagecreatetruecolor($xres, $yres);
        imagecopyresampled($res, $this->img, 0, 0, 0, 0, $xres, $yres, $this->getX(), $this->getY());

        return (new self($res));
    }

    /**
     * Resize PHPaint object at the following minimum dimensions.
     *
     * @param Integer $x Minimum width
     * @param Integer $y Minimum height
     *
     * @return PHPaint PHPaint resized object
     */
    public function resizeOut($xdst, $ydst)
    {
        $xres = $xdst;
        $yres = $ydst;
        if ($yres >= (($xdst * $this->getY()) / $this->getX())) {
            $xres = (($ydst * $this->getX()) / $this->getY());
        } else {
            $yres = (($xdst * $this->getY()) / $this->getX());
        }
        $res = imagecreatetruecolor($xres, $yres);
        imagecopyresampled($res, $this->img, 0, 0, 0, 0, $xres, $yres, $this->getX(), $this->getY());

        return (new self($res));
    }

    /**
     * Paste the PHPaint object in parameter in the current PHPaint object and return the result.
     *
     * @param PHPaint $imgFront Image to paste
     * @param Integer $opacite  Opacity of $imgFront (0 to 100)
     *
     * @return PHPaint Merge of the current PHPaint object and $img
     */
    public function mergeIn(PHPaint $imgFront, $opacite)
    {
        $imgFront = $imgFront->resizeIn($this->getX(), $this->getY());
        $x = ($this->getX() - $imgFront->getX()) / 2;
        $y = ($this->getY() - $imgFront->getY()) / 2;
        $res = $this->getStream();
        imagecopymerge($res, $imgFront->getStream(), $x, $y, 0, 0, $imgFront->getX(), $imgFront->getY(), $opacite);

        return (new self($res));
    }

    /**
     * Paste the PHPaint object in parameter to the middle of the current PHPaint object and return the result.
     *
     * @param PHPaint $imgFront Image to paste
     * @param Integer $opacite  Opacity of $imgFront (0 to 100)
     * @param Integer $ratio    Per cent of the original width and height of $imgFront
     *
     * @return PHPaint Merge of the current PHPaint object and $img
     */
    public function mergeMiddle(PHPaint $imgFront, $opacite, $ratio)
    {
        $imgFront = $imgFront->resizeIn($this->getX() / $ratio, $this->getY() / $ratio);
        $x = ($this->getX() - $imgFront->getX()) / 2;
        $y = ($this->getY() - $imgFront->getY()) / 2;
        $res = $this->getStream();
        imagecopymerge($res, $imgFront->getStream(), $x, $y, 0, 0, $imgFront->getX(), $imgFront->getY(), $opacite);

        return (new self($res));
    }

    /**
     * Paste the PHPaint object in parameter to a corner of the current PHPaint object and return the result.
     *
     * @param PHPaint $imgFront Image to paste
     * @param Integer $opacite  Opacity of $imgFront (0 to 100)
     * @param Integer $ratio    Per cent of the original width and height of $imgFront
     * @param Integer $corner   Corner of the current PHPaint object (00 or 01 or 10 or 11)
     *
     * @return PHPaint Merge of the current PHPaint object and $img
     */
    public function mergeCorner(PHPaint $imgFront, $opacite, $ratio, $corner)
    {
        $imgFront = $imgFront->resizeIn($this->getX() / $ratio, $this->getY() / $ratio);
        $x = 0;
        $y = 0;
        switch ($corner) {
        case 0:
            $x = 0;
            $y = 0;
            break;
        case 1:
            $x = 0;
            $y = $this->getY() - ($imgFront->getY());
            break;
        case 10:
            $x = $this->getX() - ($imgFront->getX());
            $y = 0;
            break;
        case 11:
            $x = $this->getX() - ($imgFront->getX());
            $y = $this->getY() - ($imgFront->getY());
            break;
        }
        $res = $this->getStream();
        imagecopymerge($res, $imgFront->getStream(), $x, $y, 0, 0, $imgFront->getX(), $imgFront->getY(), $opacite);

        return (new self($res));
    }

    /**
     * Create a new PHPaint object from the rotation of the current PHPaint object.
     *
     * @param Integer $angle Rotation angle
     *
     * @return PHPaint PHPaint object with image rotation
     */
    public function rotation($angle)
    {
        $whitecolor = imagecolorallocate($this->getStream(), 0xFF, 0xFF, 0xFF);

        return (new self(imagerotate($this->getStream(), $angle, $whitecolor)));
    }

    /**
     * Resize PHPaint object at the following minimum dimensions and cut borders out of the following dimensions.
     *
     * @param Integer $x Minimum width
     * @param Integer $y Minimum height
     *
     * @return PHPaint PHPaint resized and cut object
     */
    public function bizot($x, $y)
    {
        $img = $this->ResizeOut($x, $y);
        $res = imagecreatetruecolor($x, $y);
        imagecopyresampled($res, $img->getStream(), -($img->getX() - $x) / 2, -($img->getY() - $y) / 2, 0, 0, $img->getX(), $img->getY(), $img->getX(), $img->getY());

        return (new self($res));
    }

    /**
     * Save current PHPaint object as a JPG picture.
     *
     * @param String $path Target path
     */
    public function saveJPG($path)
    {
        imagejpeg($this->getStream(), $path);
    }

    /**
     * Save current PHPaint object as a PNG picture.
     *
     * @param String $path Target path
     */
    public function savePNG($path)
    {
        imagepng($this->getStream(), $path);
    }

    /**
     * Save current PHPaint object as a GIF picture.
     *
     * @param String $path Target path
     */
    public function saveGif($path)
    {
        imagegif($this->getStream(), $path);
    }

    /**
     * Destroy current PHPaint object.
     */
    public function destroy()
    {
        imagedestroy($this->img);
    }
}
