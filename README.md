# LeafOK BBS

Copyright (C) LeafOK.com, 2001-2025

演示站点位于：https://www.fenglin.info/bbs/

程序简介
=================
开发语言：PHP + MySQL  
运行平台：Linux/Windows  
软件性质：源代码采用 GNU GPL 授权发布  
功能说明：  
    提供文章浏览、发表、查找等基本功能和其它各种实用功能，可开设多类别多版块，各版块分设讨论区、文摘区、精华区，并提供全面的版主管理支持。  
可选功能：  
    与telnet方式BBS兼容的被动/主动转信功能  
    客户端论坛浏览器  


使用说明：
=================
数据库结构位于 TODO/sql/db_stru.sql ，需先导入  
将 TODO/conf/ 目录下的文件复制到 conf 目录下，并修改  
在数据库中建立系统帐号、栏目、版块等
修改 lib/common.inc.php 文件（站点个性化配置）  
BBS程序位于 bbs 目录下  
管理程序位于 manage 目录下  
生成的精华区位于 gen_ex 目录下  


报告Bug/参与改进：
=================
由于本程序源代码采用 GNU GPL 授权发布，如果您发现任何错误或者愿意加入本BBS的开发，请与我们联系。  
E-mail: leaflet@leafok.com  
