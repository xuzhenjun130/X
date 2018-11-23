<?php
/**
 * cli 模式工具
 * User: sherman
 * Date: 2017/9/20
 * Time: 17:12
 */

namespace X;


class CliTool
{

    /**
     * 创建模型文件
     * @param $tableName
     */
    public function createModel($tableName){
        $tableInfo = \X::app()->db->getFields($tableName,false);
        $property = '';
        $attributes = [];
        foreach ($tableInfo as $v){
            $type = 'string';
            if(stripos($v['Type'],'smallint')!==false){
                $type = 'integer';
            }else if(stripos($v['Type'],'integer')!==false){
                $type = 'integer';
            }
            else if(stripos($v['Type'],'bigint')!==false){
                $type = 'integer';
            }
            else if(stripos($v['Type'],'boolean')!==false){
                $type = 'boolean';
            } else if(stripos($v['Type'],'float')!==false){
                $type = 'float';
            }else if(stripos($v['Type'],'double')!==false){
                $type = 'double';
            }else if(stripos($v['Type'],'decimal')!==false){
                $type = 'double';
            }
            else if(stripos($v['Type'],'money')!==false){
                $type = 'number';
            }
            else if(stripos($v['Type'],'money')!==false){
                $type = 'number';
            }
            $property .= "* @property $type {$v['Field']} \r\n";
            if(!empty($v['Comment'])){
                $attributes[$v['Field']] = $v['Comment'];
            }else{
                $attributes[$v['Field']] = $v['Field'];
            }
        }
        $attributes = var_export($attributes,true);
        $className = ucfirst($tableName);
        $file = \X::app()->basePath.'/models/'.$className.'.php';
        $content = <<<EOF
<?php
namespace app\\models;
/**
 * Class $className
 $property
 */
 class $className extends \\X\\Model
 {
    public function attributes(){
        return $attributes;
    }
 }
EOF;
        file_put_contents($file,$content);
        echo 'file create:'.$file;
    }
}