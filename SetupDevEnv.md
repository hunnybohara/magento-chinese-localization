# Magento中文化项目的开发环境配置 #

## 签出SVN代码 ##

请根据以下页面的指示从SVN下载代码 http://code.google.com/p/magento-chinese-localization/source/checkout 。

我们建议你使用Zend Studio进行本项目的开发。但是SVN代码库中并不包含Zend Studio的项目文件，所以你可以使用任何你喜欢的开发工具。如果使用Zend Studio的话，你需要在本地新建Zend Framework项目，然后导入代码。

## 开发环境配置 ##
目前我们只准备了Windows下开发环境的设置，如果你用Linux或者其他操作系统，请自行调整。为了方便开发测试，代码库中的代码是一套已经安装好的Magento代码，所以必须通过以下步骤来搭建Magento的运行环境。

### 修改hosts文件 ###
修改C:\Windows\System32\drivers\etc\hosts文件，添加“127.0.0.1 magento.localhost.com”

### 配置MySQL数据库 ###
请从链接 http://code.google.com/p/magento-chinese-localization/downloads/list 下载最新版的开发环境数据库文件。并执行以下SQL语句

**请注意：这里你需要更改下载下来的sql文件路径**
```
CREATE DATABASE magento /*!40100 DEFAULT CHARACTER SET utf8 */;
CREATE USER 'magento'@'localhost' IDENTIFIED BY 'magento';
GRANT ALL ON magento.* TO 'magento'@'localhost';
use magento;
source D:\magento-chinese-localization-db-2010-04-29.sql;
```


### 修改Apache配置文件 ###
在httpd.conf中添加以下内容

**请注意：这里你需要根据你的环境更改文件夹路径。**
```
NameVirtualHost *:80
<VirtualHost *:80>
    ServerAdmin zhlmmc@gmail.com
    DocumentRoot "F:\documents\My Documents\workspaces\zend\Magento-Chinese-Localization"
    ServerName magento.localhost.com
    ServerAlias magento.localhost.com
    <directory "F:\documents\My Documents\workspaces\zend\Magento-Chinese-Localization">
                AllowOverride All
                allow from all
        </directory>
    ErrorLog "logs/magento.localhost-error.log"
    CustomLog "logs/magento.localhost-access.log" common
</VirtualHost>
```
重启Apache

### 验证环境配置 ###
访问 http://magento.localhost.com ， 如果看到网店首页的话就说明配置成功了。访问后台的URL是 http://magento.localhost.com/admin ，用户名密码 “admin/admin1234”。