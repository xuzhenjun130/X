<?php
/**
 * 正则表达式验证
 * User: sherman
 * Date: 2017/10/12
 * Time: 10:18
 */

namespace X\validate;


class Regular extends ValidateAbstract
{

    /**
     * @var string 正则表达式
     */
    public $pattern;

    /**
     * @var bool 是否反转验证逻辑
     */
    public $not = false;

    public $message = ':attribute 格式不正确';

    public function run($model, $field)
    {
        $value = $model->$field;
        $valid = !is_array($value) &&
            (!$this->not && preg_match($this->pattern, $value)
                || $this->not && !preg_match($this->pattern, $value));
        return $valid ? false : $this->message;
    }
}