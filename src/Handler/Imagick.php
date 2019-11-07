<?php
/**
 * Desc:
 * User: baagee
 * Date: 2019/11/7
 * Time: 下午8:47
 */

namespace BaAGee\Image\Handler;

use BaAGee\Image\Base\ImgHandlerInterface;
use BaAGee\Image\Image;

class Imagick extends ImgHandlerInterface
{

    public function open($imageFile)
    {
        // TODO: Implement open() method.
    }

    public function width()
    {
        // TODO: Implement width() method.
    }

    public function height()
    {
        // TODO: Implement height() method.
    }

    public function type()
    {
        // TODO: Implement type() method.
    }

    public function mime()
    {
        // TODO: Implement mime() method.
    }

    public function size()
    {
        // TODO: Implement size() method.
    }

    public function crop($w, $h, $x = 0, $y = 0, $width = null, $height = null)
    {
        // TODO: Implement crop() method.
    }

    public function save($imageFile, $type = null, $quality = 80, $interlace = true)
    {
        // TODO: Implement save() method.
    }

    public function thumb($width, $height, $type = Image::IMAGE_THUMB_SCALE)
    {
        // TODO: Implement thumb() method.
    }

    public function water($source, $location = Image::IMAGE_WATER_SOUTHEAST, $alpha = 80)
    {
        // TODO: Implement water() method.
    }

    public function text($text, $font, $size, $color = '#00000000', $location = Image::IMAGE_WATER_SOUTHEAST, $offset = 0, $angle = 0)
    {
        // TODO: Implement text() method.
    }
}