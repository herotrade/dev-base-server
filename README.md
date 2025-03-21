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

# 新增一个扩展来实现数据过滤等功能 Hyperf Query Builder

很多功能请参考文档：[hyperf-query-builder 扩展文档地址](https://github.com/daixinguo/hyperf-query-builder)

### 用法示例

```php
// App\QueryBuilder\QueryBuilder 这是一个基于 Hyperf Query Builder 中 ApiElf\QueryBuilder\QueryBuilder 进行扩展的基类。这个基类中扩展了三个方法
// filters() 配合模型基类 App\QueryBuilder\Model 实现自带创建时间、更新时间、id 的相关筛选（详细参考这两个基类）
// pagex() 分页，支持通过 query 参数 page_size 设置为 -1 获取所有记录（当一个接口既有分页展示场景又有不分页展示场景如需要作为下拉选择的数据时）
// page()  强制分页，page_size <= 0 时默认每页展示 15 条数据
use App\QueryBuilder\QueryBuilder;

/**
 * 获取币种列表
 */
public function list(RequestInterface $request): mixed
{
    return QueryBuilder::for(Currency::class, $request)
        ->filters(['symbol', 'name'])
        ->defaultSort('sort')
        ->allowedSorts(['id', 'sort', 'created_at'])
        ->pagex();
}
```

- 以上示例支持的部分过滤、筛选参数

```
# query 参数示例
?filter[name]=BTC&filter[symbol]=btc&sort=id,-created_at&page=1&page_size=15
```

| 名称                     | 位置  | 类型   | 必选 | 说明                                                                                        |
| ------------------------ | ----- | ------ | ---- | ------------------------------------------------------------------------------------------- |
| filter[name]             | query | string | 否   | 【筛选】币种名称                                                                            |
| filter[symbol]           | query | string | 否   | 【筛选】币种代码                                                                            |
| filter[CreatedAtOfDay]   | query | string | 否   | 【筛选】指定日期当天时间范围筛选                                                            |
| filter[CreatedAtBefore]  | query | string | 否   | 【筛选】给定创建时间之前                                                                    |
| filter[CreatedAtAfter]   | query | string | 否   | 【筛选】给定创建时间之后                                                                    |
| filter[CreatedAtBetween] | query | string | 否   | 【筛选】给定创建时间区间                                                                    |
| filter[UpdatedAtBefore]  | query | string | 否   | 【筛选】给定更新时间前                                                                      |
| filter[UpdatedAtAfter]   | query | string | 否   | 【筛选】给定更新时间后                                                                      |
| filter[UpdatedAtBetween] | query | string | 否   | 【筛选】给定更新时间区间                                                                    |
| sort                     | query | string | 否   | 【排序】支持：id、sort、created_at。?sort=-id：表示根据 id 降序；?sort=id：表示根据 id 升序 |
| page                     | query | string | 否   | 【分页】当前页                                                                              |
| page_size                | query | string | 否   | 【分页】每页条数（设置为 -1 时表示不分页，获取所有数据）                                    |

# App\Http\Common\Controller\AbstractController 调整

- 继承此基类的控制器可以直接在控制器中使用 success 方法返回查询的结果（基类中自动处理分页数据结构）

```php
$result = $this->currencyService->list($this->request);
return $this->success($result);
```

- 基类 success 方法代码

```php
protected function success(mixed $data = [], ?string $message = null): Result
{
    // 处理分页情况
    if ($data instanceof LengthAwarePaginator) {
        // dump("分页");
        $data = [
            'list' => $data->items(),
            'total' => $data->total(),
            'page' => $data->currentPage(),
            'page_size' => $data->perPage(),
            'total_page' => $data->lastPage(),
        ];
    }
    if ($data instanceof JsonResource && $data->resource instanceof LengthAwarePaginator) {
        // dump("资源分页");
        $data = [
            'list' => $data->resource->items(),
            'total' => $data->resource->total(),
            'page' => $data->resource->currentPage(),
            'page_size' => $data->resource->perPage(),
            'total_page' => $data->resource->lastPage(),
        ];
    }
    return new Result(ResultCode::SUCCESS, $message, $data);
}
```

# Resource 处理数据结构

- 当从数据库中查询出来的列表数据需要对数据结构进行调整时请使用 API 资源构造器进行处理（App\Http\Common\Controller\AbstractController 中 success 方法同样考虑了这种情况）

[文档地址](https://hyperf.wiki/3.1/#/zh-cn/db/resource)

```php
$result = $this->currencyService->list($this->request);
return $this->success(CurrencyResource::collection($result));

// 在 CurrencyResource 中对列表的每条数据进行数据结构处理
```

# 时区设置

- 在 .env 中添加 TIMEZONE 配置

```
# timezone 时区
TIMEZONE=Asia/Shanghai
```

# JWT AUTH 秘钥相关，业务后台与前台应用应该使用两个不同的秘钥

- 提供了生产秘钥的命令（每一个项目必须生成不同的秘钥！！！）

```shell
php bin/hyperf.php jwt:secret

# help
Description:
  设置用于签署令牌的JWT密钥

Usage:
  jwt:secret [options]

Options:
  -s, --show                      生成并显示密钥而不是修改文件
      --always-no                 跳过生成密钥如果它已经存在
  -f, --force                     跳过确认覆盖现有密钥
      --disable-event-dispatcher  Whether disable event dispatcher.
  -h, --help                      Display help for the given command. When no command is given display help for the list command
      --silent                    Do not output any message
  -q, --quiet                     Only errors are displayed. All other output is suppressed
  -V, --version                   Display this application version
      --ansi|--no-ansi            Force (or disable --no-ansi) ANSI output
  -n, --no-interaction            Do not ask any interactive question
  -v|vv|vvv, --verbose            Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

- 为管理端、用户端设置不同的秘钥到不同的场景中。JWT 配置 config/autoload/jwt.php

```php
// 管理端 JWT 场景
'default' => [
  // jwt 配置 https://lcobucci-jwt.readthedocs.io/en/latest/
  'driver' => Jwt::class,
  // 其他配置省略
]
// 用户端 JWT 场景
'api' => [
  // jwt 配置 https://lcobucci-jwt.readthedocs.io/en/latest/
  'driver' => Jwt::class,
  // jwt 签名key
  'key' => InMemory::base64Encoded(env('JWT_API_SECRET')),
  // jwt 签名算法 可选 https://lcobucci-jwt.readthedocs.io/en/latest/supported-algorithms/
  'alg' => new Sha256(),
  // token过期时间，单位为秒
  'ttl' => (int) env('JWT_API_TTL', 3600),
  // 刷新token过期时间，单位为秒
  'refresh_ttl' => (int) env('JWT_API_REFRESH_TTL', 7200),
  // 黑名单模式
  'blacklist' => [
      // 是否开启黑名单
      'enable' => true,
      // 黑名单缓存前缀
      'prefix' => 'jwt_blacklist',
      // 黑名单缓存驱动
      'connection' => 'default',
      // 黑名单缓存时间 该时间一定要设置比token过期时间要大一点，最好设置跟过期时间一样
      'ttl' => (int) env('JWT_API_BLACKLIST_TTL', 7201),
  ],
  'claims' => [
      // 默认的jwt claims
      RegisteredClaims::ISSUER => (string) env('APP_NAME'),
  ],
],
```

- 管理端验证登录中间件：

```php
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Admin\Middleware\PermissionMiddleware;
use App\Http\Common\Middleware\OperationMiddleware;

#[Controller(prefix: 'admin/currency')]
// 验证登录
#[Middleware(middleware: AccessTokenMiddleware::class, priority: 100)]
// 验证权限
#[Middleware(middleware: PermissionMiddleware::class, priority: 99)]
// 记录操作日志
#[Middleware(middleware: OperationMiddleware::class, priority: 98)]
class CurrencyController extends AbstractController
{
  // ....
}
```

- 用户端验证登录中间件：

```php
use App\Http\Api\Middleware\TokenMiddleware;


#[Controller(prefix: 'api/currency')]
// 验证登录
#[Middleware(middleware: TokenMiddleware::class, priority: 100)]
class CurrencyController extends AbstractController
{
    // ...
}
```

# AI CURD 代码生产规则

本目录包含用于 Hyperf CRUD 代码生成的工具和模板，帮助您快速生成符合项目规范的代码。

## 文档索引

1. [标准模板](./StandardTemplate.md) - 包含所有 CRUD 相关文件的标准模板
2. [提示词模板](./PromptTemplate.md) - 用于向 AI 请求生成代码的标准提示词模板
3. [开发指南](./DevelopmentGuide.md) - 详细的开发流程和最佳实践

## 快速开始

要生成一个新的 CRUD 功能，请按照以下步骤操作：

1. 准备您的数据库表 DDL 语句
2. 复制[提示词模板](./PromptTemplate.md)内容
3. 将您的 DDL 和配置信息填入提示词模板中
4. 向 AI 提交请求，生成 CRUD 代码
5. 审查并应用生成的代码

## 主要特点

### 验证规则自动生成

系统会根据数据库表 DDL 自动生成大部分验证规则，包括：

- 从`NOT NULL`约束生成`required`验证
- 从字符串长度生成`max:{length}`验证
- 从数据类型生成相应的类型验证
- 从唯一索引生成`unique`验证
- 从枚举类型生成`in:{options}`验证

您只需要在配置文件中添加那些无法从 DDL 中推断的特殊验证规则，如：数组验证、邮箱验证、URL 验证等。

### 特殊字段自动处理

系统能智能处理多种特殊字段类型：

- **枚举字段**：对于 tinyint 类型字段，如果注释中包含多个可选值（如"1-正常,2-禁用"），系统会自动创建对应的枚举类
- **布尔字段**：对于 tinyint 类型字段，如果注释中表明只有 0 和 1 两个值，系统会处理为布尔类型
- **JSON 字段**：对于 JSON 类型字段，会自动添加 array 验证和类型转换

### 简化的配置结构

配置文件结构已经过简化，只需要关注：

- 实体基本信息
- 可过滤和可排序字段
- 特殊验证规则（仅补充 DDL 无法表达的规则）
- 枚举字段配置（可选）
- API 接口配置

## 常见问题

### 如何选择需要过滤的字段？

通常选择用户可能需要按条件查询的字段，如名称、状态、类型等。

### 如何选择可排序字段？

通常包括 ID、创建时间、更新时间以及一些可能需要排序的业务字段，如排序值、价格等。

### 哪些验证规则需要手动添加？

只需添加那些 DDL 无法表达的验证规则，例如：

- `array`验证（用于 JSON 字段）
- `email`验证（用于邮箱字段）
- `url`验证（用于 URL 字段）
- 正则表达式验证
- 自定义验证规则

### 如何处理枚举字段？

对于有多个固定值的字段（如状态字段），系统会：

1. 自动从 DDL 注释中提取可能的枚举值
2. 创建相应的枚举类（位于`app/Model/Enums/{Module}/`目录）
3. 在模型中添加类型转换
4. 在验证规则中添加`in:{可选值列表}`验证

您也可以在配置中的`enums`部分手动指定枚举配置。
