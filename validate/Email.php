<?php
/**
 * 邮件验证
 * User: sherman
 * Date: 2017/10/11
 * Time: 17:48
 */

namespace X\validate;


class Email extends ValidateAbstract
{
    public function run($model,$field='')
    {
        if ($model->$field && filter_var($model->$field,FILTER_VALIDATE_EMAIL)===false) {
            return $this->message == '' ? ':attribute 格式不正确' : $this->message;
        };
        return false;
    }
}