<?php
/**
 * Created by PhpStorm.
 * User: sherman
 * Date: 2017/10/11
 * Time: 18:00
 */

namespace X\validate;


class Compare extends ValidateAbstract
{
    /**
     * 对比字段
     * @var string the name of the attribute to be compared with. When both this property
     * and [[compareValue]] are set, the latter takes precedence. If neither is set,
     * it assumes the comparison is against another attribute whose name is formed by
     * appending '_repeat' to the attribute being validated. For example, if 'password' is
     * being validated, then the attribute to be compared would be 'password_repeat'.
     * @see compareValue
     */
    public $compareAttribute;
    /**
     * 对比数据
     * @var mixed the constant value to be compared with. When both this property
     * and [[compareAttribute]] are set, this property takes precedence.
     * @see compareAttribute
     */
    public $compareValue;
    /**
     * @var string  数据类型
     *
     * - string: the values are being compared as strings. No conversion will be done before comparison.
     * - number: the values are being compared as numbers. String values will be converted into numbers before comparison.
     */
    public $type = 'string';
    /**
     * @var string 对比运算符
     *
     * - `==`: check if two values are equal. The comparison is done is non-strict mode.
     * - `===`: check if two values are equal. The comparison is done is strict mode.
     * - `!=`: check if two values are NOT equal. The comparison is done is non-strict mode.
     * - `!==`: check if two values are NOT equal. The comparison is done is strict mode.
     * - `>`: check if value being validated is greater than the value being compared with.
     * - `>=`: check if value being validated is greater than or equal to the value being compared with.
     * - `<`: check if value being validated is less than the value being compared with.
     * - `<=`: check if value being validated is less than or equal to the value being compared with.
     *
     * When you want to compare numbers, make sure to also set [[type]] to `number`.
     */
    public $operator = '==';

    public $message = ":attribute 不正确";

    public function run($model,$field='')
    {
        if(empty($this->compareValue)){
            $this->compareValue = $model->{$this->compareAttribute};
        }
        $rs = $this->compareValues($this->operator,$this->type,$model->$field,$this->compareValue);
        if(!$rs){
            return $this->message;
        }
        return false;
    }

    /**
     * Compares two values with the specified operator.
     * @param string $operator the comparison operator
     * @param string $type the type of the values being compared
     * @param mixed $value the value being compared
     * @param mixed $compareValue another value being compared
     * @return boolean whether the comparison using the specified operator is true.
     */
    protected function compareValues($operator, $type, $value, $compareValue)
    {
        if ($type === 'number') {
            $value = (float) $value;
            $compareValue = (float) $compareValue;
        } else {
            $value = (string) $value;
            $compareValue = (string) $compareValue;
        }
        switch ($operator) {
            case '==':
                return $value == $compareValue;
            case '===':
                return $value === $compareValue;
            case '!=':
                return $value != $compareValue;
            case '!==':
                return $value !== $compareValue;
            case '>':
                return $value > $compareValue;
            case '>=':
                return $value >= $compareValue;
            case '<':
                return $value < $compareValue;
            case '<=':
                return $value <= $compareValue;
            default:
                return false;
        }
    }
}