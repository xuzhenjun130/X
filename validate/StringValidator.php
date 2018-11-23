<?php
/**
 * 字符串 验证器
 * User: sherman
 * Date: 2017/10/11
 * Time: 18:31
 */

namespace X\validate;


class StringValidator extends ValidateAbstract
{
    /**
     * @var int 最小长度
     */
    public $min;
    /**
     * @var int 最大长度
     */
    public $max;

    /**
     * @var int 固定长度
     */
    public $length;

    public $message = ":attribute 长度不正确";

    public function run($model,$field='')
    {
        $value = $model->$field;
        $length = mb_strlen($value);
        if ($this->min !== null && $length < $this->min) {
            return $this->message.'。不能小于最小长度:'.$this->min;
        }
        if ($this->max !== null && $length > $this->max) {
            return $this->message.'。不能大于最大长度:'.$this->max;
        }
        if ($this->length !== null && $length !== $this->length) {
            return $this->message.'必须等于:'.$this->length;
        }
        return false;
    }
}