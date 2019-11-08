<?php
/**
 * Desc: 图片操作接口
 * User: baagee
 * Date: 2019/11/7
 * Time: 下午8:27
 */

namespace BaAGee\Image\Base;

use BaAGee\Image\Image;

interface  ImgHandlerInterface
{
    public function __construct($imageFile = '');

    public function open($imageFile);

    public function getWidth();

    public function getHeight();

    public function getType();

    public function getMime();

    public function getSize();

    public function crop($w, $h, $x = 0, $y = 0, $width = null, $height = null);

    public function save($imageFile, $type = null, $quality = 80, $interlace = true);

    public function thumb($width, $height, $type = Image::IMAGE_THUMB_SCALE);

    public function water($source, $location = Image::IMAGE_WATER_SOUTHEAST, $alpha = 80);

    public function text($text, $font, $size, $color = '#00000000', $location = Image::IMAGE_WATER_SOUTHEAST, $offset = 0, $angle = 0);
}
