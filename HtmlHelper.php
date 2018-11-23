<?php
/**
 *  html 帮助类
 * User: sherman
 * Date: 2017/9/18
 * Time: 17:35
 */

namespace X;


class HtmlHelper
{


    /**
     * 生成url 链接
     * @param string $route
     * @param array $params
     * @return string
     */

    public static function url($route = '', Array $params = [])
    {
        $dir = dirname($_SERVER['PHP_SELF']);
        $dir = $dir == DIRECTORY_SEPARATOR ? '':$dir;
        $scheme = isset($_SERVER['HTTPS']) && 'on' === $_SERVER['HTTPS'] ? 'https':'http';
        $baseUrl = $scheme.'://'.$_SERVER['HTTP_HOST'].$dir.'/?r='.$route;
        return empty($params) ? $baseUrl : $baseUrl . '&' . http_build_query($params);
    }

    /**
     * 生成 a 链接
     * @param string $text
     * @param string $route
     * @param array $params
     * @param array $options
     * @return string
     */
    public static function a($text = '', $route = '', Array $params = [], $options = [])
    {
        $optionsHtml = '';
        array_walk($options, function ($value, $key) use (&$optionsHtml) {
            $optionsHtml .= $key . '="' . $value . '" ';
        });
        return '<a href="' . self::url($route, $params) . '" ' . $optionsHtml . ' >' . $text . '</a>';
    }
}