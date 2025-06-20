# Hyperf CRUD 代码生成提示词模板

我需要您帮我基于 Hyperf 框架为以下数据库表创建完整的 CRUD 功能代码。请遵循我们项目的标准开发规范。

## 模块名称：交易所管理

## 表结构

```sql
CREATE TABLE `member` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `account` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '用户账号',
  `password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '密码',
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '用户邮箱',
  `nickname` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '用户昵称',
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '用户头像URL',
  `google_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Google OAuth2.0唯一标识',
  `apple_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Apple Sign-in唯一标识',
  `user_type` tinyint NOT NULL DEFAULT '0' COMMENT '用户类型：0-普通用户 1-分润用户 2-套餐用户',
  `last_login_type` tinyint NOT NULL DEFAULT '0' COMMENT '最后登录方式：0-普通登录 1-Google登录 2-Apple登录',
  `parent_id` bigint NOT NULL DEFAULT '0' COMMENT '上级id',
  `invite_code` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '邀请码',
  `status` tinyint NOT NULL DEFAULT '1' COMMENT '账号状态：1-正常 0-禁用',
  `last_login_time` timestamp NULL DEFAULT NULL COMMENT '最后登录时间',
  `last_login_ip` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '最后登录IP',
  `is_trader` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '是否系统交易员',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `member_account_unique` (`account`),
  UNIQUE KEY `member_email_unique` (`email`),
  UNIQUE KEY `member_google_id_unique` (`google_id`),
  UNIQUE KEY `member_apple_id_unique` (`apple_id`),
  KEY `mobile_user_status_index` (`status`),
  KEY `mobile_user_created_at_index` (`created_at`),
  KEY `mobile_user_login_type_index` (`user_type`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='移动端用户表';
```

## 模型

模型文件已经存在，请勿重复创建。模型文件地址：app/Model/Member/Member.php
关联其他模型时需要注意模型目录在 app/Model 下，但是需要排出 app/Model/Mobile 下的所有模型（此目录下的模型已经被废弃）

## 配置信息

请使用以下 PHP 配置数组来生成代码（已包含详细注释说明每个配置项的作用）：

```php
$config = [
    // 实体信息配置
    'entity' => [
        // 实体名称，使用大驼峰(PascalCase)命名法，对应模型类名
        'name' => 'Member',
        // 模块名称，通常与实体名一致或表示功能模块，用于目录结构
        'module' => 'Member',
        // 实体变量名，使用小驼峰(camelCase)命名法，用于控制器和服务类中
        'variableName' => 'member',
    ],

    // 字段相关配置
    'fields' => [
        // 可过滤字段列表，这些字段将在QueryBuilder中通过filters方法配置
        // 前端可以通过?filter[field]=value参数过滤数据。配置说明：name 表示模糊查询，==name 表示精确查询
        'filterable' => ['account', 'email', 'nickname','==user_type','==parent_id','==status','==is_trader'],

        // 可排序字段列表，这些字段将在QueryBuilder中通过allowedSorts方法配置
        // 前端可以通过?sort=field或?sort=-field(降序)参数排序数据
        'sortable' => ['id', 'created_at'],

        // 默认排序字段，将在QueryBuilder中通过defaultSort方法配置
        'defaultSort' => '',

        // 自定义验证规则（只需配置特殊验证规则）
        // 注意：基本验证规则会根据DDL中的字段类型、长度、约束等自动生成
        // 只需在此处配置DDL无法表达的特殊验证规则
        'customValidation' => [
            // 字段名 => 额外验证规则数组
            '字段名' => [
                // 额外的验证规则（会与从DDL自动生成的规则合并）
                'rules' => 'array|自定义验证规则',
                // 验证失败时的错误消息
                'message' => '自定义错误消息',
            ],
            // 例如：对于JSON字段需要验证为数组
            // 'name' => [
            //     'rules' => 'array',
            //     'message' => '名称格式不正确',
            // ],
        ],
    ],

    // API接口配置
    'api' => [
        // 管理端API配置
        'admin' => [
            // 是否启用管理端API
            'enabled' => true,
            // 【资源类】是否生成对应的 Resource 文件（当从数据库中查询出来的列表数据需要对数据结构进行调整时请使用 API 资源构造器进行处理）
            'resource' => false,
            // 分页是否支持通过 query 参数 page_size 设置为 -1 获取所有记录
            'pagex' => false,
        ],

        // 用户端API配置
        'user' => [
            // 是否启用用户端API
            'enabled' => false,
            // 需要创建的用户端接口列表
            // 可选值: "list", "detail", "create", "update", "delete"
            'endpoints' => ['list'],
            // 【资源类】是否生成对应的 Resource 文件（当从数据库中查询出来的列表数据需要对数据结构进行调整时请使用 API 资源构造器进行处理）
            'resource' => false,
            // 分页是否支持通过 query 参数 page_size 设置为 -1 获取所有记录
            'pagex' => true,
        ],
    ],
];
```

## 需要生成的接口

// 权限 => 接口名称
'member:list:index' => '用户管理-用户列表-列表',
'member:list:add' => '用户管理-用户列表-添加',
'member:list:update' => '用户管理-用户列表-更新',
'member:list:delete' => '用户管理-用户列表-删除',
'member:list:statistics' => '用户管理-用户列表-统计',
'member:list:activity' => '用户管理-用户列表-账户活动',
'member:profitUser:index' => '用户管理-分润用户-列表',
'member:profitUser:audit' => '用户管理-分润用户-审核',
'member:profitUser:setStrategy' => '用户管理-分润用户-设置策略',
'member:profitUser:statistics' => '用户管理-分润用户-统计',

## 验证规则自动生成说明

我将根据表结构 自动生成基本验证规则，规则如下：

1. 如果字段定义为`NOT NULL`，会添加`required`验证
2. 对于有长度限制的字符串字段 char 类型或者 varchar(20)，会添加`max:{length}`验证（说明一下：如果是 varchar(255)则不需要添加长度限制）
3. 对于数值类型(int, decimal 等)，会添加对应的`integer`或`numeric`验证
4. 对于有默认值的字段，若有默认值则允许为空(非必填)
5. 对于日期时间字段，会添加`date`相关验证
6. 对于枚举类型：

   生成的验证规则：

   ```php
   use Hyperf\Validation\Rule;

   'status' => ['required', Rule::in(array_column(\App\Model\Enums\Module\Status::cases(), 'value'))],
   ```

7. 对于唯一索引字段，会添加`unique:{table},{column}`验证，并在更新时排除当前记录
8. 对于 JSON 类型，默认添加`array`验证

请在`customValidation`中只添加 DDL 无法表达的特殊验证规则，如：

- 数组验证：`array`
- 邮箱验证：`email`
- URL 验证：`url`
- 正则表达式验证：`regex:/pattern/`
- 自定义验证规则

## 过滤配置说明

配置中支持配置精确查询过滤和模糊查询过滤。==name 表示精确查询过滤，name 表示模糊查询过滤

- 精确查询过滤

当配置信息中 $config['fields']['filterable'] 配置的字段名前面加'=='时表示精确查询过滤，在 QueryBuilder 的过滤写法中需要写成 AllowedFilter::exact('字段名')，参考下面示例

- 示例

```php
use ApiElf\QueryBuilder\AllowedFilter;

return QueryBuilder::for(Currency::class, $request)
    // 配置示例 $config['fields']['filterable'] = ["==symbol", "==name"]
    // 当数据库表中存在 id、created_at、updated_at 字段时使用
    ->filters(AllowedFilter::exact('symbol'), AllowedFilter::exact('name'))
    // 当数据库表中不存在 id、created_at、updated_at 字段时使用
    // ->allowedFilters(AllowedFilter::exact('symbol'), AllowedFilter::exact('name'))
    ->defaultSort('sort')
    ->allowedSorts(['id', 'sort', 'created_at'])
    ->pagex();
```

- 模糊查询过滤

当配置信息中 $config['fields']['filterable'] 配置的字段名前面没有加'=='时表示模糊查询过滤，在 QueryBuilder 的过滤写法中直接写出字段名，参考下面示例

- 示例

```php
use ApiElf\QueryBuilder\AllowedFilter;

return QueryBuilder::for(Currency::class, $request)
    // 配置示例 $config['fields']['filterable'] = ["symbol", "name"]
    // 当数据库表中存在 id、created_at、updated_at 字段时使用
    ->filters('symbol', 'name')
    // 当数据库表中不存在 id、created_at、updated_at 字段时使用
    // ->allowedFilters('symbol', 'name')
    ->defaultSort('sort')
    ->allowedSorts(['id', 'sort', 'created_at'])
    ->pagex();
```

## 需要创建的文件

请为我生成以下文件：

1. 管理端控制器 `app/Http/Admin/Controller/{Module}/{Entity}Controller.php`
2. （当配置中开启时）管理端资源类 `app/Http/Admin/Resource/{Module}/{Entity}Resource.php`
3. 管理端请求验证 `app/Http/Admin/Request/{Module}/{Entity}Request.php`
4. 管理端服务 `app/Http/Admin/Service/{Module}/{Entity}Service.php`
5. 用户端控制器 `app/Http/Api/Controller/{Module}/{Entity}Controller.php`
6. （当配置中开启时）用户端资源类 `app/Http/Api/Resource/{Module}/{Entity}Resource.php`
7. （当 endpoints 这项配置中包含 "create", "update" 时）用户端请求验证 `app/Http/Api/Request/{Module}/{Entity}Request.php`
8. 用户端服务 `app/Http/Api/Service/{Module}/{Entity}Service.php`
9. 如果有枚举字段，还需要创建对应的枚举类 `app/Model/Enums/{Module}/{EnumName}.php`
10. 复制一份[视图页面提示词模板](view_prompt_template.md)文档，重命名为：{Module}-view_prompt_template.md，然后根据模型文件中的字段信息和后端生成的接口以及相关功能尽可能的修改这份前端提示词文件中的”配置信息“部分的配置参数。特别注意：只修改”配置信息“部分的 json 配置，其他内容保持一致！

## 代码规范要求

- 所有 PHP 文件必须使用`<?php`标签开始，并添加`declare(strict_types=1);`
- 添加标准文件头注释，包含项目名称和描述
- 类名使用 PascalCase，方法名和属性名使用 camelCase
- 所有方法必须有返回类型声明
- 管理端接口必须使用三个中间件：AccessTokenMiddleware、PermissionMiddleware 和 OperationMiddleware
- 用户端接口必须使用 TokenMiddleware 中间件
- 使用 QueryBuilder 实现数据列表查询功能
- 所有新生成的 controller、service 类中的方法名严格参考标准模板[标准模板](app/AIGenerate/StandardTemplate.md)
- service 中的各方法实现代码遵循配置信息设置并严格参考标准模板中对应功能的代码进行实现[标准模板](app/AIGenerate/StandardTemplate.md)

请根据以上信息并认真参考这些文档进行开发：
[标准模板](app/AIGenerate/StandardTemplate.md)
[开发文档](app/AIGenerate/DevelopmentGuide.md)
