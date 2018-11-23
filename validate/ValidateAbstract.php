<?php
/**
 * 验证器 抽象 定义
 * User: sherman
 * Date: 2017/10/11
 * Time: 15:55
 */

namespace X\validate;


use \X\Model;

abstract class ValidateAbstract
{
    /**
     * @var string 错误信息
     */
    public $message = '';

    /**
     * 执行验证，返回验证细信息
     * @param Model $model
     * @param string $field
     * @return false | string
     */
    abstract protected function run($model,$field);
}