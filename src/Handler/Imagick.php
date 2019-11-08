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

/**
 * Class Imagick
 * @package BaAGee\Image\Handler
 */
class Imagick implements ImgHandlerInterface
{
    /**
     * @var \Imagick
     */
    private $imgSource;
    /**
     * @var array
     */
    private $imgInfo;

    /**
     * Imagick constructor.
     * @param string $imageFile 图片文件路径
     * @throws \ImagickException
     */
    public function __construct($imageFile = '')
    {
        if (!empty($imageFile) && is_file($imageFile)) {
            $this->open($imageFile);
        }
    }

    /**
     * 打开图像
     * @param string $imageFile 图片路径
     * @return $this
     * @throws \ImagickException
     */
    public function open($imageFile)
    {
        //检测图像文件
        if (!is_file($imageFile)) {
            throw new \Exception('不存在的图像文件');
        }

        //销毁已存在的图像
        empty($this->imgSource) || $this->imgSource->destroy();

        //载入图像
        $this->imgSource = new \Imagick(realpath($imageFile));

        //设置图像信息
        $this->imgInfo = [
            'width'  => $this->imgSource->getImageWidth(),
            'height' => $this->imgSource->getImageHeight(),
            'type'   => strtolower($this->imgSource->getImageFormat()),
            'mime'   => $this->imgSource->getImageMimeType(),
        ];
        return $this;
    }

    /**
     * 获取宽度
     * @return mixed
     * @throws \Exception
     */
    public function getWidth()
    {
        if (empty($this->imgSource)) {
            throw new \Exception('没有指定图像资源');
        }
        return $this->imgInfo['width'];
    }

    /**
     * 获取高度
     * @return mixed
     * @throws \Exception
     */
    public function getHeight()
    {
        if (empty($this->imgSource)) {
            throw new \Exception('没有指定图像资源');
        }
        return $this->imgInfo['height'];
    }

    /**
     * 获取类型
     * @return mixed
     * @throws \Exception
     */
    public function getType()
    {
        if (empty($this->imgSource)) {
            throw new \Exception('没有指定图像资源');
        }
        return $this->imgInfo['type'];
    }

    /**
     * 获取mime类型
     * @return mixed
     * @throws \Exception
     */
    public function getMime()
    {
        if (empty($this->imgSource)) {
            throw new \Exception('没有指定图像资源');
        }
        return $this->imgInfo['mime'];
    }

    /**
     * 获取宽高
     * @return array
     * @throws \Exception
     */
    public function getSize()
    {
        if (empty($this->imgSource)) {
            throw new \Exception('没有指定图像资源');
        }
        return [
            'width'  => $this->imgInfo['width'],
            'height' => $this->imgInfo['height']
        ];
    }

    /**
     * 裁剪图片
     * @param integer $w      裁剪区域宽度
     * @param integer $h      裁剪区域高度
     * @param integer $x      裁剪区域x坐标
     * @param integer $y      裁剪区域y坐标
     * @param integer $width  图像保存宽度
     * @param integer $height 图像保存高度
     * @return $this
     * @throws \Exception
     */
    public function crop($w, $h, $x = 0, $y = 0, $width = null, $height = null)
    {
        if (empty($this->imgSource)) {
            throw new \Exception('没有可以被裁剪的图像资源');
        }

        //设置保存尺寸
        empty($width) && $width = $w;
        empty($height) && $height = $h;

        //裁剪图片
        if ('gif' == $this->imgInfo['type']) {
            $img = $this->imgSource->coalesceImages();
            $this->imgSource->destroy(); //销毁原图

            //循环裁剪每一帧
            do {
                $this->_crop($w, $h, $x, $y, $width, $height, $img);
            } while ($img->nextImage());

            //压缩图片
            $this->imgSource = $img->deconstructImages();
            $img->destroy(); //销毁临时图片
        } else {
            $this->_crop($w, $h, $x, $y, $width, $height);
        }
        return $this;
    }

    /**
     * 保存图片
     * @param string $imageFile 保存图片的路径
     * @param null   $type      图片类型
     * @param int    $quality   图片质量
     * @param bool   $interlace 是否对JPEG类型图像设置隔行扫描 Imagick用不到
     * @return bool
     * @throws \Exception
     */
    public function save($imageFile, $type = null, $quality = 80, $interlace = true)
    {
        if (empty($this->imgSource)) {
            throw new \Exception('没有可以被保存的图像资源');
        }

        //设置图片类型
        if (is_null($type) || empty($type)) {
            $type = $this->imgInfo['type'];
        } else {
            $type = strtolower($type);
            $this->imgSource->setImageFormat($type);
        }

        //JPEG图像设置隔行扫描
        if ('jpeg' == $type || 'jpg' == $type) {
            $this->imgSource->setImageInterlaceScheme(1);
        }

        // 设置图像质量
        $this->imgSource->setImageCompressionQuality($quality);

        //去除图像配置信息
        $this->imgSource->stripImage();

        //保存图像
        $imageFile = realpath(dirname($imageFile)) . DIRECTORY_SEPARATOR . basename($imageFile); //强制绝对路径
        if ('gif' == $type) {
            $this->imgSource->writeImages($imageFile, true);
        } else {
            $this->imgSource->writeImage($imageFile);
        }
        return true;
    }

    /**
     * 创建缩略图
     * @param int $width  宽度
     * @param int $height 高度
     * @param int $type   缩略图类型
     * @return $this
     * @throws \ImagickException
     */
    public function thumb($width, $height, $type = Image::IMAGE_THUMB_SCALE)
    {
        if (empty($this->imgSource)) {
            throw new \Exception('没有可以被缩略的图像资源');
        }

        //原图宽度和高度
        $w = $this->imgInfo['width'];
        $h = $this->imgInfo['height'];

        /* 计算缩略图生成的必要参数 */
        switch ($type) {
            /* 等比例缩放 */
            case Image::IMAGE_THUMB_SCALE:
                //原图尺寸小于缩略图尺寸则不进行缩略
                if ($w < $width && $h < $height)
                    return $this;

                //计算缩放比例
                $scale = min($width / $w, $height / $h);

                //设置缩略图的坐标及宽度和高度
                $x      = $y = 0;
                $width  = $w * $scale;
                $height = $h * $scale;
                break;

            /* 居中裁剪 */
            case Image::IMAGE_THUMB_CENTER:
                //计算缩放比例
                $scale = max($width / $w, $height / $h);

                //设置缩略图的坐标及宽度和高度
                $w = $width / $scale;
                $h = $height / $scale;
                $x = ($this->imgInfo['width'] - $w) / 2;
                $y = ($this->imgInfo['height'] - $h) / 2;
                break;

            /* 左上角裁剪 */
            case Image::IMAGE_THUMB_NORTHWEST:
                //计算缩放比例
                $scale = max($width / $w, $height / $h);

                //设置缩略图的坐标及宽度和高度
                $x = $y = 0;
                $w = $width / $scale;
                $h = $height / $scale;
                break;

            /* 右下角裁剪 */
            case Image::IMAGE_THUMB_SOUTHEAST:
                //计算缩放比例
                $scale = max($width / $w, $height / $h);

                //设置缩略图的坐标及宽度和高度
                $w = $width / $scale;
                $h = $height / $scale;
                $x = $this->imgInfo['width'] - $w;
                $y = $this->imgInfo['height'] - $h;
                break;

            /* 填充 */
            case Image::IMAGE_THUMB_FILLED:
                //计算缩放比例
                if ($w < $width && $h < $height) {
                    $scale = 1;
                } else {
                    $scale = min($width / $w, $height / $h);
                }

                //设置缩略图的坐标及宽度和高度
                $neww = $w * $scale;
                $newh = $h * $scale;
                $posx = ($width - $w * $scale) / 2;
                $posy = ($height - $h * $scale) / 2;

                //创建一张新图像
                $newimg = new \Imagick();
                $newimg->newImage($width, $height, 'white', $this->imgInfo['type']);


                if ('gif' == $this->imgInfo['type']) {
                    $imgs = $this->imgSource->coalesceImages();
                    $img  = new \Imagick();
                    $this->imgSource->destroy(); //销毁原图

                    //循环填充每一帧
                    do {
                        //填充图像
                        $image = $this->_fill($newimg, $posx, $posy, $neww, $newh, $imgs);

                        $img->addImage($image);
                        $img->setImageDelay($imgs->getImageDelay());
                        $img->setImagePage($width, $height, 0, 0);

                        $image->destroy(); //销毁临时图片

                    } while ($imgs->nextImage());

                    //压缩图片
                    $this->imgSource->destroy();
                    $this->imgSource = $img->deconstructImages();
                    $imgs->destroy(); //销毁临时图片
                    $img->destroy(); //销毁临时图片
                } else {
                    //填充图像
                    $img = $this->_fill($newimg, $posx, $posy, $neww, $newh);
                    //销毁原图
                    $this->imgSource->destroy();
                    $this->imgSource = $img;
                }

                //设置新图像属性
                $this->imgInfo['width']  = $width;
                $this->imgInfo['height'] = $height;
                return $this;

            /* 固定 */
            case Image::IMAGE_THUMB_FIXED:
                $x = $y = 0;
                break;

            default:
                throw new \Exception('不支持的缩略图裁剪类型');
        }

        /* 裁剪图像 */
        $this->crop($w, $h, $x, $y, $width, $height);
        return $this;
    }

    /**
     * 设置图片水印
     * @param string $waterFile 图片水印文件
     * @param int    $location  水印位置
     * @param int    $alpha     水印透明度 imagick无作用
     * @return $this
     * @throws \ImagickException
     */
    public function water($waterFile, $location = Image::IMAGE_WATER_SOUTHEAST, $alpha = 80)
    {
        //资源检测
        if (empty($this->imgSource)) {
            throw new \Exception('没有可以被添加水印的图像资源');
        }
        if (!is_file($waterFile)) {
            throw new \Exception('水印图像不存在');
        }

        //创建水印图像资源
        $water = new \Imagick(realpath($waterFile));
        $info  = array($water->getImageWidth(), $water->getImageHeight());

        /* 设定水印位置 */
        switch ($location) {
            /* 右下角水印 */
            case Image::IMAGE_WATER_SOUTHEAST:
                $x = $this->imgInfo['width'] - $info[0];
                $y = $this->imgInfo['height'] - $info[1];
                break;

            /* 左下角水印 */
            case Image::IMAGE_WATER_SOUTHWEST:
                $x = 0;
                $y = $this->imgInfo['height'] - $info[1];
                break;

            /* 左上角水印 */
            case Image::IMAGE_WATER_NORTHWEST:
                $x = $y = 0;
                break;

            /* 右上角水印 */
            case Image::IMAGE_WATER_NORTHEAST:
                $x = $this->imgInfo['width'] - $info[0];
                $y = 0;
                break;

            /* 居中水印 */
            case Image::IMAGE_WATER_CENTER:
                $x = ($this->imgInfo['width'] - $info[0]) / 2;
                $y = ($this->imgInfo['height'] - $info[1]) / 2;
                break;

            /* 下居中水印 */
            case Image::IMAGE_WATER_SOUTH:
                $x = ($this->imgInfo['width'] - $info[0]) / 2;
                $y = $this->imgInfo['height'] - $info[1];
                break;

            /* 右居中水印 */
            case Image::IMAGE_WATER_EAST:
                $x = $this->imgInfo['width'] - $info[0];
                $y = ($this->imgInfo['height'] - $info[1]) / 2;
                break;

            /* 上居中水印 */
            case Image::IMAGE_WATER_NORTH:
                $x = ($this->imgInfo['width'] - $info[0]) / 2;
                $y = 0;
                break;

            /* 左居中水印 */
            case Image::IMAGE_WATER_WEST:
                $x = 0;
                $y = ($this->imgInfo['height'] - $info[1]) / 2;
                break;

            default:
                /* 自定义水印坐标 */
                if (is_array($location)) {
                    list($x, $y) = $location;
                } else {
                    throw new \Exception('不支持的水印位置类型');
                }
        }

        //创建绘图资源
        $draw = new \ImagickDraw();
        $draw->composite($water->getImageCompose(), $x, $y, $info[0], $info[1], $water);

        if ('gif' == $this->imgInfo['type']) {
            $img = $this->imgSource->coalesceImages();
            $this->imgSource->destroy(); //销毁原图

            do {
                //添加水印
                $img->drawImage($draw);
            } while ($img->nextImage());

            //压缩图片
            $this->imgSource = $img->deconstructImages();
            $img->destroy(); //销毁临时图片

        } else {
            //添加水印
            $this->imgSource->drawImage($draw);
        }

        //销毁水印资源
        $draw->destroy();
        $water->destroy();
        return $this;
    }

    /**
     * 图片添加文字
     * @param string  $text     添加的文字
     * @param string  $font     字体路径
     * @param integer $size     字号
     * @param string  $color    文字颜色
     * @param integer $location 文字写入位置
     * @param integer $offset   文字相对当前位置的偏移量
     * @param integer $angle    文字倾斜角度
     * @return $this
     * @throws \Exception
     */
    public function text($text, $font, $size, $color = '#000000', $location = Image::IMAGE_WATER_SOUTHEAST, $offset = 0, $angle = 0)
    {
        //资源检测
        if (empty($this->imgSource)) {
            throw new \Exception('没有可以被写入文字的图像资源');
        }
        if (!is_file($font)) {
            throw new \Exception("不存在的字体文件：{$font}");
        }

        //获取颜色和透明度
        if (is_array($color)) {
            $color = array_map('dechex', $color);
            foreach ($color as &$value) {
                $value = str_pad($value, 2, '0', STR_PAD_LEFT);
            }
            $color = '#' . implode('', $color);
        } elseif (!is_string($color) || 0 !== strpos($color, '#')) {
            throw new \Exception('错误的颜色值');
        }
        $col = substr($color, 0, 7);
        $alp = strlen($color) == 9 ? substr($color, -2) : 0;


        //获取文字信息
        $draw = new \ImagickDraw();
        $draw->setFont(realpath($font));
        $draw->setFontSize($size);
        $draw->setFillColor($col);
        $draw->setFillOpacity(1 - hexdec($alp) / 127);
        $draw->setTextAntialias(true);
        $draw->setStrokeAntialias(true);

        $metrics = $this->imgSource->queryFontMetrics($draw, $text);

        /* 计算文字初始坐标和尺寸 */
        $x = 0;
        $y = $metrics['ascender'];
        $w = $metrics['textWidth'];
        $h = $metrics['textHeight'];

        /* 设定文字位置 */
        switch ($location) {
            /* 右下角文字 */
            case Image::IMAGE_WATER_SOUTHEAST:
                $x += $this->imgInfo['width'] - $w;
                $y += $this->imgInfo['height'] - $h;
                break;

            /* 左下角文字 */
            case Image::IMAGE_WATER_SOUTHWEST:
                $y += $this->imgInfo['height'] - $h;
                break;

            /* 左上角文字 */
            case Image::IMAGE_WATER_NORTHWEST:
                // 起始坐标即为左上角坐标，无需调整
                break;

            /* 右上角文字 */
            case Image::IMAGE_WATER_NORTHEAST:
                $x += $this->imgInfo['width'] - $w;
                break;

            /* 居中文字 */
            case Image::IMAGE_WATER_CENTER:
                $x += ($this->imgInfo['width'] - $w) / 2;
                $y += ($this->imgInfo['height'] - $h) / 2;
                break;

            /* 下居中文字 */
            case Image::IMAGE_WATER_SOUTH:
                $x += ($this->imgInfo['width'] - $w) / 2;
                $y += $this->imgInfo['height'] - $h;
                break;

            /* 右居中文字 */
            case Image::IMAGE_WATER_EAST:
                $x += $this->imgInfo['width'] - $w;
                $y += ($this->imgInfo['height'] - $h) / 2;
                break;

            /* 上居中文字 */
            case Image::IMAGE_WATER_NORTH:
                $x += ($this->imgInfo['width'] - $w) / 2;
                break;

            /* 左居中文字 */
            case Image::IMAGE_WATER_WEST:
                $y += ($this->imgInfo['height'] - $h) / 2;
                break;

            default:
                /* 自定义文字坐标 */
                if (is_array($location)) {
                    list($posx, $posy) = $location;
                    $x += $posx;
                    $y += $posy;
                } else {
                    throw new \Exception('不支持的文字位置类型');
                }
        }

        /* 设置偏移量 */
        if (is_array($offset)) {
            $offset = array_map('intval', $offset);
            list($ox, $oy) = $offset;
        } else {
            $offset = intval($offset);
            $ox     = $oy = $offset;
        }

        /* 写入文字 */
        if ('gif' == $this->imgInfo['type']) {
            $img = $this->imgSource->coalesceImages();
            $this->imgSource->destroy(); //销毁原图
            do {
                $img->annotateImage($draw, $x + $ox, $y + $oy, $angle, $text);
            } while ($img->nextImage());

            //压缩图片
            $this->imgSource = $img->deconstructImages();
            $img->destroy(); //销毁临时图片
        } else {
            $this->imgSource->annotateImage($draw, $x + $ox, $y + $oy, $angle, $text);
        }
        $draw->destroy();
        return $this;
    }

    /**
     * 返回Imagick对象
     * @return \Imagick
     * @throws \Exception
     */
    public function getImgSource()
    {
        if (empty($this->imgSource)) {
            throw new \Exception('没有可以被添加水印的图像资源');
        }
        return $this->imgSource;
    }

    /*
     * 裁剪图片
     */
    private function _crop($w, $h, $x, $y, $width, $height, $img = null)
    {
        is_null($img) && $img = $this->imgSource;

        //裁剪
        $info = $this->imgInfo;
        if ($x != 0 || $y != 0 || $w != $info['width'] || $h != $info['height']) {
            $img->cropImage($w, $h, $x, $y);
            $img->setImagePage($w, $h, 0, 0); //调整画布和图片一致
        }

        //调整大小
        if ($w != $width || $h != $height) {
            $img->sampleImage($width, $height);
        }

        //设置缓存尺寸
        $this->imgInfo['width']  = $width;
        $this->imgInfo['height'] = $height;
    }

    /*
     * 填充图片
     */
    private function _fill($newimg, $posx, $posy, $neww, $newh, $img = null)
    {
        is_null($img) && $img = $this->imgSource;
        /* 将指定图片绘入空白图片 */
        $draw = new \ImagickDraw();
        $draw->composite($img->getImageCompose(), $posx, $posy, $neww, $newh, $img);
        /* @var \Imagick */
        $image = clone $newimg;
        $image->drawImage($draw);
        $draw->destroy();
        return $image;
    }

    /**
     * 析构方法，用于销毁图像资源
     */
    public function __destruct()
    {
        empty($this->imgSource) || $this->imgSource->destroy();
    }

    /**
     * 旋转图片
     * @param int   $degrees         旋转角度
     * @param array $backgroundColor 周围背景颜色
     * @return $this
     * @throws \Exception
     */
    public function rotate($degrees, $backgroundColor = ['r' => 0, 'g' => 0, 'b' => 0, 'a' => 0])
    {
        //资源检测
        if (empty($this->imgSource)) {
            throw new \Exception('没有可以被写入文字的图像资源');
        }

        $backgroundColor = new \ImagickPixel(sprintf(
            "rgba(%d,%d,%d,%s)",
            $backgroundColor['r'] ?? 0,
            $backgroundColor['g'] ?? 0,
            $backgroundColor['b'] ?? 0,
            $backgroundColor['a'] ?? 0
        ));
        if ('gif' == $this->imgInfo['type']) {
            $img = $this->imgSource->coalesceImages();
            $this->imgSource->destroy(); //销毁原图

            do {
                $img->rotateimage($backgroundColor, $degrees);//旋转指定角度
            } while ($img->nextImage());

            //压缩图片
            $this->imgSource = $img->coalesceImages();
            $img->destroy(); //销毁临时图片
        } else {
            $this->imgSource->rotateimage($backgroundColor, $degrees);//旋转指定角度
        }
        $this->imgInfo['width']  = $this->imgSource->getImageWidth();
        $this->imgInfo['height'] = $this->imgSource->getImageHeight();
        return $this;
    }

    /**
     * 翻转图片
     * @param int $mode 翻转模式 x轴翻转 y轴翻转
     * @return $this
     * @throws \Exception
     */
    public function flip($mode = Image::IMAGE_FLIP_MODE_Y)
    {
        //资源检测
        if (empty($this->imgSource)) {
            throw new \Exception('没有可以被写入文字的图像资源');
        }
        if ('gif' == $this->imgInfo['type']) {
            $img = $this->imgSource->coalesceImages();
            $this->imgSource->destroy(); //销毁原图
            do {
                if ($mode == Image::IMAGE_FLIP_MODE_X) {
                    $img->flipImage();
                } else {
                    $img->flopImage();
                }
            } while ($img->nextImage());
            //压缩图片
            $this->imgSource = $img->deconstructImages();
            $img->destroy(); //销毁临时图片
        } else {
            if ($mode == Image::IMAGE_FLIP_MODE_X) {
                $this->imgSource->flipImage();
            } else {
                $this->imgSource->flopImage();
            }
        }
        return $this;
    }
}
