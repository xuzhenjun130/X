<?php
/**
 * 代码调试用的快捷函数
 * @author zhenjun_xu <412530435@qq.com>
 * Date: 14-2-27
 * Time: 上午10:19
 */
/**
 * var_export 调试代码，高亮显示
 * @param $var
 */
function p($var){
    $str = highlight_string("<?php\n".var_export($var,true),true);
    $str = preg_replace('/&lt;\\?php<br \\/>/','',$str,1);
    echo $str;
}

/**
 * var_export 调试代码，高亮显示,并结束程序
 * @param $var
 */
function pr($var){
    $str = highlight_string("<?php\n".var_export($var,true),true);
    $str = preg_replace('/&lt;\\?php<br \\/>/','',$str,1);
    exit($str);
}