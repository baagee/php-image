<?php
/**
 * Desc: 图片处理 DG扩展封装
 * User: baagee
 * Date: 2019/11/7
 * Time: 下午8:47
 */

namespace BaAGee\Image\Handler;

use BaAGee\Image\Base\ImgHandlerInterface;
use BaAGee\Image\Handler\Gif\GIF;
use BaAGee\Image\Image;

/**
 * Class Gd
 * @package BaAGee\Image\Handler
 */
class Gd implements ImgHandlerInterface
{
    /**
     * 图像资源对象
     * @var resource
     */
    private $imgSource;
    /**
     * @var GIF
     */
    private $gifSource;

    /**
     * 图像信息，包括width,height,type,mime,size
     * @var array
     */
    private $imgInfo;

    /**
     * Gd constructor.
     * @param string $imageFile
     * @throws \Exception
     */
    public function __construct($imageFile = '')
    {
        if (!empty($imageFile) && is_file($imageFile)) {
            $this->open($imageFile);
        }
    }

    /**
     * 打开一个图片
     * @param string $imageFile 图片路径
     * @throws \Exception
     */
    public function open($imageFile)
    {
        //检测图像文件
        if (!is_file($imageFile)) {
            throw new \Exception('不存在的图像文件');
        }

        //获取图像信息
        $info = getimagesize($imageFile);

        //检测图像合法性
        if (false === $info || (IMAGETYPE_GIF === $info[2] && empty($info['bits']))) {
            throw new \Exception('非法图像文件');
        }

        //设置图像信息
        $this->imgInfo = array(
            'width'  => $info[0],
            'height' => $info[1],
            'type'   => image_type_to_extension($info[2], false),
            'mime'   => $info['mime'],
        );

        //销毁已存在的图像
        empty($this->imgSource) || imagedestroy($this->imgSource);

        //打开图像
        if ('gif' == $this->imgInfo['type']) {
            $class           = GIF::class;
            $this->gifSource = new $class($imageFile);
            $this->imgSource = imagecreatefromstring($this->gifSource->image());
        } else {
            $fun             = "imagecreatefrom{$this->imgInfo['type']}";
            $this->imgSource = $fun($imageFile);
        }
    }

    /**
     * 获取宽度
     * @return mixed
     * @throws \Exception
     */
    public function width()
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
    public function height()
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
    public function type()
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
    public function mime()
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
    public function size()
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

        do {
            //创建新图像
            $img = imagecreatetruecolor($width, $height);
            // 调整默认颜色
            $color = imagecolorallocate($img, 255, 255, 255);
            imagefill($img, 0, 0, $color);

            //裁剪
            imagecopyresampled($img, $this->imgSource, 0, 0, $x, $y, $width, $height, $w, $h);
            imagedestroy($this->imgSource); //销毁原图

            //设置新图像
            $this->imgSource = $img;
        } while (!empty($this->gifSource) && $this->gifNext());

        $this->imgInfo['width']  = $width;
        $this->imgInfo['height'] = $height;
        return $this;
    }

    /**
     * 保存图片
     * @param string  $imageFile 保存文件路径
     * @param string  $type      图像类型
     * @param integer $quality   图像质量
     * @param boolean $interlace 是否对JPEG类型图像设置隔行扫描
     * @return bool
     * @throws \Exception
     */
    public function save($imageFile, $type = null, $quality = 80, $interlace = true)
    {
        if (empty($this->imgSource)) {
            throw new \Exception('没有可以被保存的图像资源');
        }

        //自动获取图像类型
        if (is_null($type) || empty($type)) {
            $type = $this->imgInfo['type'];
        } else {
            $type = strtolower($type);
        }
        //保存图像
        if ('jpeg' == $type || 'jpg' == $type) {
            //JPEG图像设置隔行扫描
            imageinterlace($this->imgSource, $interlace);
            imagejpeg($this->imgSource, $imageFile, $quality);
        } elseif ('gif' == $type && !empty($this->gifSource)) {
            $this->gifSource->save($imageFile);
        } else {
            $fun = 'image' . $type;
            $fun($this->imgSource, $imageFile);
        }
        return true;
    }

    /**
     * 获取gif图下一帧
     * @return bool|string
     */
    private function gifNext()
    {
        ob_start();
        ob_implicit_flush(0);
        imagegif($this->imgSource);
        $img = ob_get_clean();

        $this->gifSource->image($img);
        $next = $this->gifSource->nextImage();

        if ($next) {
            $this->imgSource = imagecreatefromstring($next);
            return $next;
        } else {
            $this->imgSource = imagecreatefromstring($this->gifSource->image());
            return false;
        }
    }

    /**
     * 设置缩略图
     * @param int $width  宽度
     * @param int $height 高度
     * @param int $type   缩略图类型
     * @return $this
     * @throws \Exception
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
                if ($w < $width && $h < $height) {
                    throw new \Exception('原图尺寸小于缩略图尺寸不进行缩略');
                }

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

                do {
                    //创建新图像
                    $img = imagecreatetruecolor($width, $height);
                    // 调整默认颜色
                    $color = imagecolorallocate($img, 255, 255, 255);
                    imagefill($img, 0, 0, $color);

                    //裁剪
                    imagecopyresampled($img, $this->imgSource, $posx, $posy, 0, 0, $neww, $newh, $w, $h);
                    imagedestroy($this->imgSource); //销毁原图
                    $this->imgSource = $img;
                } while (!empty($this->gifSource) && $this->gifNext());

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
     * @param int    $alpha     水印透明度
     * @return $this
     * @throws \Exception
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

        //获取水印图像信息
        $info = getimagesize($waterFile);
        if (false === $info || (IMAGETYPE_GIF === $info[2] && empty($info['bits']))) {
            throw new \Exception('非法水印文件');
        }

        //创建水印图像资源
        $fun   = 'imagecreatefrom' . image_type_to_extension($info[2], false);
        $water = $fun($waterFile);

        //设定水印图像的混色模式
        imagealphablending($water, true);

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

        do {
            //添加水印
            $src = imagecreatetruecolor($info[0], $info[1]);
            // 调整默认颜色
            $color = imagecolorallocate($src, 255, 255, 255);
            imagefill($src, 0, 0, $color);

            imagecopy($src, $this->imgSource, 0, 0, $x, $y, $info[0], $info[1]);
            imagecopy($src, $water, 0, 0, 0, 0, $info[0], $info[1]);
            imagecopymerge($this->imgSource, $src, $x, $y, 0, 0, $info[0], $info[1], $alpha);

            //销毁零时图片资源
            imagedestroy($src);
        } while (!empty($this->gifSource) && $this->gifNext());

        //销毁水印资源
        imagedestroy($water);
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
    public function text($text, $font, $size, $color = '#00000000', $location = Image::IMAGE_WATER_SOUTHEAST, $offset = 0, $angle = 0)
    {
        //资源检测
        if (empty($this->imgSource)) {
            throw new \Exception('没有可以被写入文字的图像资源');
        }
        if (!is_file($font)) {
            throw new \Exception("不存在的字体文件：{$font}");
        }

        //获取文字信息
        $info = \imagettfbbox($size, $angle, $font, $text);
        $minx = min($info[0], $info[2], $info[4], $info[6]);
        $maxx = max($info[0], $info[2], $info[4], $info[6]);
        $miny = min($info[1], $info[3], $info[5], $info[7]);
        $maxy = max($info[1], $info[3], $info[5], $info[7]);

        /* 计算文字初始坐标和尺寸 */
        $x = $minx;
        $y = abs($miny);
        $w = $maxx - $minx;
        $h = $maxy - $miny;

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
                    E('不支持的文字位置类型');
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

        /* 设置颜色 */
        if (is_string($color) && 0 === strpos($color, '#')) {
            $color = str_split(substr($color, 1), 2);
            $color = array_map('hexdec', $color);
            if (empty($color[3]) || $color[3] > 127) {
                $color[3] = 0;
            }
        } elseif (!is_array($color)) {
            throw new \Exception('错误的颜色值');
        }

        do {
            /* 写入文字 */
            $col = imagecolorallocatealpha($this->imgSource, $color[0], $color[1], $color[2], $color[3]);
            imagettftext($this->imgSource, $size, $angle, $x + $ox, $y + $oy, $col, $font, $text);
        } while (!empty($this->gifSource) && $this->gifNext());
        return $this;
    }

    /**
     * 析构方法，用于销毁图像资源
     */
    public function __destruct()
    {
        empty($this->imgSource) || imagedestroy($this->imgSource);
    }
}
