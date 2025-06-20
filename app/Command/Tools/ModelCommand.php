<?php

declare(strict_types=1);

namespace App\Command\Tools;

use Hyperf\Command\Annotation\Command;
use Hyperf\Database\Schema\Schema;
use Hyperf\DbConnection\Db;
use Hyperf\Stringable\Str;
use Hyperf\Stringable\StrCache;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

#[Command]
class ModelCommand extends GeneratorCommand
{
    public function __construct(protected ContainerInterface $container)
    {
        parent::__construct('x:model');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('创建一个 Model，支持自动设置模型的 table、fillable、casts 属性（当表名称不符合通过模型名称标准转化时需要指定表名称）');

        $this->addArgument('name', InputArgument::REQUIRED, '模型名称，请携带模块名称');
        $this->addOption('table', 't', InputOption::VALUE_OPTIONAL, '模型对应的表名，当表名未按照规则命名时需要指定');
    }

    public function handle()
    {
        //
    }

    protected function getStub(): string
    {
        return __DIR__ . '/stubs/model.stub';
    }

    protected function getDefaultNamespace(): string
    {
        $name = $this->input->getArgument('name');
        if (!str_contains($name, '/')) {
            return $this->getConfig()['namespace'] ?? 'App\Model';
        }

        $name_arr = explode("/", $name);
        $module = array_shift($name_arr);
        $namespace = 'App\\' . $module . '\\Model';
        return $namespace;
    }

    protected function loadStubContent(string $stub_content): string
    {
        // 连接数据库并获取表结构
        $database = env("DB_DATABASE");
        $table = $this->input->getOption('table');
        if (empty($table)) {
            // laravel 根据模型名称自动获取表名
            $table = StrCache::snake(Str::pluralStudly($this->input->getArgument('name')));
        }

        $table_structure = Db::select("SELECT COLUMN_NAME AS `Field`, COLUMN_TYPE AS `Type`, COLUMN_COMMENT AS `Annotation` FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '{$database}' AND TABLE_NAME = '{$table}';");

        $casts_type_arr = [
            'int' => 'integer',
            'char' => 'string',
            'json' => 'array',
            'text' => 'string',
            'time' => 'datetime',
            'date' =>  'datetime',
            'decimal' => 'decimal', // 还需要设置小数位数
        ];

        $fillable = [];
        $casts = [];
        $annotation_arr = [];
        foreach ($table_structure as $value) {
            array_push($fillable, $value->Field);
            $annotation_arr[$value->Field] = $value->Annotation;
            $type = '';
            foreach ($casts_type_arr as $key => $casts_type) {

                if (str_contains($value->Type, $key)) {
                    if (
                        $key == 'decimal'
                    ) {
                        [$t, $f] = explode('(', str_replace(')', '', $value->Type));
                        [$l, $p] = explode(",", $f);
                        $type = $key . ":" . $p;
                    } else {
                        $type = $casts_type;
                    }
                }
            }

            if ($type) {
                $casts[$value->Field] = $type;
            }
        }

        $fillable_str = '[';
        $casts_str = '[';
        foreach ($fillable as $key => $value) {
            $fillable_str .= PHP_EOL . "        '{$value}', // {$annotation_arr[$value]}";
        }
        $fillable_str = $fillable_str . PHP_EOL . '    ]';

        foreach ($casts as $key => $value) {
            $casts_str .= PHP_EOL . "        '{$key}' => '{$value}',";
        }
        $casts_str = $casts_str . PHP_EOL . '    ]';

        $stub_content = str_replace(
            ['%TABLE%', '%FILLABLE%', '%CASTS%'],
            [$table, $fillable_str, $casts_str],
            $stub_content
        );

        return $stub_content;
    }
}
