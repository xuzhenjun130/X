<?php
namespace X;
/**
 * X框架主文件
 * @author zhenjun_xu <412530435@qq.com>
 * Date: 14-2-16
 * Time: 下午1:33
 */
defined('X_BEGIN_TIME') or define('X_BEGIN_TIME', microtime(true)); //开始时间
defined('X_DEBUG') or define('X_DEBUG', false); //是否调试
defined('X_PATH') or define('X_PATH', dirname(__FILE__)); //框架目录

/**
 * Class X
 * @property string $basePath 程序基本路径
 * @property string $runtimePath 运行时候缓存路径
 * @property string $name 程序名称
 * @property \X\Router $router 路由
 * @property \X\Db $db 数据库
 * @property \X\CliTool $cliTool  cli模式工具
 * @property \X\User $user  用户登录
 * @property \X\Pagination $page  分页
 * @property \X\Uploader $uploader  文件上传
 */
class X extends \X\Component
{
    /**
     * @var  X
     */
    private static $_app;
    /** @var string 程序基本路径 */
    private $_basePath;
    /**
     * @var string 缓存目录
     */
    private $_runtimePath;
    /** @var string 程序名称 */
    private $_name = 'X 框架';
    /**
     * @var string 时区
     */
    private $_timeZone;

    /** @var array 框架组件 */
    private $_components = [
        'errorHandler' => ['class' => \X\ErrorHandler::class],
        'application' => ['class' => \X\Application::class],
        'router' => ['class' => \X\Router::class],
        'db' => ['class' => \X\Db::class],
        'cliTool' => ['class' => \X\CliTool::class],
        'user' => ['class' => \X\User::class],
        'page' => ['class' => \X\Pagination::class],
        'uploader' => ['class' => \X\Uploader::class],
    ];
    /**
     * @var array 组件实例化缓存
     */
    private static $_objComponents;

    /**
     * @var array 自定义全局数据
     */
    public  $params = [];

    /**
     * 初始化，若传配置参数，则调用配置方法
     * @param array $config
     */
    public function __construct($config = [])
    {
        register_shutdown_function([$this, 'fatal']);
        set_exception_handler([$this, 'exception']);
        set_error_handler([$this, 'error']);
        if (!empty($config)) {
            $this->config($config);
        }
    }

    /**
     * 致命错误处理
     * @throws \Exception
     */
    public function fatal()
    {
        $msg = error_get_last();

        if ($msg) {
            if (X_DEBUG && PHP_SAPI !='cli') {
                $error = self::getComponent('errorHandler');
                $error->displayFatal($msg);
            } else {
                echo $msg['message'];
            }
        }
    }

    /**
     * 异常处理
     */
    /**
     * @param \Exception $exception
     * @param  string $custom 自定义错误信息
     * @throws \Exception
     */
    public function exception($exception,$custom='')
    {
        if (X_DEBUG && PHP_SAPI !='cli') {
            /**
             * @var $error \X\ErrorHandler
             */
            $error = self::getComponent('errorHandler');
            $error->displayException($exception,$custom);
        } else if(PHP_SAPI =='cli'){
            echo $exception->getMessage()."\r\n".$exception->getTraceAsString();
        }else {
            echo '<fieldset>';
            echo '<legend>error</legend>';
            echo '<p>' . $exception->getMessage() . '</p>';
            echo '</fieldset>';
        }
    }

    /**
     * 错误处理
     * @param $code
     * @param $message
     * @param $file
     * @param $line
     * @throws \Exception
     */
    public function error($code, $message, $file, $line)
    {
        if ($code && error_reporting()) {
            if (X_DEBUG && PHP_SAPI !='cli') {
                $error = self::getComponent('errorHandler');
                $error->displayError($code, $message, $file, $line);
            } else if(PHP_SAPI =='cli'){
                echo $message;
            }else {
                $errorCallBack = \X\Router::$error_callback;
                if ($errorCallBack) {
                    call_user_func($errorCallBack, ['message' => $message, 'file' => $file, 'line' => $line]);
                    exit;
                } else {
                    echo '<fieldset style="background: #F3F3F3">';
                    echo '<legend style="color:red;">error[' . $code . ']</legend>';
                    echo '<p>' . $message . '  (' . $file . '[' . $line . '])</p>';
                    echo '</fieldset>';

                    echo '<fieldset style="background: #ddd;">';
                    echo '<legend style="color:#800000">trace</legend>';
                    echo '<pre>';
                    debug_print_backtrace();
                    echo '</pre>';
                    echo '</fieldset>';
                }
            }
        }
    }

    /**
     * 引入程序自定义配置
     * @param array $config
     */
    public function config($config = array())
    {
        foreach ($config as $k => $v) {
            $this->$k = $v;
        }
    }

    /**
     * 启动程序
     * @param  X $app
     * @throws \Exception
     */
    public function run()
    {
        session_start();
        self::$_app = $this;
        if (empty($this->_runtimePath)) {
            $this->_runtimePath = $this->_basePath . '/runtime';
        }
        $application = $this->getComponent('application');
        $application->runController();
    }

    /**
     * 获取本类
     * @return X
     */
    public static function app()
    {
        return self::$_app;
    }

    /**
     * 获取程序基本路径
     * @return string
     */
    public function getBasePath()
    {
        return $this->_basePath;
    }

    /**
     * 设置程序基本路径
     * @param string $path
     * @throws \Exception
     */
    public function setBasePath($path)
    {
        if (($this->_basePath = realpath($path)) === false || !is_dir($this->_basePath)) {
            throw new \Exception('程序基本路径' . $this->_basePath . '设置失败');
        }
    }

    /**
     * 设置缓存目录
     * @return string 缓存目录
     */
    public function getRunTimePath()
    {
        return $this->_runtimePath;
    }

    public function setRunTimePath($path)
    {
        $this->_runtimePath = $path;
    }

    /**
     * 设置程序名称
     * @param string $name
     * @return string
     */
    public function setName($name)
    {
        return $this->_name = $name;
    }

    /**
     * 获取程序名称
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * 设置时区
     * @param string $timeZone
     */
    public function setTimeZone($timeZone)
    {
        $this->_timeZone = $timeZone;
        date_default_timezone_set($timeZone);
    }

    /**
     * 设置组件
     * @param $components
     */
    public function setComponents($components)
    {
        $this->_components = array_merge($this->_components, $components);
    }

    /**
     * 获取组件
     * @param string $component 组件名称
     * @return mixed
     * @throws \Exception
     */
    public function getComponent($component)
    {
        if (isset($this->_components[$component])) {
            return self::createComponents($this->_components[$component]);
        } else {
            throw new \Exception($component . '组件不存在');
        }
    }

    /**
     * 实例化组件
     * @param array| string $config
     * @param  bool $cache
     * @return mixed
     * @throws \Exception
     */
    public static function createComponents($config,$cache = true)
    {
        if (is_string($config)) {
            $className = $config;
            $config = array();
        } else {
            if (!isset($config['class'])) {
                throw new \Exception("组件必须指定class");
            }
            $className = $config['class'];
            unset($config['class']);
        }
        if ($cache && isset(self::$_objComponents[$className])) {
            $obj = self::$_objComponents[$className];
        } else {
            if ($n = func_num_args() > 1) {
                $args = func_get_args();
                switch ($n) {
                    case 2:
                        $obj = new $className($args[1]);
                        break;
                    case 3:
                        $obj = new $className($args[1], $args[2]);
                        break;
                    case 4:
                        $obj = new $className($args[1], $args[2], $args[2]);
                        break;
                    default:
                        unset($args[0]);
                        $class = new \ReflectionClass($className);
                        $obj = call_user_func_array(array($class, 'newInstance'), $args);
                }
            } else {
                $obj = new $className;
            }
            self::$_objComponents[$className] = $obj;
            foreach ($config as $k => $v) {
                $obj->$k = $v;
            }
            if (method_exists($obj, 'init')) {
                $obj->init();
            }
        }

        return $obj;
    }
}
