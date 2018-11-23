<?php

/**
 * 错误处理
 * @author zhenjun_xu <412530435@qq.com>
 * Date: 14-3-3
 * Time: 上午9:50
 */
 namespace X;
 
class ErrorHandler
{
    /**
     * @var bool 是否 清空之前的输出内容
     */
    public $discardOutput = true;

    public function init()
    {
        //清空之前的输出内容

        if($this->discardOutput){
            for ($level = ob_get_level(); $level > 0; --$level) {
                if (!@ob_end_clean()) ob_clean();
            }
        }
        if (!headers_sent())
            header("HTTP/1.0 500 Internal Server Error");
    }

    /**
     * 致命错误显示
     * @param array $error
     */
    public function displayFatal($error)
    {
        $data = array();
        $data['code'] = 0;
        $data['message'] = $error['message'];
        $data['file'] = $error['file'];
        $data['line'] = $error['line'];
        $data['type'] = 'fatal';
        $data = $this->_formatError(debug_backtrace(), $data);

        $this->render('error', $data);
    }

    /**
     * 显示错误
     * @param $code
     * @param $message
     * @param $file
     * @param $line
     */
    public function displayError($code, $message, $file, $line)
    {
        $data = array();
        $data['code'] = $code;
        $data['message'] = $message;
        $data['file'] = $file;
        $data['line'] = $line;
        $data['type'] = 'error';
        $data = $this->_formatError(debug_backtrace(), $data);
        $this->render('error', $data);

    }

    /**
     * 显示异常
     * @param \Exception $exception
     * @param  string $custom 自定义错误信息
     */
    public function displayException($exception,$custom='')
    {
        $data = array();
        $data['code'] = $exception->getCode();
        $data['message'] = $exception->getMessage()." ( {$custom} )";
        $data['file'] = $exception->getFile();
        $data['line'] = $exception->getLine();
        $data['type'] = get_class($exception);
        $trace = $exception->getTrace();
        $data = $this->_formatError($trace, $data);
        $this->render('error', $data);
    }

    /** @var int 错误所在行的上下文行数 */
    public $maxLine = 6;

    /**
     * 显示错误代码
     * @param $file
     * @param $line
     */
    public function showCode($file, $line)
    {
        $line = $line - 1; //数组下标从0开始的
        $contentArr = file($file);
        foreach ($contentArr as $k => $v) {
            if ($k > $line - $this->maxLine) {
                echo '<span class="line">' . ($k + 1) . '</span>' . $v;
            }
            if ($k == $line - 1) {
                break;
            }
        }
        echo '<span class="errorLine"><span class="line">' . ($line + 1) . '</span>' . $contentArr[$line] . '</span>';
        foreach ($contentArr as $k => $v) {
            if ($k > $line) {
                echo '<span class="line">' . ($k + 1) . '</span>' . $v;
            }
            if ($k == $line + $this->maxLine) {
                break;
            }
        }

    }

    /**
     * view 显示
     * @param string $fileName
     * @param array $data
     */
    protected function render($fileName, $data)
    {
        $file =  X_PATH . '/views/' . $fileName . '.php';
        include($file);
        exit;
    }

    /**
     * 格式化代码追踪数组
     * @param $trace
     * @param array $data
     * @return array
     */
    private function _formatError($trace, Array $data)
    {
        foreach ($trace as $k => $v) {
            if ($k == 0) continue;
            $data['trace'][] = array(
                'file' => isset($v['file']) ? $v['file'] : null,
                'line' => isset($v['line']) ? $v['line'] : null,
                'method' => array(
                    'class' => isset($v['class']) ? $v['class'] : null,
                    'type' => isset($v['type']) ? $v['type'] : null,
                    'function' => isset($v['function']) ? $v['function'] : null,
                    'args' => isset($v['args']) ? $v['args'] : null,
                ),
            );
        }
        return $data;
    }

} 