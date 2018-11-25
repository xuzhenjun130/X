<?php
/**
 * 视图模板处理
 * User: sherman
 * Date: 2017/9/14
 * Time: 18:39
 */

namespace X;


class TemplateParser
{

    /**
     * 模板内容
     * @var string
     */
    private $_tpl;

    /**
     * 构造方法，用于获取模板文件里的内容
     * @param $_tplFile
     * @throws \Exception
     */
    public function __construct($_tplFile) {
        if (!$this->_tpl = file_get_contents($_tplFile)) {
           throw new \Exception('ERROR：模板文件读取错误！');
        }
    }
    //解析普通变量
    private function parVar() {
        $_patten = '/\{\{\$(.+)\}\}/';
        if (preg_match($_patten,$this->_tpl)) {
            $this->_tpl = preg_replace($_patten,"<?php echo htmlentities(\$$1) ;?>",$this->_tpl);
        }
    }

    /**
     * 执行编译
     * @param $_parFile
     * @throws \Exception
     */
    public function compile($_parFile) {
        //解析模板内容，只实现解析普通变量
        $this->parVar();
        //$this->parIf();
        //生成编译文件
        $viewPath = dirname($_parFile);
        if(!file_exists($viewPath)){
            mkdir($viewPath,0777,true);
        }
        if (!file_put_contents($_parFile, $this->_tpl)) {
            throw new \Exception('ERROR：编译文件生成出错！');
        }
    }

}