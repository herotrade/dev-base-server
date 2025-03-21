# Hyperf CRUD 标准模板

## 目录结构

```
app/
├── Model/                          # 模型目录
│   └── {Module}/                   # 模块目录
│       └── {Entity}.php            # 实体模型
├── Http/
│   ├── Admin/                      # 管理端模块
│   │   ├── Controller/
│   │   │   └── {Module}/
│   │   │       └── {Entity}Controller.php  # 管理端控制器
│   │   ├── Request/
│   │   │   └── {Module}/
│   │   │       └── {Entity}Request.php     # 请求验证
│   │   ├── Resource/
│   │   │   └── {Module}/
│   │   │       └── {Entity}Resource.php    # 资源类
│   │   └── Service/
│   │       └── {Module}/
│   │           └── {Entity}Service.php     # 服务层
│   ├── Api/                        # 用户端模块
│   │   ├── Controller/
│   │   │   └── {Module}/
│   │   │       └── {Entity}Controller.php  # 用户端控制器
│   │   ├── Request/
│   │   │   └── {Module}/
│   │   │       └── {Entity}Request.php     # 请求验证
│   │   ├── Resource/
│   │   │   └── {Module}/
│   │   │       └── {Entity}Resource.php    # 资源类
│   │   └── Service/
│   │       └── {Module}/
│   │           └── {Entity}Service.php     # 服务层
│   └── Common/                     # 公共模块
│       ├── Controller/
│       │   └── AbstractController.php      # 抽象控制器
│       └── Resource/
│           └── BaseResource.php            # 基础资源类
└── QueryBuilder/                   # 查询构建器
    └── QueryBuilder.php            # 查询构建器
```

## 数据库迁移文件模板 `databases/migrations/{yyyy}_{mm}_{dd}_{hhiiss}_create_{table_name}_table.php`

```php
<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('{table_name}', function (Blueprint $table) {
            $table->bigIncrements('id');
            // 在此添加表字段
            $table->timestamps();
            // 在此添加索引
            $table->comment('{表注释}');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('{table_name}');
    }
};
```

## 模型模板 `app/Model/{Module}/{Entity}.php`

```php
<?php

declare(strict_types=1);
/**
 * 策略平台API
 * {实体}模型
 */

namespace App\Model\{Module};

use App\QueryBuilder\Model;
use Carbon\Carbon;

/**
 * @property int $id ID，主键
 * @property Carbon|null $created_at 创建时间
 * @property Carbon|null $updated_at 更新时间
 * {添加其他属性文档注释}
 */
final class {Entity} extends Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = '{table_name}';

    /**
     * The attributes that are mass assignable.
     */
    protected array $guarded = ['id', 'created_at', 'updated_at'];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = [
        'id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        // 添加其他字段的类型转换
    ];
}
```

## 管理端控制器模板 `app/Http/Admin/Controller/{Module}/{Entity}Controller.php`

```php
<?php

declare(strict_types=1);
/**
 * 策略平台API
 * {实体}管理控制器
 */

namespace App\Http\Admin\Controller\{Module};

use App\Http\Admin\Controller\AbstractController;
use App\Http\Admin\Resource\{Module}\{Entity}Resource;
use App\Http\Admin\Request\{Module}\{Entity}Request;
use App\Http\Admin\Service\{Module}\{Entity}Service;
use App\Http\Common\Result;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\DeleteMapping;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\HttpServer\Annotation\PutMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\Middleware;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Admin\Middleware\PermissionMiddleware;
use App\Http\Common\Middleware\OperationMiddleware;
use Mine\Access\Attribute\Permission;

#[Controller(prefix: 'admin/{module}')]
#[Middleware(middleware: AccessTokenMiddleware::class, priority: 100)]
#[Middleware(middleware: PermissionMiddleware::class, priority: 99)]
#[Middleware(middleware: OperationMiddleware::class, priority: 98)]
class {Entity}Controller extends AbstractController
{
    protected RequestInterface $request;

    public function __construct(
        private readonly {Entity}Service ${moduleService},
        RequestInterface $request
    ) {
        $this->request = $request;
        parent::__construct();
    }

    /**
     * 获取{实体}列表
     */
    #[GetMapping('list')]
    #[Permission(code: '{module}:index')]
    public function index(): Result
    {
        $result = $this->{moduleService}->list($this->request);
        // 根据配置决定是否使用资源类
        // return $this->success($result);
        return $this->success({Entity}Resource::collection($result));
    }

    /**
     * 获取{实体}详情
     */
    #[GetMapping('{id}')]
    #[Permission(code: '{module}:index')]
    public function show(int $id): Result
    {
        ${module} = $this->{moduleService}->show($id);
        return $this->success(${module});
    }

    /**
     * 创建{实体}
     */
    #[PostMapping('')]
    #[Permission(code: '{module}:create')]
    public function store({Entity}Request $request): Result
    {
        ${module} = $this->{moduleService}->create($request->validated());
        return $this->success(${module});
    }

    /**
     * 更新{实体}
     */
    #[PutMapping('{id}')]
    #[Permission(code: '{module}:update')]
    public function update(int $id, {Entity}Request $request): Result
    {
        ${module} = $this->{moduleService}->update($id, $request->validated());
        return $this->success(${module});
    }

    /**
     * 删除{实体}
     */
    #[DeleteMapping('{id}')]
    #[Permission(code: '{module}:delete')]
    public function destroy(int $id): Result
    {
        $this->{moduleService}->delete($id);
        return $this->success();
    }

    /**
     * 批量删除{实体}
     */
    #[DeleteMapping('batch/delete')]
    #[Permission(code: '{module}:delete')]
    public function batchDestroy(): Result
    {
        $ids = $this->request->input('ids', []);
        $del_count = $this->{moduleService}->batchDelete($ids);
        return $this->success([
            'deleted_count' => $del_count,
        ]);
    }
}
```

## 管理端请求验证模板 `app/Http/Admin/Request/{Module}/{Entity}Request.php`

```php
<?php

declare(strict_types=1);
/**
 * 策略平台API
 * {实体}请求验证类
 */

namespace App\Http\Admin\Request\{Module};

use Hyperf\Validation\Request\FormRequest;

class {Entity}Request extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    /**
     * 验证规则
     */
    public function rules(): array
    {
        $rules = [
            // 基本验证规则（自动从DDL生成）
            // 'name' => 'required|string|max:255',
            // 枚举字段验证示例
            // 'status' => ['required', Rule::in(array_column(\App\Model\Enums\{Module}\Status::cases(), 'value'))],
            // 布尔字段验证示例
            // 'is_visible' => 'required|boolean',
        ];

        // 更新操作时排除当前记录
        if ($this->isMethod('PUT')) {
            $id = $this->route('id');
            // 唯一性验证排除当前记录
            // $rules['unique_field'] = "required|unique:{table},unique_field,{$id}";
        }

        return $rules;
    }

    /**
     * 错误消息
     */
    public function messages(): array
    {
        return [
            // 定义错误消息
        ];
    }
}
```

## 管理端服务层模板 `app/Http/Admin/Service/{Module}/{Entity}Service.php`

```php
<?php

declare(strict_types=1);
/**
 * 策略平台API
 * {实体}服务类
 */

namespace App\Http\Admin\Service\{Module};

use App\Model\{Module}\{Entity};
use App\QueryBuilder\QueryBuilder;
use Hyperf\HttpServer\Contract\RequestInterface;
use Psr\Container\ContainerInterface;

class {Entity}Service
{
    public function __construct(private readonly ContainerInterface $container) {}

    /**
     * 获取{实体}列表
     */
    public function list(RequestInterface $request): mixed
    {
        return QueryBuilder::for({Entity}::class, $request)
            // 配置示例"filterable": ["=={过滤字段1}", "{过滤字段2}"]
            // 当数据库表中存在 id、created_at、updated_at 字段时使用
            ->filters(AllowedFilter::exact({过滤字段1}), {过滤字段2})
            // 当数据库表中不存在 id、created_at、updated_at 字段时使用
            // ->allowedFilters(AllowedFilter::exact({过滤字段1}), {过滤字段2})
            ->defaultSort('{默认排序字段}')
            ->allowedSorts([{可排序字段列表}])
            // 配置中 pagex:true 时
            ->pagex();
            // 配置中 pagex:false 时
            // ->page();
    }

    /**
     * 获取{实体}详情
     */
    public function show(int $id): {Entity}
    {
        return {Entity}::query()->findOrFail($id);
    }

    /**
     * 创建{实体}
     */
    public function create(array $data): {Entity}
    {
        return {Entity}::query()->create($data);
    }

    /**
     * 更新{实体}
     */
    public function update(int $id, array $data): {Entity}
    {
        ${module} = {Entity}::query()->findOrFail($id);
        ${module}->update($data);
        return ${module};
    }

    /**
     * 删除{实体}
     */
    public function delete(int $id): bool
    {
        ${module} = {Entity}::query()->findOrFail($id);
        return ${module}->delete();
    }

    /**
     * 批量删除{实体}
     */
    public function batchDelete(array $ids)
    {
        return {Entity}::query()->whereIn('id', $ids)->delete();
    }
}
```

## 用户端控制器模板 `app/Http/Api/Controller/{Module}/{Entity}Controller.php`

```php
<?php

declare(strict_types=1);
/**
 * 策略平台API
 * {实体}控制器
 */

namespace App\Http\Api\Controller\{Module};

use App\Http\Api\Middleware\TokenMiddleware;
use App\Http\Api\Resource\{Module}\{Entity}Resource;
use App\Http\Api\Service\{Module}\{Entity}Service;
use App\Http\Common\Controller\AbstractController;
use App\Http\Common\Result;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Contract\RequestInterface;

#[Controller(prefix: 'api/{module}')]
#[Middleware(middleware: TokenMiddleware::class, priority: 100)]
class {Entity}Controller extends AbstractController
{
    #[Inject]
    protected {Entity}Service ${moduleService};

    /**
     * 获取{实体}列表
     */
    #[GetMapping('list')]
    public function list(RequestInterface $request): Result
    {
        $result = $this->{moduleService}->list($request);
        // 根据配置决定是否使用资源类
        // return $this->success($result);
        return $this->success({Entity}Resource::collection($result));
    }
}
```

## 用户端资源类模板 `app/Http/Api/Resource/{Module}/{Entity}Resource.php`

```php
<?php

declare(strict_types=1);
/**
 * 策略平台API
 * {实体}资源类
 */

namespace App\Http\Api\Resource\{Module};

use App\Http\Common\Resource\BaseResource;

class {Entity}Resource extends BaseResource
{
    /**
     * 转换资源为数组
     */
    public function toArray(): array
    {
        // 单条资源
        // $this->resource;
        // 详细参考 Hyperf\Resource\Json\JsonResource

        return parent::toArray();
    }
}
```

## 用户端服务层模板 `app/Http/Api/Service/{Module}/{Entity}Service.php`

```php
<?php

declare(strict_types=1);
/**
 * 策略平台API
 * {实体}服务类
 */

namespace App\Http\Api\Service\{Module};

use App\Model\{Module}\{Entity};
use App\QueryBuilder\QueryBuilder;
use Hyperf\HttpServer\Contract\RequestInterface;
use Psr\Container\ContainerInterface;

class {Entity}Service
{
    public function __construct(private readonly ContainerInterface $container) {}

    /**
     * 获取{实体}列表
     */
    public function list(RequestInterface $request): mixed
    {
        return QueryBuilder::for({Entity}::class, $request)
            // 配置示例"filterable": ["=={过滤字段1}", "{过滤字段2}"]
            // 当数据库表中存在 id、created_at、updated_at 字段时使用
            ->filters(AllowedFilter::exact({过滤字段1}), {过滤字段2})
            // 当数据库表中不存在 id、created_at、updated_at 字段时使用
            // ->allowedFilters(AllowedFilter::exact({过滤字段1}), {过滤字段2})
            ->defaultSort('{默认排序字段}')
            ->allowedSorts([{可排序字段列表}])
            // 配置中 pagex:true 时
            ->pagex();
            // 配置中 pagex:false 时
            // ->page();
    }
}
```
