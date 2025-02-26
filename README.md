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
