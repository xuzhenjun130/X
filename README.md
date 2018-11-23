# X framework  - 极简的php mvc 框架

(PHP 5 >= 5.5.0)

## 利用composer 做自动加载
这年头不用composer 都不好意思说自己学过php.
## 参考 Yii框架的Component 组件设计
有点Yii框架的影子，方便扩展，通过配置替换框架代码，实现自己想要的功能
## 没有复杂的设计模式，最多就用个继承
十分钟上手，才十来个文件的框架，几百行代码，有啥不满意的，直接改框架源码就是了
## 适合做个小网站，快速开发
没有复杂的路由解析了，只能通过 r=controller/action  的形式来访问
适合放在php虚拟主机，没钱买不起服务器；没有多域名，没有apache、nginx 可配置,一切从简。


# 项目文件说明

```
+--- X.php                   #框架主文件
+--- Application.php         #程序基本控制器
+--- CliTool.php             #cli 模式工具  用于自动生成模型文件
+--- Component.php           #框架组件基本类，所有的组件都是继承这个类
+--- Controller.php          #控制器基类
+--- Db.php                  #mysql 数据库操作
+--- DebugFunction.php       #debug 函数
+--- ErrorHandler.php        #错误处理
+--- HtmlHelper.php          #html 帮助类
+--- Model.php               #模型基类
+--- Pagination.php          #分页
+--- Router.php              #路由
+--- TemplateParser.php      #模板
+--- Uploader.php            #文件上传
+--- User.php                #用户登录
+--- validate                #模型自动验证相关
|   +--- Compare.php         #比较验证
|   +--- Email.php           #邮件验证
|   +--- Regular.php         #正则验证    
|   +--- Required.php        #必填验证
|   +--- StringValidator.php #字符串验证
|   +--- Unique.php          #字段唯一性验证
|   +--- ValidateAbstract.php #验证器 抽象定义
+--- views                   #视图
|   +--- error.php           #错误视图 
```

# 安装

`composer require sherman/x`

# 例子及说明文档

[https://github.com/xuzhenjun130/X-framework-app](https://github.com/xuzhenjun130/X-framework-app)