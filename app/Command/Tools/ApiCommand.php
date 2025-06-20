<?php

declare(strict_types=1);

namespace App\Command\Tools;

use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Command\Annotation\Command;
use Psr\Container\ContainerInterface;

#[Command]
class ApiCommand extends HyperfCommand
{
    public function __construct(protected ContainerInterface $container)
    {
        parent::__construct('x:api');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('创建一个（组） Api 接口需要的控制器、验证器、模型、service');
    }

    public function handle()
    {
        $model = '';
        $controller = $this->ask('输入控制器名称（请携带模块名称及可能存在的Controller目录下的子目录名称，如：Api/User/UserController 表示生成的控制器位于 /app/Http/Api/Controller/User 目录，命名空间为：App\Http\Api\Controller\User）：');
        $model_namespace = $this->ask('【选填】输入模型已经存在的模型命名空间名称（如：App\\Model\\Strategy 或者 App/Model/Strategy）（填写后将自动生成实现对模型的增、删、改、查（列表数据、单条明细数据）对应的接口）：');
        if (empty($model_namespace)) {
            $model = $this->ask('【选填】输入模型名称（填写模型名称后将自动生成实现对模型的增、删、改、查（列表数据、单条明细数据）对应的接口）：');
            $table = '';
            if (!empty($model)) {
                $table = $this->ask('输入模型对应的表名称（如果表名称未按照模型名称对应的表名规则命名时需要设置）：');
                $module_model = $this->ask('是否将模型创建到模块下，默认创建到 app/Model 下（0：否（默认），1：是）：', '0');
            }
        }
        $prefix = $this->ask('【选填】输入路由前缀：');

        $params = [
            'name' => $controller,
            '--prefix' => $prefix
        ];

        if (!empty($model_namespace)) {
            $params['--model_namespace'] = $model_namespace;
        }

        if (!empty($model)) {
            $params['--model'] = $model;
            if (!empty($table)) {
                $params['--model'] = $model . ':' . $table;
            }
            if (!empty($module_model)) {
                $params['--module_model'] = true;
            }
        }
        $this->call('x:controller', $params);
    }
}
