# PDS (Proxy Download Server)
PDS -- Proxy Download Server(代理下载服务器)
- 基于PHP Curl实现代理下载服务器，也可以当作 代理请求服务器、中转服务器
- 分片输出，避免大文件崩溃，节约内存，可以做PHP大文件下载
- CURLOPT_WRITEFUNCTION 参数的 example 代码

Request:

> pds.php?url=http://example.com/

Debug:

> pds.php?url=http://example.com/&debug=1

### 项目灵感
有一次在Github下载软件安装包，国内网络下载超慢，想到了代理下载，或者用中转服务器来下载快很多，就开始设计和调研，并动手写个代理下载服务。

你们还想什么场景用到，欢迎跟我交流

Email:  lyaohe@gmail.com

### 正在解决的问题
1. 请求百度有点问题
- `pds.php?url=https://www.baidu.com`  请求失败
- `pds.php?url=https://www.baidu.com&debug=1`  Debug输出是成功，还在分析header报文

2. 多考虑一些异常情况


### 计划继续开发的功能
1. 支持post请求、支持cookie透传，还在想哪些场景需要
2. 简单的网页代理，返回的Body内容的链接替换成代理链接
3. 复杂的网页代理，JS的异步请求链接也替换成代理链接

更新时间: 2017.07.14
