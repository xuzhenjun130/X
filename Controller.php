<?php
/**
 * 控制器基类
 * User: sherman
 * Date: 2017/9/14
 * Time: 18:36
 */

namespace X;

class Controller
{
    /**
     * @var \X  框架对象
     */
    public $app;

    public function __construct()
    {
        $this->app = X::app();
    }

    /**
     * @var string 布局模板
     */
    public $layout = null;

    /**
     * 渲染视图
     * @param $fileName
     * @param array $data
     * @return string
     */
    public function render($fileName, $data = [])
    {
        $content = $this->renderParticle($fileName, $data);
        if ($this->layout) {
            ob_start();
            ob_implicit_flush(false);
            $layout = $this->getLayoutPath();
            require $layout;
            return ob_get_clean();
        } else {
            return $content;
        }
    }

    /**
     * 渲染部分视图
     * @param $fileName
     * @param array $data
     * @param string $path 视图文件的路径，默认不传，自动查找
     * @return string
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function renderParticle($fileName, $data = [], $path = "")
    {
        ob_start();
        ob_implicit_flush(false);
        $viewFile = $this->getViewPath() . '/' . $fileName . '.php';
        if ($path) {
            $viewFile = $path . '/' . $fileName . '.php';
        }
        unset($fileName);
        extract($data);
        unset($data);
        if (substr($viewFile, -9) == 'blade.php') {
            //blade 需要解析模板
            $tmp = \X::app()->runtimePath . '/views/' . md5($viewFile) . '.php';
            //当编译文件不存在，或者模板文件修改过，则生成编译文件
            if (!file_exists($tmp) || filemtime($tmp) < filemtime($viewFile) || X_DEBUG) {
                //引入模板解析类
                $parser = new TemplateParser($viewFile);
                $parser->compile($tmp);
            }
            require $tmp;
        } else {
            require($viewFile);
        }
        return ob_get_clean();
    }

    /**
     * 获取views 路径
     * @return string
     * @throws \ReflectionException
     */
    public function getViewPath()
    {
        $ref = new \ReflectionClass($this);
        $controllerId = str_replace('Controller', '', $ref->getShortName());
        $path = dirname(dirname($ref->getFileName()));
        return $path . '/views/' . lcfirst($controllerId);
    }

    /**
     * get layouts path
     * @return string
     * @throws \ReflectionException
     */
    public function getLayoutPath()
    {
        $ref = new \ReflectionClass($this);
        return dirname(dirname($ref->getFileName())) . '/views/layouts/' . $this->layout . '.php';
    }

    /**
     * 获取$_GET 参数
     *
     * @param null $name
     * @param null $defaultValue
     * @return array|mixed
     * @example 获取二维数组中的值：$this->get('Collect.category_id');
     */
    public function get($name = null, $defaultValue = null)
    {
        if ($name == null) return $_GET;
        $rs = isset($_GET[$name]) ? $_GET[$name] : $defaultValue;
        //Collect.category_id 获取 Collect[category_id]
        if ($rs == null && stripos($name, '.') > 0) {
            $arr = explode('.', $name);
            $rs2 = $_GET[$arr[0]];
            if (isset($rs2[$arr[1]])) {
                $rs = $rs2[$arr[1]];
            }
        }
        return $rs;
    }

    /**
     * 获取$_POST 参数
     * @param null $name
     * @param null $defaultValue
     * @return array|mixed
     * @example 获取二维数组中的值：$this->post('collect.category_id');
     */
    public function post($name = null, $defaultValue = null)
    {
        if ($name == null) return $_POST;
        $rs = isset($_POST[$name]) ? $_POST[$name] : $defaultValue;
        //Collect.category_id 获取 Collect[category_id]
        if ($rs == null && stripos($name, '.') > 0) {
            $arr = explode('.', $name);
            $rs2 = $_POST[$arr[0]];
            if (isset($rs2[$arr[1]])) {
                $rs = $rs2[$arr[1]];
            }
        }
        return $rs;
    }

    /**
     * 判断是否ajax 请求
     * @return bool
     */
    public function isAjax()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    }

    /**
     * http 重定向
     * @param $url
     * @param array $params
     */
    public function redirect($url, $params = [])
    {
        $to = HtmlHelper::url($url,$params);
        header("");
        header("Location:".$to);
    }

}