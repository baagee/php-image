<?php
/**
 * Desc:
 * User: 01372412
 * Date: 2019/11/7
 * Time: 下午10:36
 */
include __DIR__ . '/../vendor/autoload.php';

// 创建一个image
$image = new \BaAGee\Image\Image(\BaAGee\Image\Handler\Imagick::class);
// 打开一个图片
$image->open('./chosence.jpg');
$mime   = $image->getMime();
$size   = $image->getSize();
$width  = $image->getWidth();
$height = $image->getHeight();
$type   = $image->getType();
var_dump($mime, $size, $width, $height, $type);
// 加文字水印并保存
$image->text("WTF!!!", './font.otf', 29, '#00eea1')->save('./images/imagick/text.jpg');
// 设置水印位置和旋转角度
$image->open('./wtf.png')->text('WTF!!!', './font.otf', 40,
    '#123456', \BaAGee\Image\Image::IMAGE_WATER_SOUTH, 0, 90
)->save('images/imagick/text.png');

// 裁剪图片
$image->open('./chosence.jpg')->crop(700, 500)->save('./images/imagick/crop.jpg');
// 裁剪突变并且设置文字水印
$image->open('./chosence.jpg')->crop(700, 500)
    ->text('hello!', './font.otf', 40)
    ->save('./images/imagick/crop_text.jpg');
// 缩略图
$image->open('./chosence.jpg')->thumb(400, 300)->save('./images/imagick/thumb.jpg');
// 缩略图并且裁剪并且加文字水印
$image->open('./chosence.jpg')->thumb(400, 300)
    ->crop(300, 200)
    ->text("thumb,crop", './font.otf', 40)
    ->save('./images/imagick/thumb_crop_text.jpg');
// 图片水印
$image->open('./chosence.jpg')->water('./wtf.png')->save('./images/imagick/water.jpg');
// gif图加文字水印
$image->open('./gif.gif')->text('huaji滑稽', './font.otf', 30)->save('./images/imagick/huaji_text.gif');
// gif缩略图
$image->open('./gif.gif')->thumb(200, 200)->save('./images/imagick/huaji_thumb.gif');
// gif图片水印
$image->open('./gui.gif')->water('./bm.png')->save('./images/imagick/gui_water.gif');

echo 'over' . PHP_EOL;

