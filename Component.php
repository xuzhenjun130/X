<?php
/**
 * 框架组件基本类，所有的组件都是继承这个类
 * @author zhenjun_xu <412530435@qq.com>
 * Date: 14-2-16
 * Time: 下午2:46
 */
namespace X;

/**
 * @method getComponent($name) \X
 */
class Component
{

    /**
     * 获取属性
     * @param $name
     * @return mixed
     * @throws \Exception
     */
    public function __get($name)
    {
        $getter = 'get' . $name;
        if (method_exists($this, $getter)) {
            return $this->$getter();
        } else {
            if(get_class($this)=="X"){
                return $this->getComponent($name);
            }
            throw new \Exception('class:' . get_class($this) . '找不到[' . $name . ']属性');
        }
    }

    /**
     * 设置属性
     * @param $name
     * @param $value
     * @return mixed
     * @throws \Exception
     */
    public function __set($name, $value)
    {
        $setter = 'set' . $name;
        if (method_exists($this, $setter)) {
            return $this->$setter($value);
        } else {
            throw new \Exception('class:' . get_class($this) . '找不到[' . $name . ']属性');
        }
    }

}