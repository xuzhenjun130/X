<?php
/**
 * 数据库模型基类
 * User: sherman
 * Date: 2017/9/15
 * Time: 14:58
 */

namespace X;


class Model implements \ArrayAccess
{
    /**
     * @var  array| false | null 查找出来的单条数据
     */
    private $_modelData;

    /**
     * @var array 验证错误信息
     */
    private $_validateError = [];
    /**
     * @var bool 是否新增数据
     */
    public $isNewRecord = false;
    /**
     * @var string 主键名称
     */
    public static $pk = 'id';

    public static function tableName()
    {
        $tableName = explode('\\',get_called_class());
        $tableName = end($tableName);
        $tableName = preg_replace('/[A-Z]/','_$0',lcfirst($tableName));
        return $tableName;
    }

    public static function select(array $fileds){
        X::app()->db->select($fileds);
        return new static();
    }

    /**
     * 查找单条数据
     * @param $id
     * @return mixed
     */
    public static function findById($id)
    {
        $data = X::app()->db->table(self::tableName())->findById($id, self::$pk);
        if(!$data){
            return false;
        }
        return new static($data,false);
    }

    /**
     * 查找多条数据
     * @param null $param
     * @param string $order
     * @param string $limit
     * @param bool $asArray 是否返回数组，而不是数组对象
     * @return array
     */
    public static function findAll($param = null, $order = "", $limit = "",$asArray=false)
    {
        $data = X::app()->db->table(self::tableName())->findAll($param, $order, $limit);
        if (!$asArray && !empty($data)) {
            foreach ($data as $k => $v) {
                $data[$k] = new static($v,false);
            }
        }
        return $data;
    }

    /**
     * 查找单条数据
     * @param null $param
     * @return static|false
     */
    public static function find($param = null)
    {
        $data = X::app()->db->table(self::tableName())->find($param);
        if (empty($data)) return false;
        return new static($data,false);
    }

    /**
     * 静态调用赋值
     * Model constructor.
     * @param array $data
     * @param bool $isNewRecord
     */
    public function __construct($data = [],$isNewRecord=true)
    {
        $this->_modelData = $data;
        $this->isNewRecord = $isNewRecord;
        if(!$isNewRecord){
            $this->afterFind();
        }
    }

    /**
     * @param $name
     * @return mixed|null
     * @throws \Exception
     */
    public function __get($name)
    {
        if (isset($this->_modelData[$name])) {
            return $this->_modelData[$name];
        } else {
            $fields = X::app()->db->getFields(self::tableName());
            foreach ($fields as $v) {
                if ($v == $name) return null;
            }
            throw new \Exception("不存在的字段：" . $name);
        }
    }

    public function __set($name, $value)
    {
        $this->_modelData[$name] = $value;
    }

    /**
     *  新增或者修改
     * @param bool $new
     * @param bool $validate 是否验证
     * @param array $validateFields 需要验证的字段
     * @return int
     */
    public function save($new = false, $validate=true,$validateFields=[])
    {
        $this->isNewRecord = $new;

        if($validate){
            if(!$this->validate($validateFields)){
                return false;
            }
        }
        if(!static::beforeSave()) return false;
        $db = X::app()->db->table(self::tableName());
        $tableFields = $db->getFields(self::tableName());
        foreach($this->_modelData as $k=>$v){
            //删除数据库没有的字段
            if(!in_array($k,$tableFields)) unset($this->_modelData[$k]);
        }
        if ($new) {
            return $db->add($this->_modelData);
        } else {
            $id = $this->{self::$pk};
            unset($this->_modelData[self::$pk]);
            return $db->update([self::$pk => $id], $this->_modelData);
        }
    }

    public function asArray()
    {
        return $this->_modelData;
    }

    /**
     * @param null $param
     * @return int
     */
    public static function total($param = null)
    {
        return X::app()->db->table(self::tableName())->total($param);
    }

    /**
     * 删除数据
     * @return int
     */
    public function delete(){
        return X::app()->db->table(self::tableName())->delete([self::$pk=>$this->{self::$pk}]);
    }

    /**
     * 验证规则
     * @return array
     */
    public function rules()
    {
        return [];
    }

    /**
     * 字段名称
     * @return array
     */
    public function attributes(){
        return [];
    }

    /**
     * 验证
     * @param array $fields 需要验证的字段，默认全部
     * @return bool
     */
    public function validate($fields = [])
    {
        if (empty($this->rules())) return true;
        foreach ($this->rules() as $rules) {
            if (is_array($rules[0])) {
                foreach ($rules[0] as $v){
                    if(!empty($fields) && !in_array($v,$fields)) continue;
                    $this->_validate($rules,$v);
                }
            } else {
                if(!empty($fields) && !in_array($rules[0],$fields)) continue;
                $this->_validate($rules,$rules[0]);
            }
        }
        $attributes = $this->attributes();
        if(!empty($this->_validateError) && !empty($attributes)){
            foreach ($this->_validateError as $k=> &$v){
                if(strpos($v,':attribute')!==false){
                    $v = isset($attributes[$k])? str_replace(':attribute',$attributes[$k],$v) : $v;
                }
            }
        }
        return empty($this->_validateError);
    }


    /**
     * 执行验证
     * @param $rules
     * @param $field
     */
    private function _validate($rules,$field){
        //已经有错误的字段则不再执行下一个相同字段验证
        if(isset($this->_validateError[$field])) return;
        $config = [];
        $validateMethod = $rules[1];
        //非数字的key 是 类的属性配置
        foreach ($rules as $k1 => $v1) {
            if (!is_numeric($k1)) {
                $config[$k1] = $v1;
            }
        }
        if( class_exists($validateMethod)){
            $config['class'] = $validateMethod;
            $validate = X::createComponents($config,false);
            $rs = $validate->run($this,$field);
        }else{
            $rs = $this->{$validateMethod}();
        }
        if ($rs) {
            $this->_validateError[$field] = $rs;
        }
    }

    /**
     * 获取验证的错误信息
     * @return array
     */
    public function getErrors(){
        return $this->_validateError;
    }

    /**
     * 设置错误信息，在具体的模型使用自定义验证方法的时候使用
     * @param $field
     * @param $msg
     */
    public function addError($field,$msg){
        $this->_validateError[$field] = $msg;
    }

    /**
     * 保存数据前需要做的操作
     * 返回false 则取消保存
     * @return bool
     */
    public function beforeSave(){
        return true;
    }

    /**
     * 查找数据后做的操作
     * @return bool
     */
    public function afterFind(){
        return true;
    }

    /**
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset)
    {

        if (isset($this->_modelData[$offset])) {
            return true;
        } else {
            $fields = X::app()->db->getFields(self::tableName());
            foreach ($fields as $v) {
                if ($v == $offset) return null;
            }
        }
        return false;
    }

    /**
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        if (isset($this->_modelData[$offset])) {
            return $this->_modelData[$offset];
        } else {
            $fields = X::app()->db->getFields(self::tableName());
            foreach ($fields as $v) {
                if ($v == $offset) return null;
            }
        }
        return false;
    }

    /**
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetSet($offset, $value)
    {
        if (isset($this->_modelData[$offset])) {
             $this->_modelData[$offset] = $value;
        } else {
            $fields = X::app()->db->getFields(self::tableName());
            foreach ($fields as $v) {
                if ($v == $offset){
                    $this->_modelData[$offset] = $value;
                }
            }
        }
    }

    /**
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     * @since 5.0.0
     * @throws \Exception
     */
    public function offsetUnset($offset)
    {
        if (isset($this->_modelData[$offset])) {
            unset($this->_modelData[$offset]);
        }else{
            throw  new \Exception('不存在的字段：'.$offset);
        }
    }
}