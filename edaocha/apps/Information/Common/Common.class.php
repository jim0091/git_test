<?php

namespace Apps\Information;

defined('SITE_PATH') || exit('Forbidden');
/**
 * 公用库
 *
 * @package Apps\Information\Common
 * @author Seven Du <lovevipdsw@vip.qq.com>
 **/
class Common
{
    /**
     * 文件加载列表
     *
     * @var array
     **/
    protected static $_includes = array();

    /**
     * 加载的文件对象列表
     *
     * @var array
     **/
    protected static $_loads = array();

    /**
     * 加载文件
     *
     * @param  string $filePath 文件地址
     * @return bool
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public static function import($filePath)
    {
        $filePath = str_replace('\\', DIRECTORY_SEPARATOR, $filePath);
        $filePath = str_replace('/', DIRECTORY_SEPARATOR, $filePath);

        if (in_array($filePath, self::$_includes)) {
            return true;
        } elseif (file_exists($filePath)) {
            array_push(self::$_includes, $filePath);

            return include $filePath;
        }

        return false;
    }

    /**
     * 自适应加载
     * 目前只有模型有需求
     *
     * @param  string $namespace 命名空间
     * @return bool
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public static function autoLoader($namespace)
    {
        if (strpos($namespace, '\\')) {
            $name = explode('\\', $namespace);
            $name = array_pop($name);
        }

        return self::import(APPS_PATH.'/Information/Lib/Model/'.$name.'Model.class.php');
    }

    /**
     * 根据命名空间加载并返回实例
     *
     * @param  string $namespace 命名空间
     * @return object
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public static function load($namespace)
    {
        if (self::$_loads[$namespace]) {
            return self::$_loads[$namespace];
        } elseif (self::autoLoader($namespace)) {
            self::$_loads[$namespace] = new $namespace;

            return self::$_loads[$namespace];
        }

        return false;
    }

    /**
     * 设置头部文本输出类型
     *
     * @param string $type    设置文本类型
     * @param string $charset 设置字符集
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function setHeader($type = 'text/html', $charset = 'utf-8')
    {
        header('Content-type:'.$type.';charset='.$charset);
        header('Cache-control: private');
    }

    /**
     * 获取表单数据
     *
     * @param string|array $name 数组或者键名，不传则返回所有数据
     * @param string 获取的类型 默认只有request|get|post三种
     * @return data 返回的数据
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public static function getInput($name = null, $method = 'request')
    {
        $method = strtolower($method);
        if (is_array($name)) {
            foreach ($name as $key => $value) {
                $name[$key] = self::getInput($value, $method);
            }

            return $name;
        } elseif (!$name && $method == 'get') {
            return $_GET;
        } elseif (!$name && $method == 'post') {
            return $_POST;
        } elseif (!$name && $method == 'request') {
            return $_REQUEST;
        } elseif ($method == 'get' && isset($_GET[$name])) {
            return $_GET[$name];
        } elseif ($method == 'post' && isset($_POST[$name])) {
            return $_POST[$name];
        } elseif (isset($_REQUEST[$name])) {
            return $_REQUEST[$name];
        }

        return null;
    }

    /**
     * 求取字符串位数（非字节），以UTF-8编码长度计算
     *
     * @param  string $string 需要被计算位数的字符串
     * @return int
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public static function strlen($string)
    {
        $length = strlen($string);
        $index = $num = 0;
        while ($index < $length) {
            $str = $string[$index];
            if ($str < "\xC0") {
                $index += 1;
            } elseif ($str < "\xE0") {
                $index += 2;
            } elseif ($str < "\xF0") {
                $index += 3;
            } elseif ($str < "\xF8") {
                $index += 4;
            } elseif ($str < "\xFC") {
                $index += 5;
            } else {
                $index += 6;
            }
            $num += 1;
        }

        return $num;
    }

    /**
     * 更具表达式返回内容
     *
     * @param  PHP condition $condition PHP条件
     * @param  unknow        $yes       表达式成立返回内容
     * @param  unknow        $no        表达式不成立返回内容
     * @return unkonw
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public static function hasEcho($condition, $yes = '', $no = '')
    {
        return $condition ? $yes : $no;
    }


    /**
     * @param $content
     * @return mixed
     *
     * 清除外部链接
     */
    public static function clear_link($content){
        return ereg_replace("<a [^>]*>|<\/a>","",$content);
    }

    /**
     * @param $article_content
     * @param $thumb_no
     * @param string $ext
     * @return string
     *
     * 设置内容第几张图片做为封面图
     */
    public static function save_remote($article_content,$thumb_no,$ext = 'jpg|jpeg|gif|png|bmp'){
        if(preg_match_all("/src=([\"|']?)([^ \"'>]+\.($ext))\\1/i", $article_content, $matches)){
            return $matches[2][$thumb_no];
        }else{
            return '';
        }
    }


    /**
     * @param $article_content
     * @param $introduce_length
     * @return string
     *
     * 截取资讯前几个字符到摘要
     */
    public static function subArticle($article_content,$introduce_length){
        if($introduce_length) {
            $intro = trim(strip_tags($article_content));
            $intro = preg_replace("/&([a-z]{1,});/", '', $intro);
            $intro = str_replace(array("\r", "\n", "\t", '  '), array('', '', '', ''), $intro);
            return self::dsubstr($intro, $introduce_length);
        } else {
            return '';
        }
    }

    function dsubstr($string, $length, $suffix = ''){
        $strlen = strlen($string);
        if($strlen <= $length) return $string;
        $string = str_replace(array('&quot;', '&lt;', '&gt;'), array('"', '<', '>'), $string);
        $length = $length - strlen($suffix);
        $n = $tn = $noc = 0;
        while($n < $strlen)	{
            $t = ord($string{$n});
            if($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
                $tn = 1; $n++; $noc++;
            } elseif(194 <= $t && $t <= 223) {
                $tn = 2; $n += 2; $noc += 2;
            } elseif(224 <= $t && $t <= 239) {
                $tn = 3; $n += 3; $noc += 2;
            } elseif(240 <= $t && $t <= 247) {
                $tn = 4; $n += 4; $noc += 2;
            } elseif(248 <= $t && $t <= 251) {
                $tn = 5; $n += 5; $noc += 2;
            } elseif($t == 252 || $t == 253) {
                $tn = 6; $n += 6; $noc += 2;
            } else {
                $n++;
            }
            if($noc >= $length) break;
        }
        if($noc > $length) $n -= $tn;
        $str = substr($string, 0, $n);
        $str = str_replace(array('"', '<', '>'), array('&quot;', '&lt;', '&gt;'), $str);
        return addslashes($str);
    }
} // END class Common
// class_alias('Apps\Information\Common', 'Common');
