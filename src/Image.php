<?php
/**
 * Desc: 图片处理
 * User: baagee
 * Date: 2019/11/7
 * Time: 下午8:36
 */

namespace BaAGee\Image;

use BaAGee\Image\Base\ImgHandlerInterface;
use BaAGee\Image\Handler\Gd;

final class Image
{
    // 缩略图相关常量定义
    const IMAGE_THUMB_SCALE     = 1000; //常量，标识缩略图等比例缩放类型
    const IMAGE_THUMB_FILLED    = 2000; //常量，标识缩略图缩放后填充类型
    const IMAGE_THUMB_CENTER    = 3000; //常量，标识缩略图居中裁剪类型
    const IMAGE_THUMB_NORTHWEST = 4000; //常量，标识缩略图左上角裁剪类型
    const IMAGE_THUMB_SOUTHEAST = 5000; //常量，标识缩略图右下角裁剪类型
    const IMAGE_THUMB_FIXED     = 6000; //常量，标识缩略图固定尺寸缩放类型

    // 水印相关常量定义
    const IMAGE_WATER_NORTHWEST = 100000; //常量，标识左上角水印
    const IMAGE_WATER_NORTH     = 200000; //常量，标识上居中水印
    const IMAGE_WATER_NORTHEAST = 300000; //常量，标识右上角水印
    const IMAGE_WATER_WEST      = 400000; //常量，标识左居中水印
    const IMAGE_WATER_CENTER    = 500000; //常量，标识居中水印
    const IMAGE_WATER_EAST      = 600000; //常量，标识右居中水印
    const IMAGE_WATER_SOUTHWEST = 700000; //常量，标识左下角水印
    const IMAGE_WATER_SOUTH     = 800000; //常量，标识下居中水印
    const IMAGE_WATER_SOUTHEAST = 900000; //常量，标识右下角水印

    /**
     * @var ImgHandlerInterface
     */
    private $imgHandler;

    /**
     * Image constructor.
     * @param string $handlerClass 图片处理handler Gd or Imagick or other
     * @param string $imageFile    要处理的图片文件
     * @throws \Exception
     */
    public function __construct($handlerClass = Gd::class, $imageFile = '')
    {
        // if (!($handlerClass instanceof ImgHandlerInterface)) {
        //     throw new \Exception(sprintf('%s 没有实现 %s', $handlerClass, ImgHandlerInterface::class));
        // }
        $this->imgHandler = new $handlerClass($imageFile);
    }

    /**
     * 打开一幅图像
     * @param string $imageFile 图片路径
     * @return $this          当前图片处理库对象
     */
    public function open($imageFile)
    {
        $this->imgHandler->open($imageFile);
        return $this;
    }

    /**
     * 保存图片
     * @param string  $imageFile 图片保存名称
     * @param string  $type      图片类型
     * @param integer $quality   图像质量
     * @param boolean $interlace 是否对JPEG类型图片设置隔行扫描
     * @return $this             当前图片处理库对象
     */
    public function save($imageFile, $type = null, $quality = 80, $interlace = true)
    {
        $this->imgHandler->save($imageFile, $type, $quality, $interlace);
        return $this;
    }

    /**
     * 返回图片宽度
     * @return integer 图片宽度
     */
    public function width()
    {
        return $this->imgHandler->width();
    }

    /**
     * 返回图片高度
     * @return integer 图片高度
     */
    public function height()
    {
        return $this->imgHandler->height();
    }

    /**
     * 返回图像类型
     * @return string 图片类型
     */
    public function type()
    {
        return $this->imgHandler->type();
    }

    /**
     * 返回图像MIME类型
     * @return string 图像MIME类型
     */
    public function mime()
    {
        return $this->imgHandler->mime();
    }

    /**
     * 返回图像尺寸数组 0 - 图片宽度，1 - 图片高度
     * @return array 图片尺寸
     */
    public function size()
    {
        return $this->imgHandler->size();
    }

    /**
     * 裁剪图片
     * @param integer $w      裁剪区域宽度
     * @param integer $h      裁剪区域高度
     * @param integer $x      裁剪区域x坐标
     * @param integer $y      裁剪区域y坐标
     * @param integer $width  图片保存宽度
     * @param integer $height 图片保存高度
     * @return $this          当前图片处理库对象
     */
    public function crop($w, $h, $x = 0, $y = 0, $width = null, $height = null)
    {
        $this->imgHandler->crop($w, $h, $x, $y, $width, $height);
        return $this;
    }

    /**
     * 生成缩略图
     * @param integer $width  缩略图最大宽度
     * @param integer $height 缩略图最大高度
     * @param integer $type   缩略图裁剪类型
     * @return $this          当前图片处理库对象
     */
    public function thumb($width, $height, $type = self::IMAGE_THUMB_SCALE)
    {
        $this->imgHandler->thumb($width, $height, $type);
        return $this;
    }

    /**
     * 添加水印
     * @param string  $source 水印图片路径
     * @param integer $locate 水印位置
     * @param integer $alpha  水印透明度
     * @return $this          当前图片处理库对象
     */
    public function water($source, $locate = self::IMAGE_WATER_SOUTHEAST, $alpha = 80)
    {
        $this->imgHandler->water($source, $locate, $alpha);
        return $this;
    }

    /**
     * 图像添加文字
     * @param string  $text   添加的文字
     * @param string  $font   字体路径
     * @param integer $size   字号
     * @param string  $color  文字颜色
     * @param integer $locate 文字写入位置
     * @param integer $offset 文字相对当前位置的偏移量
     * @param integer $angle  文字倾斜角度
     * @return $this
     */
    public function text($text, $font, $size, $color = '#000000', $locate = self::IMAGE_WATER_SOUTHEAST, $offset = 0, $angle = 0)
    {
        $this->imgHandler->text($text, $font, $size, $color, $locate, $offset, $angle);
        return $this;
    }
}
