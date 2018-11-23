<?php
/**
 * @var $data array
 */
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html lang="zh-cn">
<head>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
    <title><?php echo $data['type'] ?></title>
</head>
<style>
    .pln {
        color: #000
    }

    @media screen {
        .str {
            color: #080
        }

        .kwd {
            color: #008
        }

        .com {
            color: #800
        }

        .typ {
            color: #606
        }

        .lit {
            color: #066
        }

        .pun, .opn, .clo {
            color: #660
        }

        .tag {
            color: #008
        }

        .atn {
            color: #606
        }

        .atv {
            color: #080
        }

        .dec, .var {
            color: #606
        }

        .fun {
            color: red
        }
    }

    @media print, projection {
        .str {
            color: #060
        }

        .kwd {
            color: #006;
            font-weight: bold
        }

        .com {
            color: #600;
            font-style: italic
        }

        .typ {
            color: #404;
            font-weight: bold
        }

        .lit {
            color: #044
        }

        .pun, .opn, .clo {
            color: #440
        }

        .tag {
            color: #006;
            font-weight: bold
        }

        .atn {
            color: #404
        }

        .atv {
            color: #060
        }
    }

    pre.prettyprint {
        padding: 2px;
        border: 1px solid #888
    }

    ol.linenums {
        margin-top: 0;
        margin-bottom: 0
    }

    li.L0, li.L1, li.L2, li.L3, li.L5, li.L6, li.L7, li.L8 {
        list-style-type: none
    }

    li.L1, li.L3, li.L5, li.L7, li.L9 {
        background: #eee
    }
</style>

<style>
    fieldset legend {
        color: red;
        font-size: 20px;
        font-weight: bold;
    }

    fieldset {
        background: #F3F3F3;
        border-radius: 8px;
    }

    .file {
        font-weight: bold;
    }

    pre span.line {
        border-right: 1px solid #CCCCCC;
        color: #999999;
        margin: 10px;;
    }

    .error pre {
        border: 1px solid #EEEEEE;
        margin: 5px;
    }

    .error .errorLine {
        background: #FCE3E3;
    }

    #trace {
        color: #800000;
        font-size: 14px;
        margin: 20px 10px 0 0;
        font-weight: bold;
    }

    .trace pre {
        display: none;
    }

    .trace .file {
        cursor: pointer;
    }
</style>
<body >

<fieldset>
    <legend><?php echo $data['type'] ?></legend>
    <p><?php echo $data['message'] ?></p>
</fieldset>
<div class="error">
    <p class="file"><?php echo $data['file'] . '(' . $data['line'] . ')' ?></p>
    <pre class="prettyprint" id="PHP"><?php echo $this->showCode($data['file'], $data['line'] ); ?></pre>
</div>

<div id="trace">代码追踪</div>
<?php foreach ($data['trace'] as $k => $v):
if (empty($v['file']) || empty($v['line'])) continue;
?>
<div class="error trace">
    <p class="file" onclick="show(this)"><?php echo $v['file'] . '(' . $v['line'] . ')' ?></p>
    <pre class="prettyprint" id="PHP"><?php echo $this->showCode($v['file'], $v['line']); ?></pre>
</div>
<?php endforeach; ?>
<div id="tips">
    消耗时间: <?php echo microtime(true) - X_BEGIN_TIME; ?><br/>
    消耗内存: <?php
    $size = memory_get_usage(true);
    $unit = array('b', 'kb', 'mb', 'gb', 'tb', 'pb');
    $i = floor(log($size, 1024));
    echo round($size/pow(1024, $i), 2).' '.$unit[$i];
    ?>
</div>
</body>
<script>
    function show(obj) {
        if (obj.nextElementSibling.style.display == 'block') {
            obj.nextElementSibling.style.display = 'none';
        } else {
            obj.nextElementSibling.style.display = 'block';
        }
    }
</script>
</html>
