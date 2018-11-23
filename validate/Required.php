<?php
/**
 * 必填验证器
 * User: sherman
 * Date: 2017/10/10
 * Time: 18:31
 */

namespace X\validate;


class Required extends ValidateAbstract
{
    public function run($model,$field='')
    {
        if (strlen($model->$field) == 0) {
            return $this->message == '' ? ':attribute 必填' : $this->message;
        };
        return false;
    }
}