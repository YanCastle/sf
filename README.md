#碳素云基于Swoole的服务端框架
##功能
1. 自动Session管理，通过集成SessionHandler来实现Session管理或通过session函数扩展实现
2. 集成Model模型管理，移植TP的Model实现
3. 自动缓存，基于Redis做缓存

**注意事项**
需要区分缓存级别，一个是应用CLI应用模式下的临时缓存，这部分缓存需要在每次启动服务时清空
一种是持久缓存，启动应用不需要清空，如Session

需要配置哪个接口接入的数据允许访问哪些模块的哪些类

IP限定功能，只允许哪个IP范围的客户端连接

cache方法如果要缓存临时缓存则用tmp_开头，清楚临时缓存的方法是cache('[cleartmp]')

LOG服务，使用TCP或者UDP协议，在引入框架时通过Config或者define定义日志服务地址。所有使用L方法输出的日志全部发送到这个服务器上。

TODO MySql连接池技术