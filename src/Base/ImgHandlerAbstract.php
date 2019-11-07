<?php
/**
 * Desc:
 * User: 01372412
 * Date: 2019/11/7
 * Time: 下午8:27
 */

namespace BaAGee\Image\Base;

use BaAGee\Image\Image;

abstract class ImgHandlerAbstract
{
    abstract public function open($imageFile);

    abstract public function width();

    abstract public function height();

    abstract public function type();

    abstract public function mime();

    abstract public function size();

    abstract public function crop($w, $h, $x = 0, $y = 0, $width = null, $height = null);

    abstract public function save($imageFile, $type = null, $quality = 80, $interlace = true);

    abstract public function thumb($width, $height, $type = Image::IMAGE_THUMB_SCALE);

    abstract public function water($source, $location = Image::IMAGE_WATER_SOUTHEAST, $alpha = 80);

    abstract public function text($text, $font, $size, $color = '#00000000', $location = Image::IMAGE_WATER_SOUTHEAST, $offset = 0, $angle = 0);


}
