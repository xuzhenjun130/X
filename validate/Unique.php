<?php
/**
 * 字段唯一验证器
 * User: sherman
 * Date: 2017/10/11
 * Time: 15:53
 */

namespace X\validate;


class Unique extends ValidateAbstract
{
    /**
     * @param \X\Model $model
     * @param string $field
     * @return bool|false|string
     * @throws \Exception
     */
    public function run($model, $field)
    {
        $find = $model::find([$field => $model->$field]);
        $msg = $this->message == '' ? ':attribute : ' . $model->$field . ' 已经存在了' : $this->message;
        if ($find) {
            if ($model->isNewRecord) {
                return $msg;
            } else {
                if ($find->{$model::$pk} != $model->{$model::$pk}) {
                    return $msg;
                }
            }
        }
        return false;
    }
}