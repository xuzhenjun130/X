<?php

namespace X;

class Router
{


    /**
     *
     * @var string 路由GET参数名称
     */
    public static $name = 'r';
    /**
     *
     * @var array 路由信息
     */
    public static $routes = [];

    public static $error_callback;

    /**
     * get
     * @param mixed $route
     * @param mixed $callBack
     * @return void
     */
    public static function get($route, $callBack)
    {
        array_push(self::$routes, [$route => ['method' => 'get', 'callBack' => $callBack]]);
    }

    /**
     * post
     * @param mixed $route
     * @param mixed $callBack
     * @return void
     */
    public static function post($route, $callBack)
    {
        array_push(self::$routes, [$route => ['method' => 'post', 'callBack' => $callBack]]);
    }

    /**
     * 任何请求方式都可以
     * @param $route
     * @param $callBack
     */
    public static function any($route,$callBack){
        array_push(self::$routes, [$route => ['method' => 'any', 'callBack' => $callBack]]);
    }

    /**
     *  Defines callback if route is not found
     * @param $callback
     */
    public static function error($callback)
    {
        self::$error_callback = $callback;
    }

    /**
     * 执行路由
     * @return bool|mixed
     * @throws \Exception
     * @throws \ReflectionException
     */
    public static function dispatch()
    {
        $currentRoute = isset($_GET[self::$name]) ? $_GET[self::$name] : '/';
        $found_route = false;
        $method = strtolower($_SERVER['REQUEST_METHOD']);
        foreach (self::$routes as $v) {
            if (!empty($v)) {
                foreach ($v as $k2 => $v2) {
                    if ($k2 == $currentRoute && ($v2['method'] == $method || $v2['method']=='any')) {
                        $found_route = true;
                        $_GET['r'] = $currentRoute;
                        // Call closure
                        $refFunc = new \ReflectionFunction($v2['callBack']);
                        $params = [];
                        foreach ($refFunc->getParameters() as $v3) {
                            if (isset($_REQUEST[$v3->name])) {
                                $params[$v3->name] = $_REQUEST[$v3->name];
                            }else{
                                //参数是否有默认值
                                if($v3->isDefaultValueAvailable()){
                                    $params[$v3->name] = $v3->getDefaultValue();
                                }else{
                                    //参数是否是个对象
                                    $classRef = $v3->getClass();
                                    if($classRef){
                                        $classProperties = $classRef->getProperties();
                                        /**
                                         * @var $class Model
                                         */
                                        $class = $classRef->newInstance();
                                        if($classProperties){
                                           foreach($classProperties as $v4){
                                               $propertyName  = $v4->name;
                                               if(isset($_REQUEST[$propertyName])){
                                                   $class->$propertyName = $_REQUEST[$propertyName];
                                               }
                                           }
                                        }
                                        //验证参数
                                        if($classRef->hasMethod('validate')){
                                            if(!$class->validate()){
                                                throw new \Exception(json_encode($class->getErrors(),JSON_UNESCAPED_UNICODE));
                                            }
                                        }
                                        $params = [$class];
                                    }else{
                                        throw new \Exception('参数不存在：'.$v3->name,400);
                                    }
                                }
                            }
                        }
                        //将post or get 参数传入
                        if (!empty($params)) {
                            return call_user_func_array($v2['callBack'], $params);
                        } else {
                            return call_user_func($v2['callBack']);
                        }
                       break;
                    }
                }
            }
        }

        if ($found_route == false) {
            if (!self::$error_callback) {
                header($_SERVER['SERVER_PROTOCOL'] . " 404 Not Found");
                //echo '404';
            } else {
                return call_user_func(self::$error_callback, ['message' => '404 Not Found']);
            }

        }
        return false;

    }
}
