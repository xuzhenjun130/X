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
     * @throws \Exception
     */
    public function createModel($tableName){
        $tableInfo = X::app()->db->getFields($tableName,false);
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
        $attributes = $this->var_export($attributes,true);
        $className = ucfirst($tableName);
        $file = X::app()->basePath.'/models/'.$className.'.php';
        $content = <<<EOF
<?php
namespace models;
/**
 * Class $className
 $property
 */
 class $className extends \\X\\Model
 {
    public function attributes()
    {
        return $attributes;
    }
 }
EOF;
        file_put_contents($file,$content);
        echo 'file create:'.$file;
    }

    /***
     * var_export 变量带有方括号，缩进4个空格
     * @param $expression
     * @param bool $return
     * @return mixed|null|string|string[]
     */
    private function var_export($expression, $return=FALSE) {
        $export = var_export($expression, TRUE);
        $export = preg_replace("/^([ ]*)(.*)/m", '$1$1$2', $export);
        $array = preg_split("/\r\n|\n|\r/", $export);
        $array = preg_replace(["/\s*array\s\($/", "/\)(,)?$/", "/\s=>\s$/"], [NULL, ']$1', ' => ['], $array);
        $export = join(PHP_EOL, array_filter(["["] + $array));
        if ((bool)$return) return $export; else echo $export;
    }
}