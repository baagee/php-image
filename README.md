# php-image
php image library php 基本的图像处理 来自thinkphp3.2内置的图片处理，稍微改了一下做成composer包

## 主要方法和参数
```php
// 打开图片
public function open($imageFile);
// 获取宽度
public function getWidth();
//获取高度
public function getHeight();
//获取类型
public function getType();
//获取mime
public function getMime();
//获取宽高
public function getSize();
// 裁剪图片
public function crop($w, $h, $x = 0, $y = 0, $width = null, $height = null);
// 保存图片 
public function save($imageFile, $type = null, $quality = 80, $interlace = true);
// 缩略图
public function thumb($width, $height, $type = Image::IMAGE_THUMB_SCALE);
// 图片水印
public function water($source, $location = Image::IMAGE_WATER_SOUTHEAST, $alpha = 80);
// 添加文字
public function text($text, $font, $size, $color = '#00000000', $location = Image::IMAGE_WATER_SOUTHEAST, $offset = 0, $angle = 0);
```

## 使用示例
支持Gd和imagick

### Gd使用示例
```php
include __DIR__ . '/../vendor/autoload.php';

// 创建一个image 使用Gd扩展
$image = new \BaAGee\Image\Image(\BaAGee\Image\Handler\Gd::class);
// 打开一个图片
$image->open('./chosence.jpg');
$mime   = $image->getMime();
$size   = $image->getSize();
$width  = $image->getWidth();
$height = $image->getHeight();
$type   = $image->getType();
var_dump($mime, $size, $width, $height, $type);
// 加文字水印并保存
$image->text("WTF!!!", './font.otf', 29, '#00eea1')->save('./images/text.jpg');
// 设置水印位置和旋转角度
$image->open('./wtf.png')->text('WTF!!!', './font.otf', 40,
    '#123456', \BaAGee\Image\Image::IMAGE_WATER_SOUTH, 0, 90
)->save('images/text.png');

// 裁剪图片
$image->open('./chosence.jpg')->crop(700, 500)->save('./images/crop.jpg');
// 裁剪突变并且设置文字水印
$image->open('./chosence.jpg')->crop(700, 500)
    ->text('hello!', './font.otf', 40)
    ->save('./images/crop_text.jpg');
// 缩略图
$image->open('./chosence.jpg')->thumb(400, 300)->save('./images/thumb.jpg');
// 缩略图并且裁剪并且加文字水印
$image->open('./chosence.jpg')->thumb(400, 300)
    ->crop(300, 200)
    ->text("thumb,crop", './font.otf', 40)
    ->save('./images/thumb_crop_text.jpg');
// 图片水印
$image->open('./chosence.jpg')->water('./wtf.png')->save('./images/water.jpg');
// gif图加文字水印
$image->open('./gif.gif')->text('huaji滑稽', './font.otf', 30)->save('./images/huaji_text.gif');
// gif缩略图
$image->open('./gif.gif')->thumb(200, 200)->save('./images/huaji_thumb.gif');
// gif图片水印
$image->open('./gui.gif')->water('./bm.png')->save('./images/gui_water.gif');

echo 'over' . PHP_EOL;
```

### imagick使用示例
```php
include __DIR__ . '/../vendor/autoload.php';

// 创建一个image 使用imagick扩展
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
```

### 详细使用方法见tests目录
