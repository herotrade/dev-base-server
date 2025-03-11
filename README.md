## 环境需求

- Swoole >= 5.0 并关闭 `Short Name`
- PHP >= 8.1 并开启以下扩展：
  - mbstring
  - json
  - pdo
  - openssl
  - redis
  - pcntl
- [x] Mysql >= 8.0
- [x] Pgsql >= 10
- [x] Sql Server Latest
- Sqlsrv is Latest
- Redis >= 4.0
- Git >= 2.x

# 启动

- 添加.env 文件并配置
- composer install 安装依赖
- php bin/hyperf.php migrate 执行数据表迁移
- php bin/hyperf.php db:seed 执行数据表填充
- php bin/hyperf start 启动服务

# vscode、cursor 中可以通过 f5 开启调试

- 调试配置代码目录：.vscode 中。kill_by_port.sh launch.json tasks.json

# 新增一个扩展 qiutuleng/hyperf-dump-server

- 提供一个 dump 函数代替 console()->info()，还可以在开启服务后将程序内的变量或数据打印到打开服务的浏览器窗口中，基于 Symfony 的 Var-Dump Server 组件
