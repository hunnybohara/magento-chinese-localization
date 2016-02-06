# Magento中文版 安装指南 #


## 系统要求 ##
### 操作系统 ###
Windows系统或者Linux系统

### Web应用服务器 ###
Apache （1.x或者2.x）
PHP 5.2.0以上（不建议使用PHP 5.3或者更高版本），并包含以下PHP扩展
  * PDO/MySQL
  * MySQLi
  * mcrypt
  * mhash
  * simplexml
  * DOM

### 数据库 ###
MySQL 4.1.20以上，并使用InnoDB存储引擎

### Email服务器 ###
与sendmail兼容的邮件传送器（MTA，Mail Transfer Agent）。大部分Linux系统应该都已经默认安装了sendmail。如果操作系统没有安装邮件传送器，Magento将使用SMTP来发送邮件。

## 环境配置 ##
### PHP配置 ###
由于Magento对资源的要求比较高，我们建议你将PHP的“memory\_limit”设置为“128M”或者更高。

### DNS配置 ###
由于大部分浏览器都不支持localhost为URL的cookie，所以如果你使用localhost来安装Magento的话会出现一些问题，比如后台的管理系统无法登陆。如果是生产环境，我们建议你使用域名或者IP地址来安装Magneto。如果是本机安装，我们建议你使用“127.0.0.1”或者在hosts文件中添加DNS映射“127.0.0.1 magento1.localhost.com”，然后使用“magento1.localhost.com”来安装Magento。这里你可以使用你自己喜欢的域名，但是为了教程的简洁明了，本教程将使用“magento1.localhost.com”作为示例。

## 数据库创建 ##
创建数据库“magento1”，用户名/密码：root/admin。你可以任意更改数据库名，你也可以不用root账号。本教程将使用这里的设置作为示例。

## 创建Apache虚拟主机 ##
不同的系统，这里的配置会有些不一样，但是也基本大同小异，就是要创建一个域名为“magento1.localhost.com”的虚拟主机。一般情况下，在http.conf文件中，添加如下代码：
```
NameVirtualHost *:80
<VirtualHost *:80>
    ServerAdmin zhlmmc@gmail.com
    DocumentRoot "D:\Develop\Magento\Magento-Test\Magento1"
    ServerName magento1.localhost.com
    ServerAlias magento1.localhost.com
    <directory "D:\Develop\Magento\Magento-Test\Magento1">
                AllowOverride All
                allow from all
    </directory>
    ErrorLog "logs/magento1.localhost-error.log"
    CustomLog "logs/magento1.localhost-access.log" common
</VirtualHost>
```
请注意，这里我们使用文件目录“D:\Develop\Magento\Magento-Test\Magento1”作为该虚拟主机的根目录，你可以更改这个目录。本教程将使用该目录作为示例。

## 安装步骤 ##
  1. 下载Magento代码，并解压。把解压以后的文件拷贝到“D:\Develop\Magento\Magento-Test\Magento1”。拷贝完以后，你应该在目录“D:\Develop\Magento\Magento-Test\Magento1”下面看到以下目录“app, downloader, errors…”。
  1. 如果你用的Linux系统，请保证以下目录对于Apache来说可写“app/etc , var , media”。建议将这些目录的权限改成777。
  1. 访问 http://magento1.localhost.com 你应该会看到安装界面。
  1. 安装向导应该很容易懂，一路继续就可以了。在“配置”这一步的时候，记得选中“网址重写”。中文版的Magento默认应该已经选中该选项。还有就是在生产环境中，出于网站的安全考虑，我们建议修改管理路径，不要用默认的“admin”，可以修改为任意字符串，比如“webadmin”。那么修改以后，我们就得用 http://magento1.localhost.com/webadmin 来登录后台管理界面。
  1. 点击继续，Magento会去建立数据表。该步骤需要较长时间，如果出现错误可以尝试刷新页面，如果多次刷新没有用那就把数据库删了重新来过。
  1. 点击继续，输入管理员信息，密钥不需要填。
  1. 点击继续，安装就完成了。
  1. 自动生成的密钥不需要记录，可以在“app/etc/local.xml”文件中找到。

## 后续设置 ##
暂无