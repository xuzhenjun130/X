<?php

namespace X;
/**
 * 分页类
 * Class Pagination
 * @package framework
 */
class Pagination
{
    /**
     * @var int 分页数量
     */
    public $totalPage = 0;
    /**
     * @var int 总条数
     */
    public $count = 0;
    /**
     * @var int 分页大小
     */
    public $size = 10;

    /**
     * @var string 页面url
     */
    public $url = '';
    /**
     * @var int 当前页
     */
    public $page = 1;
    /**
     * @var string 当前 li class
     */
    public $activeClass = "active";

    public $ulClass = "pagination";
    /**
     * @var string li class
     */
    public $itemClass = "";
    public $firstClass = "first";
    public $lastClass = "last";
    public $preClass = "pre";
    public $nextClass = "next";


    public $showFirstLast = true;
    public $first = "首页";
    public $last = "尾页";

    public $showPrevNext = true;
    public $prev = "上页";
    public $next = "下页";


    /**
     * @var int 分页显示前后多少页
     */
    public $displayLength = 6;

    /**
     * 显示分页
     * @param  int $page
     * @return string
     */
    public function display($page)
    {
        $this->totalPage = ceil($this->count / $this->size);
        if ($page * 1 <= 0) {
            $this->page = 1;
        } else if ($page > $this->totalPage) {
            $this->page = $this->totalPage;
        } else {
            $this->page = $page * 1;
        }

        $data = [];//用于分页显示的数据
        if ($this->totalPage <= $this->displayLength) {
            $data = range(1, $this->totalPage);
        } else {
            $half = intval($this->displayLength / 2);
            $start = max(1, $this->page - $half);
            $end = min($this->page + $half - 1, $this->totalPage);
            //当前分页向后 -halt 小于0，说明不够，往后补足
            if ($this->page - $half <= 0) {
                $end = $end + abs($this->page - $half) + 1;
                if ($this->displayLength % 2 !== 0) $end++; //非偶数，会算少1，加上
            }
            //当前分页向前+ halt 大于分页数，说明不够，往前补足
            if ($this->page + $half > $this->totalPage) {
                $start = $start - ($half - ($this->totalPage - $this->page)) + 1;
                if ($this->displayLength % 2 !== 0) $start--; //非偶数，会算少1，加上
            }

            $data = range($start, $end);
        }
        /**
         * 分页html
         */
        $html = '<ul class="'.$this->ulClass.'">';
        //first
        if($this->showFirstLast){
            $html .= "<li class='{$this->itemClass} {$this->firstClass}'><a href='{$this->url}&p=1'>$this->first</a></li>";
        }

        foreach ($data as $k => $v) {

            //pre
            if ($k == 0 && $this->showPrevNext) {
                $prev = $this->page - 1;
                $prev = $prev <= 0 ? 1 : $prev;
                if ($prev == $this->page) {
                    $html .= "<li class='{$this->itemClass} {$this->preClass}'><a href='javascript:void(0);'>$this->prev</a></li>";
                } else {
                    $html .= "<li class='{$this->itemClass} {$this->preClass}'><a href='{$this->url}&p=$prev'>$this->prev</a></li>";
                }
            }
            $html .= "<li class='{$this->itemClass} ".($v==$this->page?$this->activeClass:'')."'><a href='{$this->url}&p=$v'>$v</a></li>";

            if ($k == count($data) - 1 && $this->showPrevNext) {
                if($this->totalPage > $this->displayLength && ($this->totalPage-$this->page > $this->displayLength)){
                    $html .= "<li class='{$this->itemClass} middle'>...</li>";
                    $html .= "<li class='{$this->itemClass}'><a href='{$this->url}&p=$this->totalPage'>$this->totalPage</a></li>";
                }
                //next
                $next = $this->page + 1;
                $next = $this->page >= $this->totalPage ? $this->totalPage : $next;
                if ($next == $this->page) {
                    $html .= "<li class='{$this->itemClass} {$this->nextClass}'><a href='javascript:void(0)'>$this->next</a></li>";
                } else {
                    $html .= "<li class='{$this->itemClass} {$this->nextClass}'><a href='{$this->url}&p=$next'>$this->next</a></li>";
                }
            }
        }
        //last
        if($this->showFirstLast){
            $html .= "<li class='{$this->itemClass} {$this->lastClass}'><a href='{$this->url}&p=$this->totalPage'>$this->last</a></li>";
        }
        $html .= '</ul>';

        return $html;

    }


}

