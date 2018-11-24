<?php

/**
 *
 * controller 程序基本控制器
 *
 * 默认控制器为 模块名称+Controller.php
 *
 * @author zhenjun_xu <412530435@qq.com>
 * Date: 14-4-16
 * Time: 下午4:56
 */

namespace X;
class Application extends Component
{

    /**
     * @throws \ReflectionException
     */
    public function runController()
    {
        if(PHP_SAPI!='cli'){
            $router = X::app()->router;
            $rs = $router::dispatch();
            if(is_array($rs) || is_object($rs)){
                echo json_encode($rs,JSON_UNESCAPED_UNICODE);
            }else if (strlen($rs)){
                echo $rs;
            }
        }else{
            $tableName = getopt('m:');
            if(empty($tableName)){
                echo "only accept -m ,'-m tableName' to create model";
            }else{
                \X::app()->cliTool->createModel($tableName['m']);
            }
        }
    }

}