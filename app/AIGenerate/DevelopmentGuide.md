# Hyperf CRUD 开发指南

## 1. 概述

本指南结合标准模板和配置文件，为使用 Hyperf 框架开发 CRUD 功能提供详细的步骤和注意事项。开发过程中请参考。

## 2. 开发流程

### 2.1 准备数据库 DDL

1. 准备数据库表的 DDL 语句
2. DDL 应包含字段名、类型、约束、注释等完整信息
3. 确保 DDL 中包含创建时间和更新时间字段
4. 对于枚举字段，在注释中清晰说明可选值（例如："状态：1-正常，2-禁用"）
5. 对于布尔字段，在注释中说明只有 0 和 1 两个值

### 2.2 创建配置文件

1. 基于`PromptTemplate.md`中的配置模板创建配置数组
2. 填写实体信息、数据库 DDL、验证规则等
3. 指定可过滤字段、可排序字段和默认排序
4. 配置 API 接口参数
5. 对于特殊的枚举字段，可以在`enums`部分手动配置

### 2.3 生成代码文件

1. 使用 AI 根据配置生成迁移文件、模型、控制器等代码
2. 审查生成的代码并进行必要的调整
3. 安装代码到相应目录

### 2.4 验证功能

1. 运行迁移命令创建数据库表
2. 测试 API 接口功能
3. 进行必要的调整和完善

## 3. 使用配置文件生成代码

简化后的配置文件（`ConfigurationTemplate.json`）充分利用数据库 DDL 中的信息，只需要配置少量额外参数。

### 3.1 配置文件结构说明

```json
{
  "entity": {
    "name": "实体名称",
    "module": "模块名称",
    "variableName": "实体变量名(小驼峰)"
  },
  "table": {
    "ddl": "数据库表的DDL语句"
  },
  "fields": {
    "filterable": ["=可过滤字段1", "可过滤字段2"],
    "sortable": ["可排序字段1", "可排序字段2"],
    "defaultSort": "默认排序字段",
    "validation": {
      "字段名1": {
        "create": "创建时验证规则",
        "update": "更新时验证规则或使用{id}占位符",
        "message": "验证错误消息"
      }
    }
  },
  "api": {
    "admin": {
      "enabled": true,
      "permission": {
        "prefix": "权限前缀"
      }
    },
    "user": {
      "enabled": true,
      "endpoints": ["list", "detail", "create", "update", "delete"]
    }
  }
}
```

### 3.2 基于币种模块的配置示例

```json
{
  "entity": {
    "name": "Currency",
    "module": "Currency",
    "variableName": "currency"
  },
  "table": {
    "ddl": "CREATE TABLE `currency` (\n  `id` bigint unsigned NOT NULL AUTO_INCREMENT,\n  `name` json NOT NULL COMMENT '币种名称（支持多语言）',\n  `symbol` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '币种代码',\n  `icon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '币种图标',\n  `sort` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT '排序',\n  `decimals` int NOT NULL DEFAULT '2' COMMENT '精度',\n  `created_at` timestamp NULL DEFAULT NULL,\n  `updated_at` timestamp NULL DEFAULT NULL,\n  PRIMARY KEY (`id`),\n  UNIQUE KEY `symbol_unique` (`symbol`)\n) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='交易对表';"
  },
  "fields": {
    "filterable": ["=symbol", "name"],
    "sortable": ["id", "sort", "created_at"],
    "defaultSort": "sort",
    "validation": {
      "name": {
        "create": "required|array",
        "update": "required|array",
        "message": "币种名称不能为空"
      },
      "symbol": {
        "create": "required|string|max:20|unique:currency,symbol",
        "update": "required|string|max:20|unique:currency,symbol,{id}",
        "message": "币种代码不能为空"
      }
    }
  },
  "api": {
    "admin": {
      "enabled": true,
      "permission": {
        "prefix": "currency"
      }
    },
    "user": {
      "enabled": true,
      "endpoints": ["list"]
    }
  }
}
```

### 3.3 使用 AI 生成代码

通过向 AI 提供配置文件，可以快速生成符合项目规范的 CRUD 代码。步骤如下：

1. 准备数据库表的 DDL 语句
2. 创建符合项目规范的配置文件（JSON 格式）
3. 向 AI 提供配置文件，让其生成迁移文件、模型、控制器、服务层等代码
4. 检查生成的代码并进行必要的调整
5. 运行迁移命令创建数据库表
6. 测试 API 接口功能

## 4. QueryBuilder 的使用

### 4.1 常用方法

- `allowedFilters()`：定义可过滤字段（组件中默认的定义可过滤字段的方法）
- `filters()`：定义可过滤字段（基于组件中 allowedFilters 扩展的一个方法，自带对 id、created_at、updated_at 相关的过滤条件）（当数据库表中存在 id、created_at、updated_at 字段时使用）
- `defaultSort()`：设置默认排序字段
- `allowedSorts()`：设置允许排序的字段
- `page()`：启用分页功能（强制分页）(配置中 pagex:false)
- `pagex()`：启用分页功能（支持通过 query 参数 page_size 设置为 -1 获取所有记录（当一个接口既有分页展示场景又有不分页展示场景如需要作为下拉选择的数据时））(配置中 pagex:true)
- 更多功能参考文档 vendor/apielf/hyperf-query-builder/README.md
- 特别说明，过滤条件配置中支持配置精确查询过滤字段。配置方式为：filterable": ["==symbol"]
  - 当配置为 ==symbol 时表示需要精确查询过滤，当配置为 symbol 时表示通过模糊查询过滤

### 4.2 查询示例

```php
use ApiElf\QueryBuilder\AllowedFilter;

return QueryBuilder::for(Currency::class, $request)
    // 配置示例"filterable": ["==symbol", "name"]
    // 当数据库表中存在 id、created_at、updated_at 字段时使用
    ->filters(AllowedFilter::exact('symbol'), 'name')
    // 当数据库表中不存在 id、created_at、updated_at 字段时使用
    // ->allowedFilters(AllowedFilter::exact('symbol'), 'name')
    ->defaultSort('sort')
    ->allowedSorts(['id', 'sort', 'created_at'])
    // 配置中 pagex:true 时
    ->pagex();
    // 配置中 pagex:false 时
    // ->page();
```

### 4.3 请求参数

客户端可以通过 URL 参数控制查询行为：

- 过滤：`?filter[字段名]=值`
- 排序：`?sort=字段名` 或 `?sort=-字段名`（降序）
- 分页：`?page=1&per_page=15`
- 包含关系：`?include=关系名`

## 5. AI 代码生成的优势

### 5.1 从 DDL 提取信息

AI 能够从 DDL 语句中提取大量信息，包括：

- 表名和表注释
- 字段名、类型和注释
- 默认值和约束条件
- 主键和索引信息
- 枚举值和布尔字段的信息

这些信息用于生成：

- 模型属性和 PHPDoc 注释
- 类型转换设置
- 迁移文件代码
- 基本的验证规则

### 5.2 配置补充信息

配置文件中只需要补充 DDL 中无法直接获取的信息：

- 实体名称和模块信息
- 可过滤和可排序字段的设置
- 复杂的验证规则
- API 接口配置和权限信息
- 特殊的枚举字段配置（如果自动识别不准确）

## 6. 特殊字段处理

### 6.1 枚举字段

对于表示有限选项的字段（如状态、类型等），建议使用枚举：

1. 在数据库中使用 tinyint 类型
2. 在字段注释中清晰说明可选值（例如："状态：1-正常，2-禁用"）
3. 系统将自动创建对应的枚举类
4. 在模型中自动添加类型转换

枚举字段的 DDL 格式示例：

```sql
`status` tinyint NOT NULL DEFAULT '1' COMMENT '状态：1-正常，2-禁用',
```

生成的模型类型转换：

```php
protected array $casts = [
    'status' => \App\Model\Enums\Module\Status::class,
];
```

生成的验证规则：

```php
use Hyperf\Validation\Rule;

'status' => ['required', Rule::in(array_column(\App\Model\Enums\Module\Status::cases(), 'value'))],
```

### 6.2 布尔字段

对于表示是/否的字段：

1. 在数据库中使用 tinyint 类型
2. 在字段注释中说明只有 0 和 1 两个值（例如："是否显示：0-否，1-是"）
3. 系统将自动处理为布尔类型
4. 在模型中自动添加`'字段名' => 'boolean'`的类型转换

布尔字段的 DDL 格式示例：

```sql
`is_visible` tinyint NOT NULL DEFAULT '1' COMMENT '是否显示：0-否，1-是',
```

生成的模型类型转换：

```php
protected array $casts = [
    'is_visible' => 'boolean',
];
```

生成的验证规则：

```php
'is_visible' => 'required|boolean',
```

### 6.3 枚举类示例

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

### 6.4 手动配置枚举字段

如果 AI 无法正确从 DDL 中解析枚举值，您可以在配置中手动指定：

```php
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
```

这将覆盖 AI 从 DDL 中提取的枚举信息。

## 7. 注意事项

### 7.1 代码规范

- 所有 PHP 文件必须遵循项目的代码风格和命名规范
- 控制器方法应返回统一的结果格式（Result 对象）
- 控制器只负责请求处理和响应返回，不包含业务逻辑
- 服务层负责业务逻辑处理
- 模型负责数据存储和关系定义

### 7.2 权限控制

- 管理端接口必须添加 AccessTokenMiddleware、PermissionMiddleware 和 OperationMiddleware 中间件
- 管理端接口必须使用 Permission 注解控制操作权限
- 用户端接口必须添加 TokenMiddleware 中间件进行身份验证

### 7.3 验证规则

- 所有请求必须进行适当的验证
- 更新操作时要排除当前记录（避免唯一性冲突）
- 对于数组类型的字段（如 JSON）使用 array 验证规则

### 7.4 命名规范

- 类名使用 PascalCase（如 UserProfile）
- 方法名和变量名使用 camelCase（如 getUserProfile）
- 表名和字段名使用 snake_case（如 user_profile）
- 常量使用 SCREAMING_SNAKE_CASE（如 MAX_LOGIN_ATTEMPTS）

## 8. 总结

通过结合数据库 DDL 和简化的配置文件，AI 可以快速生成符合项目规范的 CRUD 代码，并能智能处理枚举和布尔等特殊字段，大幅提高开发效率。开发人员只需准备 DDL 和简单配置，就能获得完整的功能模块，包括数据迁移、模型、控制器、验证和服务层代码。
