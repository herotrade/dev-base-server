<?php

declare(strict_types=1);

namespace App\Command\Tools;

use Hyperf\Command\Annotation\Command;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

#[Command]
class ControllerCommand extends GeneratorCommand
{
    public function __construct(protected ContainerInterface $container)
    {
        parent::__construct('x:controller');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('（之前写的待结合新版本优化）创建一个 Controller，支持设置注解路由前缀、鉴权中间件、基于模型的增、删、改、查（列表数据、单条明细数据）对应的接口');

        $this->addArgument('name', InputArgument::REQUIRED, '控制器名称，请携带模块名称及可能存在的Controller目录下的子目录名称，如：Api/User/UserController 表示生成的控制器位于 /app/http/Api/Controller/User 目录，命名空间为：App\Http\Api\Controller\User');
        $this->addOption('prefix', 'p', InputOption::VALUE_OPTIONAL, '路由前缀');
        $this->addOption('model_namespace', 'mn', InputOption::VALUE_OPTIONAL, '模型命名空间，设置已存在的模型的命名空间（如：App\\Model\\Strategy 或者 App/Model/Strategy）。设置此选项后自动生成 list、show、store、update、destroy 接口，并生成对应的 Model、Request、Service 类');
        $this->addOption('model', 'm', InputOption::VALUE_OPTIONAL, '接口操作的模型（如：User 当需要设置表名时：User:user），设置此选项后自动生成 list、show、store、update、destroy 接口，并生成对应的 Model、Request、Service 类');
        $this->addOption('module_model', 'mm', InputOption::VALUE_NONE, '是否将模型创建到模块下');
    }

    public function handle()
    {
        $model_namespace = $this->input->getOption('model_namespace');
        if ($model_namespace && str_contains($model_namespace, '/')) {
            $model_namespace = str_replace('/', '\\', $model_namespace);
        }

        $model = $this->input->getOption('model');
        $crud = false;

        // 控制器 name 可能携带子目录，需要过滤一下
        $name = $this->input->getArgument('name');

        if ($model_namespace) {
            $crud = true;
        }

        // 模型统一放到 app/Model 下
        if ($model) {
            if (str_contains($model, ':')) {
                [$model, $table] = explode(":", $model);
            }
            $model_name = $model;
            if ($this->input->getOption('module_model')) {
                $module = explode("/", $name)[0];
                $model_name = $module . '/' . $model;
            }
            $this->call('x:model', [
                'name' => $model_name,
                '--table' => $table
            ]);
            $crud = true;
        }

        // 生成控制器对应的 Request
        $r_name = str_replace('Controller', 'Request', $name);
        $params = [
            'name' => $r_name,
            '--crud' => $crud
        ];
        if ($crud) {
            if ($model_namespace) {
                $params['--model_namespace'] = $model_namespace;
            } else {
                $params['--model'] = $model;
                if ($this->input->getOption('module_model')) {
                    $params['--module_model'] = true;
                }
            }
        }
        $this->call('x:request', $params);

        // 生成控制器对应的 Service
        $s_name = str_replace('Controller', 'Service', $name);
        $params = [
            'name' => $s_name,
            '--crud' => $crud,
            '--request' => $r_name
        ];
        if ($crud) {
            if ($model_namespace) {
                $params['--model_namespace'] = $model_namespace;
            } else {
                $params['--model'] = $model;
                if ($this->input->getOption('module_model')) {
                    $params['--module_model'] = true;
                }
            }
        }
        $this->call('x:service', $params);
    }

    protected function getStub(): string
    {
        if (str_starts_with($this->input->getArgument('name'), 'Admin')) {
            if ($this->input->getOption('model_namespace') || $this->input->getOption('model')) {
                return __DIR__ . '/stubs/admin/controller-curd.stub';
            }
            return __DIR__ . '/stubs/admin/controller.stub';
        }
        if ($this->input->getOption('model_namespace') || $this->input->getOption('model')) {
            return __DIR__ . '/stubs/controller-curd.stub';
        }
        return __DIR__ . '/stubs/controller.stub';
    }

    protected function getDefaultNamespace(): string
    {
        $name = $this->input->getArgument('name');
        if (!str_contains($name, '/')) {
            throw new \Exception('请携带模块名称，如：Api/UserController');
        }

        $name_arr = explode("/", $name);
        $module = array_shift($name_arr);
        $namespace = 'App\\Http\\' . $module . '\\Controller';
        return $namespace;
    }

    protected function loadStubContent(string $stub_content): string
    {
        $name = $this->input->getArgument('name');
        // Request
        $r_name = str_replace('Controller', 'Request', $name);
        $name_arr = explode("/", $r_name);
        $module = array_shift($name_arr);
        $r_namespace_class = 'App\\Http\\' . $module . '\\Request' . '\\' . implode("\\", $name_arr);
        $request = end($name_arr);
        $request_class = $r_namespace_class;

        // Service
        $s_name = str_replace('Controller', 'Service', $name);
        $name_arr = explode("/", $s_name);
        $module = array_shift($name_arr);
        $service = '\\App\\Http\\' . $module . '\\Service' . '\\' . implode("\\", $name_arr);

        $serviceName = last($name_arr);

        // 路由前缀
        $firstPrefix = 'api/';
        if (str_starts_with($this->input->getArgument('name'), 'Admin')) {
            $firstPrefix = 'admin/';
        }
        if ($this->input->getOption('prefix')) {
            $firstPrefix = $firstPrefix . $this->input->getOption('prefix') . '/';
        }
        $prefix_n = str_replace('Service', '', $serviceName);
        $prefix_n = strtolower($prefix_n[0]) . substr($prefix_n, 1);
        $prefix = $firstPrefix . strtolower(array_shift($name_arr)) . '/' . $prefix_n;
        $stub_content = str_replace(
            ['%REQUESTCLASS%', '%REQUEST%', '%SERVICE%', '%SERVICECLASS%', '%PREFIX%'],
            [$request_class, $request, $service, $serviceName, $prefix],
            $stub_content
        );

        $model = $this->input->getOption('model');
        if ($model) {
            // 当设置了 model 参数时，自动创建 list、show、store、update、destroy 接口
            // %MODELCLASS%
            // %MODEL%
            if (str_contains($model, ':')) {
                [$model, $table] = explode(":", $model);
            }
            if ($this->input->getOption('module_model')) {
                $model_class = 'App\\' . $module . '\\Model\\' . $model;
            } else {
                $model_class = 'App\\Model\\' . $model;
            }

            $stub_content = str_replace(
                ['%MODELCLASS%', '%MODEL%'],
                [$model_class, $model],
                $stub_content
            );
        }
        return $stub_content;
    }
}
