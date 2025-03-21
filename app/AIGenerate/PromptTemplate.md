# Hyperf CRUD 代码生成提示词模板

我需要您帮我基于 Hyperf 框架为以下数据库表创建完整的 CRUD 功能代码。请遵循我们项目的标准开发规范。

## 数据库表 DDL

```sql
{在此粘贴您的数据库表 DDL}
```

## 配置信息

请使用以下 PHP 配置数组来生成代码（已包含详细注释说明每个配置项的作用）：

```php
$config = [
    // 实体信息配置
    'entity' => [
        // 实体名称，使用大驼峰(PascalCase)命名法，对应模型类名
        'name' => '实体名称，例如：Currency',
        // 模块名称，通常与实体名一致或表示功能模块，用于目录结构
        'module' => '模块名称，例如：Currency',
        // 实体变量名，使用小驼峰(camelCase)命名法，用于控制器和服务类中
        'variableName' => '实体变量名，例如：currency',
    ],

    // 字段相关配置
    'fields' => [
        // 可过滤字段列表，这些字段将在QueryBuilder中通过filters方法配置
        // 前端可以通过?filter[field]=value参数过滤数据。配置说明：name 表示模糊查询，==name 表示精确查询
        'filterable' => ['需要支持过滤的字段名，例如：symbol', '==name'],

        // 可排序字段列表，这些字段将在QueryBuilder中通过allowedSorts方法配置
        // 前端可以通过?sort=field或?sort=-field(降序)参数排序数据
        'sortable' => ['需要支持排序的字段，例如：id', 'sort', 'created_at'],

        // 默认排序字段，将在QueryBuilder中通过defaultSort方法配置
        'defaultSort' => '默认排序字段，例如：sort',

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

        // 枚举字段配置（可选）
        // 如果表中有枚举字段，可以在此配置生成对应的枚举类
        'enums' => [
            // 字段名 => 枚举配置
            // '字段名' => [
            //     'name' => '枚举类名，例如：Status',  // 不需要包含Enum后缀
            //     'values' => [  // 枚举值定义
            //         1 => 'NORMAL',  // 值 => 枚举名称（大写）
            //         2 => 'DISABLED',
            //     ],
            //     'messages' => [  // 枚举值对应的消息（可选）
            //         1 => '正常',
            //         2 => '禁用',
            //     ],
            // ],
        ],
    ],

    // API接口配置
    'api' => [
        // 管理端API配置
        'admin' => [
            // 是否启用管理端API
            'enabled' => true,
            // 权限配置
            'permission' => [
                // 权限前缀，用于Permission注解中，如"entity:index"、"entity:create"等
                'prefix' => '权限前缀，例如：currency',
            ],
            // 【资源类】是否生成对应的 Resource 文件（当从数据库中查询出来的列表数据需要对数据结构进行调整时请使用 API 资源构造器进行处理）
            'resource' => true,
            // 分页是否支持通过 query 参数 page_size 设置为 -1 获取所有记录
            'pagex' => true,
        ],

        // 用户端API配置
        'user' => [
            // 是否启用用户端API
            'enabled' => true,
            // 需要创建的用户端接口列表
            // 可选值: "list", "detail", "create", "update", "delete"
            'endpoints' => ['需要暴露的用户端接口，例如：list', 'detail'],
            // 【资源类】是否生成对应的 Resource 文件（当从数据库中查询出来的列表数据需要对数据结构进行调整时请使用 API 资源构造器进行处理）
            'resource' => true,
            // 分页是否支持通过 query 参数 page_size 设置为 -1 获取所有记录
            'pagex' => true,
        ],
    ],
];
```

## 验证规则自动生成说明

我将根据 DDL 自动生成基本验证规则，规则如下：

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
9. 对于 tinyint 类型：
   - 如果注释中说明了只有 0 和 1 的可选值，会视为布尔类型，添加`boolean`验证，并在模型中添加类型转换`'字段名' => 'boolean'`
   - 如果注释中说明了多个可选值（例如：1-正常，2-禁用），会创建相应的枚举类，并添加`Rule::in(array_column({枚举类}::cases(), 'value'))`验证

请在`customValidation`中只添加 DDL 无法表达的特殊验证规则，如：

- 数组验证：`array`
- 邮箱验证：`email`
- URL 验证：`url`
- 正则表达式验证：`regex:/pattern/`
- 自定义验证规则

## 过滤配置说明

配置中支持配置精确查询过滤和模糊查询过滤。==name 表示精确查询过滤，name 表示模糊查询过滤

- 精确查询过滤示例

```php
use ApiElf\QueryBuilder\AllowedFilter;

return QueryBuilder::for(Currency::class, $request)
    // 配置示例"filterable": ["==symbol", "==name"]
    // 当数据库表中存在 id、created_at、updated_at 字段时使用
    ->filters(AllowedFilter::exact('symbol'), AllowedFilter::exact('name'))
    // 当数据库表中不存在 id、created_at、updated_at 字段时使用
    // ->allowedFilters(AllowedFilter::exact('symbol'), AllowedFilter::exact('name'))
    ->defaultSort('sort')
    ->allowedSorts(['id', 'sort', 'created_at'])
    ->pagex();
```

- 模糊查询过滤示例

```php
use ApiElf\QueryBuilder\AllowedFilter;

return QueryBuilder::for(Currency::class, $request)
    // 配置示例"filterable": ["symbol", "name"]
    // 当数据库表中存在 id、created_at、updated_at 字段时使用
    ->filters('symbol', 'name')
    // 当数据库表中不存在 id、created_at、updated_at 字段时使用
    // ->allowedFilters('symbol', 'name')
    ->defaultSort('sort')
    ->allowedSorts(['id', 'sort', 'created_at'])
    ->pagex();
```

## 特殊字段处理说明

### 枚举字段处理

对于包含枚举值的字段（通常是 tinyint 类型，且注释中说明了多个可选值），我会：

1. 创建对应的枚举类，保存在`app/Model/Enums/{Module}/{EnumName}.php`
2. 枚举类使用 PHP 8.1+ 原生枚举特性并继承 Hyperf 的 EnumConstantsTrait
3. 在验证规则中添加`Rule::in(array_column({枚举类}::cases(), 'value'))`
4. 在模型中添加类型转换`'字段名' => {EnumName}::class`
5. 项目中涉及到使用枚举值时不能在代码中硬编码具体的值，必须使用 {EnumName}::{对应属性} 获取

### 布尔字段处理

对于布尔类型字段（通常是 tinyint 类型，且只有 0/1 两个值），我会：

1. 在验证规则中添加`boolean`验证
2. 在模型中添加类型转换`'字段名' => 'boolean'`

## 需要创建的文件

请为我生成以下文件：

1. 数据库迁移文件 `databases/migrations/{timestamp}_create_{table_name}_table.php`
2. 模型文件 `app/Model/{Module}/{Entity}.php`
3. 管理端控制器 `app/Http/Admin/Controller/{Module}/{Entity}Controller.php`
4. （当配置中开启时）管理端资源类 `app/Http/Admin/Resource/{Module}/{Entity}Resource.php`
5. 管理端请求验证 `app/Http/Admin/Request/{Module}/{Entity}Request.php`
6. 管理端服务 `app/Http/Admin/Service/{Module}/{Entity}Service.php`
7. 用户端控制器 `app/Http/Api/Controller/{Module}/{Entity}Controller.php`
8. （当配置中开启时）用户端资源类 `app/Http/Api/Resource/{Module}/{Entity}Resource.php`
9. （当 endpoints 这项配置中包含 "create", "update" 时）用户端请求验证 `app/Http/Api/Request/{Module}/{Entity}Request.php`
10. 用户端服务 `app/Http/Api/Service/{Module}/{Entity}Service.php`
11. 如果有枚举字段，还需要创建对应的枚举类 `app/Model/Enums/{Module}/{EnumName}.php`
12. 参考[视图页面提示词模板](view_prompt_template.md)生成一份新的视图页面提示词文件，要求根据数据库表 DDL 和后端生成的接口以及相关功能尽可能的完善这份前端提示词文件中的配置信息

## 代码规范要求

- 所有 PHP 文件必须使用`<?php`标签开始，并添加`declare(strict_types=1);`
- 添加标准文件头注释，包含项目名称和描述
- 类名使用 PascalCase，方法名和属性名使用 camelCase
- 所有方法必须有返回类型声明
- 管理端接口必须使用三个中间件：AccessTokenMiddleware、PermissionMiddleware 和 OperationMiddleware
- 用户端接口必须使用 TokenMiddleware 中间件
- 使用 QueryBuilder 实现数据列表查询功能

## 枚举类示例

```php
<?php

declare(strict_types=1);
/**
 * 策略平台API
 * 用户状态枚举
 */

namespace App\Model\Enums\User;

use Hyperf\Constants\Annotation\Constants;
use Hyperf\Constants\Annotation\Message;
use Hyperf\Constants\EnumConstantsTrait;

#[Constants]
enum Status: int
{
    use EnumConstantsTrait;

    #[Message('user.enums.status.1')]
    case NORMAL = 1;

    #[Message('user.enums.status.2')]
    case DISABLED = 2;
}
```

## 币种模块示例配置（可根据需要参考）

```php
$config = [
    'entity' => [
        'name' => 'Currency',
        'module' => 'Currency',
        'variableName' => 'currency',
    ],
    'fields' => [
        'filterable' => ['==symbol', 'name'],
        'sortable' => ['id', 'sort', 'created_at'],
        'defaultSort' => 'sort',
        'customValidation' => [
            'name' => [
                'rules' => 'array',
                'message' => '币种名称格式不正确',
            ],
            'sort' => [
                'rules' => 'numeric|min:0',
                'message' => '排序必须为非负数',
            ],
            'decimals' => [
                'rules' => 'integer|min:0|max:18',
                'message' => '精度必须为0-18之间的整数',
            ],
        ],
        'enums' => [
            'status' => [
                'name' => 'Status',
                'values' => [
                    1 => 'NORMAL',
                    2 => 'DISABLED',
                ],
                'messages' => [
                    1 => '正常',
                    2 => '禁用',
                ],
            ],
        ],
    ],
    'api' => [
        'admin' => [
            'enabled' => true,
            'permission' => [
                'prefix' => 'currency',
            ],
            'resource' => true,
        ],
        'user' => [
            'enabled' => true,
            'endpoints' => ['list'],
            'resource' => true,
        ],
    ],
];
```
