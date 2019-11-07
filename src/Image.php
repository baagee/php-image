<?php
/**
 * Desc:
 * User: 01372412
 * Date: 2019/11/7
 * Time: 下午8:36
 */

namespace BaAGee\Image;

class Image
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

    public function __construct($handlerClass = '', $imageFile = '')
    {
    }

}
