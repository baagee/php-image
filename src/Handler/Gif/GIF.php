<?php
/**
 * Desc:
 * User: baagee
 * Date: 2019/11/7
 * Time: 下午8:50
 */

namespace BaAGee\Image\Handler\Gif;

class GIF
{
    /**
     * GIF帧列表
     * @var array
     */
    private $frames = [];

    /**
     * 每帧等待时间列表
     * @var array
     */
    private $delays = [];

    /**
     * 构造方法，用于解码GIF图片
     * @param string $src GIF图片数据
     * @param string $mod 图片数据类型
     * @throws \Exception
     */
    public function __construct($src = null, $mod = 'url')
    {
        if (!is_null($src)) {
            if ('url' == $mod && is_file($src)) {
                $src = file_get_contents($src);
            }

            // 解码GIF图片
            try {
                $de           = new GIFDecoder($src);
                $this->frames = $de->GIFGetFrames();
                $this->delays = $de->GIFGetDelays();
            } catch (\Exception $e) {
                throw new \Exception("解码GIF图片出错");
            }
        }
    }

    /**
     * 设置或获取当前帧的数据
     * @param string $stream 二进制数据流
     * @return boolean        获取到的数据
     */
    public function image($stream = null)
    {
        if (is_null($stream)) {
            // 获取
            $current = current($this->frames);
            return false === $current ? reset($this->frames) : $current;
        } else {
            //设置
            $this->frames[key($this->frames)] = $stream;
        }
    }

    /**
     * 将当前帧移动到下一帧
     * @return string 当前帧数据
     */
    public function nextImage()
    {
        return next($this->frames);
    }

    /**
     * 编码并保存当前GIF图片
     * @param string $gifName 图片名称
     * @throws \Exception
     */
    public function save($gifName)
    {
        $gif = new GIFEncoder($this->frames, $this->delays, 0, 0, 2, 0, 0, 0, 'bin');
        file_put_contents($gifName, $gif->GetAnimation());
    }
}