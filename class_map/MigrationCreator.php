<?php
declare(strict_types=1);

/**
 * MigrationCreator.php
 * Author:chenmaq (machen7408@gmail.com)
 * Contact:tg:@chenmaq
 * Version:1.0
 * Date:2025/4/27
 * Website:algoquant.org
 */

namespace Hyperf\Database\Migrations;

use Closure;
use Exception;
use Hyperf\Stringable\Str;
use Hyperf\Support\Filesystem\Filesystem;
use InvalidArgumentException;
use Hyperf\DbConnection\Db;

class MigrationCreator
{
    /**
     * The registered post create hooks.
     */
    protected array $postCreate = [];

    /**
     * Create a new migration creator instance.
     */
    public function __construct(protected Filesystem $files)
    {
    }

    /**
     * Create a new migration at the given path.
     *
     * @throws Exception
     */
    public function create(string $name, string $path, ?string $table = null, bool $create = false): string
    {
        $path = sprintf('%s/%s', $path, $table);
        //重写path 按数据表创建目录隔离方便维护
        $this->ensureMigrationDoesntAlreadyExist($name, $path);
        $stub = $this->getStub($table, $create);

        if (! file_exists($path)) {
            mkdir($path, 0755, true);
        }
        $this->files->put(
            $path = $this->getPath($name, $path),
            $this->populateStub($stub, $table, $create)
        );
        $this->firePostCreateHooks($table);

        return $path;
    }

    /**
     * Register a post migration create hook.
     */
    public function afterCreate(Closure $callback)
    {
        $this->postCreate[] = $callback;
    }

    /**
     * Get the path to the stubs.
     */
    public function stubPath(): string
    {
        return __DIR__ . '/stubs';
    }

    /**
     * Get the filesystem instance.
     */
    public function getFilesystem(): Filesystem
    {
        return $this->files;
    }

    /**
     * Ensure that a migration with the given name doesn't already exist.
     *
     * @throws InvalidArgumentException
     */
    protected function ensureMigrationDoesntAlreadyExist(string $name, ?string $migrationPath = null)
    {
        if (! empty($migrationPath)) {
            $migrationFiles = $this->files->glob($migrationPath . '/*.php');

            foreach ($migrationFiles as $migrationFile) {
                $this->files->requireOnce($migrationFile);
            }
        }

        if (class_exists($className = $this->getClassName($name))) {
            throw new InvalidArgumentException("A {$className} class already exists.");
        }
    }

    /**
     * Get the migration stub file.
     */
    protected function getStub(?string $table, bool $create): string
    {
        if (is_null($table)) {
            return $this->files->get($this->stubPath() . '/blank.stub');
        }

        // We also have stubs for creating new tables and modifying existing tables
        // to save the developer some typing when they are creating a new tables
        // or modifying existing tables. We'll grab the appropriate stub here.
        $stub = $create ? 'create.stub' : 'update.stub';

        return $this->files->get($this->stubPath() . "/{$stub}");
    }

    /**
     * Populate the place-holders in the migration stub.
     */
    protected function populateStub(string $stub, ?string $table, bool $create = false): string
    {
        if (!is_null($table)) {
            $stub = str_replace('DummyTable', $table, $stub);
            if (!$create && $this->tableExists($table)) {
                $schemaCode = $this->generateSchemaCode($table);
                $upPlaceholder = 'REPLACE_UP';
                $stub = str_replace($upPlaceholder, $schemaCode['up'], $stub);

                $downPlaceholder = 'REPLACE_DOWN';
                $stub = str_replace($downPlaceholder, $schemaCode['down'], $stub);
            } elseif ($create && $this->tableExists($table)) {
                $schemaCode = $this->generateCreateTableSchemaCode($table);
                $upPlaceholder = 'REPLACE_CODE';
                $stub = str_replace($upPlaceholder, $schemaCode['up'], $stub);
            }
        }
        return $stub;
    }
    
    /**
     * 检查表是否存在
     */
    protected function tableExists(string $table): bool
    {
        try {
            return true;// Db::getSchemaBuilder()->hasTable($table);
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * 解析迁移文件内容，提取列和索引信息
     */
    protected function parseMigrationContent(string $content, string $table): array
    {
        $columns = [];
        $indexes = [];
        $tableInfo = [
            'charset' => '',
            'collation' => '',
            'engine' => '',
            'comment' => '',
        ];
        
        // 提取up方法中的Schema::create部分
        if (preg_match('/public\s+function\s+up\(\).*?Schema::create\s*\(\s*[\'"]' . $table . '[\'"].*?function\s*\(\s*Blueprint\s+\$table\s*\)\s*{(.*?)}\s*\)\s*;/s', $content, $matches)) {
            $schemaContent = $matches[1];
            
            // 首先提取表级别的设置（charset, collation, engine, comment等）
            if (preg_match('/\$table->charset\s*=\s*[\'"]([^\'"]+)[\'"]/s', $schemaContent, $charsetMatches)) {
                $tableInfo['charset'] = $charsetMatches[1];
            }
            
            if (preg_match('/\$table->collation\s*=\s*[\'"]([^\'"]+)[\'"]/s', $schemaContent, $collationMatches)) {
                $tableInfo['collation'] = $collationMatches[1];
            }
            
            if (preg_match('/\$table->engine\s*=\s*[\'"]([^\'"]+)[\'"]/s', $schemaContent, $engineMatches)) {
                $tableInfo['engine'] = $engineMatches[1];
            }
            
            if (preg_match('/\$table->comment\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\)/s', $schemaContent, $commentMatches)) {
                $tableInfo['comment'] = $commentMatches[1];
            }
            
            // 过滤掉表级别的设置行，以免干扰后续列解析
            $schemaContent = preg_replace('/\$table->(charset|collation|engine)\s*=.*?;/s', '', $schemaContent);
            $schemaContent = preg_replace('/\$table->comment\s*\(.*?\);/s', '', $schemaContent);
            
            // 检查是否有timestamps方法调用
            $hasTimestamps = preg_match('/\$table->timestamps\(\);/s', $schemaContent);
            // 过滤掉timestamps行
            $schemaContentWithoutTimestamps = preg_replace('/\$table->timestamps\(\);/s', '', $schemaContent);
            
            // 提取自增ID列定义 (bigIncrements, increments)
            preg_match_all('/\$table->(bigIncrements|increments)\(\'([^\']+)\'(?:,\s*([^)]+))?\)(?:->[^;]+)?;/s', $schemaContentWithoutTimestamps, $incrementMatches, PREG_SET_ORDER);
            
            foreach ($incrementMatches as $match) {
                $method = $match[1];
                $field = $match[2];
                $definition = $match[0];
                
                // 提取注释
                $comment = '';
                if (preg_match('/->comment\(\'([^\']+)\'\)/', $definition, $commentMatch)) {
                    $comment = $commentMatch[1];
                }
                
                // 根据方法类型确定列类型
                $type = $method === 'bigIncrements' ? 'bigint(20)' : 'int(10)';
                
                $columns[] = [
                    'Field' => $field,
                    'Type' => $type,
                    'Null' => 'NO',
                    'Default' => null,
                    'Comment' => $comment,
                    'Extra' => 'auto_increment',
                ];
                
                // 添加主键信息
                $indexes[] = [
                    'Key_name' => 'PRIMARY',
                    'Column_name' => $field,
                    'Non_unique' => 0,
                ];
            }
            
            // 提取其他列定义，排除timestamps方法、表设置及主键定义
            preg_match_all('/\$table->(?!(?:bigIncrements|increments|primary|index|unique|timestamps|charset|collation|engine|comment))([^(]+)\(\'([^\']+)\'(?:,\s*([^)]+))?\)(?:->[^;]+)?;/s', $schemaContentWithoutTimestamps, $columnMatches, PREG_SET_ORDER);
            
            // 创建已处理列的列表，以避免重复处理特殊列（如created_at, updated_at）
            $processedColumns = [];
            
            foreach ($columnMatches as $match) {
                $method = $match[1];
                $field = $match[2];
                
                // 如果启用了timestamps()，跳过手动定义的created_at和updated_at
                if ($hasTimestamps && ($field === 'created_at' || $field === 'updated_at')) {
                    continue;
                }
                
                if (in_array($field, $processedColumns)) {
                    continue; // 跳过已处理的列
                }
                
                $processedColumns[] = $field;
                $params = isset($match[3]) ? $match[3] : '';
                $definition = $match[0];
                
                // 提取注释
                $comment = '';
                if (preg_match('/->comment\(\'([^\']+)\'\)/', $definition, $commentMatch)) {
                    $comment = $commentMatch[1];
                }
                
                // 提取是否可为空
                $nullable = strpos($definition, '->nullable()') !== false;
                
                // 提取默认值
                $default = null;
                if (preg_match('/->default\(([^)]+)\)/', $definition, $defaultMatch)) {
                    $default = trim($defaultMatch[1], "'\"");
                }
                
                // 检查是否是主键
                $isPrimary = strpos($definition, '->primary()') !== false;
                
                // 转换方法名到MySQL类型
                $typeMap = [
                    'integer' => 'int',
                    'bigInteger' => 'bigint',
                    'tinyInteger' => 'tinyint',
                    'smallInteger' => 'smallint',
                    'mediumInteger' => 'mediumint',
                    'string' => 'varchar',
                    'char' => 'char',
                    'text' => 'text',
                    'mediumText' => 'mediumtext',
                    'longText' => 'longtext',
                    'json' => 'json',
                    'dateTime' => 'datetime',
                    'timestamp' => 'timestamp',
                    'date' => 'date',
                    'time' => 'time',
                    'decimal' => 'decimal',
                    'float' => 'float',
                    'double' => 'double',
                    'boolean' => 'tinyint',
                    'enum' => 'enum',
                    'bigIncrements' => 'bigint',
                ];
                
                $type = $typeMap[$method] ?? 'varchar';
                
                // 添加长度信息
                if ($method === 'string' && $params) {
                    $type .= "($params)";
                } elseif ($method === 'decimal' && $params) {
                    $type .= "($params)";
                } elseif ($type === 'varchar' && !$params) {
                    $type .= "(255)"; // 默认varchar长度
                }
                
                $column = [
                    'Field' => $field,
                    'Type' => $type,
                    'Null' => $nullable ? 'YES' : 'NO',
                    'Default' => $default,
                    'Comment' => $comment,
                    'Extra' => '',
                ];
                
                $columns[] = $column;
                
                // 如果是主键，添加主键信息
                if ($isPrimary) {
                    $indexes[] = [
                        'Key_name' => 'PRIMARY',
                        'Column_name' => $field,
                        'Non_unique' => 0,
                    ];
                }
            }
            
            // 添加timestamps生成的列
            if ($hasTimestamps) {
                if (!in_array('created_at', $processedColumns)) {
                    $columns[] = [
                        'Field' => 'created_at',
                        'Type' => 'timestamp',
                        'Null' => 'YES',
                        'Default' => null,
                        'Comment' => '',
                        'Extra' => '',
                    ];
                }
                
                if (!in_array('updated_at', $processedColumns)) {
                    $columns[] = [
                        'Field' => 'updated_at',
                        'Type' => 'timestamp',
                        'Null' => 'YES',
                        'Default' => null,
                        'Comment' => '',
                        'Extra' => '',
                    ];
                }
            }
            
            // 提取主键定义
            preg_match_all('/\$table->primary\((?:\'([^\']+)\'|\[([^\]]+)\])\);/s', $schemaContentWithoutTimestamps, $primaryMatches, PREG_SET_ORDER);
            
            foreach ($primaryMatches as $match) {
                // 区分单列主键和复合主键
                if (!empty($match[1])) {
                    // 单列主键
                    $column = $match[1];
                    $indexes[] = [
                        'Key_name' => 'PRIMARY',
                        'Column_name' => $column,
                        'Non_unique' => 0,
                    ];
                } elseif (!empty($match[2])) {
                    // 复合主键
                    $columnsStr = $match[2];
                    $columnsArr = explode(',', $columnsStr);
                    foreach ($columnsArr as $column) {
                        $column = trim($column, "' \t\n\r\0\x0B\"");
                        $indexes[] = [
                            'Key_name' => 'PRIMARY',
                            'Column_name' => $column,
                            'Non_unique' => 0,
                        ];
                    }
                }
            }
            
            // 提取索引定义 - 同时支持单列索引和复合索引
            // 首先提取单列索引
            preg_match_all('/\$table->(unique|index)\(\'([^\']+)\'(?:,\s*\'([^\']+)\')?\);/s', $schemaContentWithoutTimestamps, $singleIndexMatches, PREG_SET_ORDER);
            
            foreach ($singleIndexMatches as $match) {
                $indexType = $match[1];
                $column = $match[2];
                $keyName = isset($match[3]) ? $match[3] : '';
                
                if (empty($keyName)) {
                    if ($indexType === 'unique') {
                        $keyName = "{$table}_{$column}_unique";
                    } elseif ($indexType === 'index') {
                        $keyName = "{$table}_{$column}_index";
                    }
                }
                
                $indexes[] = [
                    'Key_name' => $keyName,
                    'Column_name' => $column,
                    'Non_unique' => $indexType === 'unique' ? 0 : 1,
                    'Seq_in_index' => 1,
                    'is_composite' => false
                ];
            }
            
            // 提取复合索引 - 使用数组语法 $table->index(['column1', 'column2'], 'idx_name');
            preg_match_all('/\$table->(unique|index)\(\[([^\]]+)\](?:,\s*\'([^\']+)\')?\);/s', $schemaContentWithoutTimestamps, $compositeIndexMatches, PREG_SET_ORDER);
            
            foreach ($compositeIndexMatches as $match) {
                $indexType = $match[1];
                $columnsStr = $match[2];
                $keyName = isset($match[3]) ? $match[3] : '';
                
                // 解析复合索引列
                preg_match_all('/\'([^\']+)\'/', $columnsStr, $columnMatches);
                $indexColumns = $columnMatches[1];
                
                if (empty($keyName)) {
                    if ($indexType === 'unique') {
                        $keyName = "{$table}_" . implode('_', $indexColumns) . "_unique";
                    } elseif ($indexType === 'index') {
                        $keyName = "{$table}_" . implode('_', $indexColumns) . "_index";
                    }
                }
                
                // 添加复合索引的每一列，并标记其在索引中的序号
                $seqNumber = 1;
                foreach ($indexColumns as $indexColumn) {
                    $indexes[] = [
                        'Key_name' => $keyName,
                        'Column_name' => $indexColumn,
                        'Non_unique' => $indexType === 'unique' ? 0 : 1,
                        'Seq_in_index' => $seqNumber++,
                        'is_composite' => true
                    ];
                }
            }
        }
        
        return [$columns, $indexes, $tableInfo];
    }
    
    /**
     * 生成创建表的Schema代码
     */
    protected function generateCreateTableSchemaCode(string $table): array
    {
        $columns = $this->getTableColumns($table);
        $indexes = $this->getTableIndexes($table);
        $tableInfo = $this->getTableInfo($table);
        
        // 设置索引缓存，供generateIndexCode使用
        $this->setAllIndexesCache($indexes);
        
        $upCode = "Schema::create('{$table}', function (Blueprint \$table) {\n";
        
        // 找出主键列
        $primaryKeyColumns = [];
        foreach ($indexes as $index) {
            if ($index['Key_name'] === 'PRIMARY') {
                $primaryKeyColumns[] = $index['Column_name'];
            }
        }
        
        // 检查是否有created_at和updated_at列，用于决定是否使用timestamps()
        $hasCreatedAt = false;
        $hasUpdatedAt = false;
        $createdAtType = '';
        $updatedAtType = '';
        
        foreach ($columns as $column) {
            if ($column['Field'] === 'created_at') {
                $hasCreatedAt = true;
                $createdAtType = $column['Type'];
            } elseif ($column['Field'] === 'updated_at') {
                $hasUpdatedAt = true;
                $updatedAtType = $column['Type'];
            }
        }
        
        $useTimestamps = $hasCreatedAt && $hasUpdatedAt && 
                        (strpos($createdAtType, 'timestamp') !== false || strpos($createdAtType, 'datetime') !== false) &&
                        (strpos($updatedAtType, 'timestamp') !== false || strpos($updatedAtType, 'datetime') !== false);
        
        // 生成列创建代码
        foreach ($columns as $column) {
            // 如果使用timestamps()方法，跳过created_at和updated_at列
            if ($useTimestamps && ($column['Field'] === 'created_at' || $column['Field'] === 'updated_at')) {
                continue;
            }
            
            // 检查列是否是自增主键
            $isAutoIncrement = stripos($column['Extra'] ?? '', 'auto_increment') !== false;
            
            // 如果是自增主键，使用适当的方法
            if ($isAutoIncrement && in_array($column['Field'], $primaryKeyColumns)) {
                if (strpos(strtolower($column['Type']), 'bigint') === 0) {
                    $code = "\$table->bigIncrements('{$column['Field']}')";
                } elseif (strpos(strtolower($column['Type']), 'int') === 0) {
                    $code = "\$table->increments('{$column['Field']}')";
                } else {
                    // 默认使用bigIncrements
                    $code = "\$table->bigIncrements('{$column['Field']}')";
                }
                
                // 添加注释（如果有）
                if (!empty($column['Comment'])) {
                    $code .= "->comment('{$column['Comment']}')";
                }
                
                $upCode .= "            {$code};\n";
            } else {
                // 对于非自增主键列，使用普通方法
                $columnCode = $this->generateColumnCode($column);
                $upCode .= "            {$columnCode}\n";
            }
        }
        
        // 生成单列非自增主键
        if (count($primaryKeyColumns) === 1 && !$this->isAutoIncrementColumn($columns, $primaryKeyColumns[0])) {
            $upCode .= "            \$table->primary('{$primaryKeyColumns[0]}');\n";
        }
        // 生成复合主键
        elseif (count($primaryKeyColumns) > 1) {
            $primaryKeyString = "'" . implode("', '", $primaryKeyColumns) . "'";
            $upCode .= "            \$table->primary([{$primaryKeyString}]);\n";
        }
        
        // 生成其他索引代码
        foreach ($indexes as $index) {
            if ($index['Key_name'] !== 'PRIMARY') {
                $indexCode = $this->generateIndexCode($index);
                $upCode .= "            {$indexCode}\n";
            }
        }
        
        // 添加时间戳列
        if ($useTimestamps) {
            $upCode .= "            \$table->timestamps();\n";
        }
        
        // 设置表字符集和校对集
        if (!empty($tableInfo['charset']) && !empty($tableInfo['collation'])) {
            $upCode .= "            \$table->charset = '{$tableInfo['charset']}';\n";
            $upCode .= "            \$table->collation = '{$tableInfo['collation']}';\n";
        }
        
        // 设置表注释
        if (!empty($tableInfo['comment'])) {
            $upCode .= "            \$table->comment('{$tableInfo['comment']}');\n";
        }
        
        // 设置引擎
        if (!empty($tableInfo['engine'])) {
            $upCode .= "            \$table->engine = '{$tableInfo['engine']}';\n";
        }
        
        $upCode .= "        });";
        
        return [
            'up' => $upCode,
            'down' => "Schema::dropIfExists('{$table}');"
        ];
    }
    
    /**
     * 检查列是否为自增列
     */
    protected function isAutoIncrementColumn(array $columns, string $columnName): bool
    {
        foreach ($columns as $column) {
            if ($column['Field'] === $columnName && stripos($column['Extra'] ?? '', 'auto_increment') !== false) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * 检查表是否有timestamps列
     */
    protected function hasTimestamps(array $columns): bool
    {
        $hasCreatedAt = false;
        $hasUpdatedAt = false;
        
        foreach ($columns as $column) {
            if ($column['Field'] === 'created_at' && strpos(strtolower($column['Type']), 'timestamp') !== false ||
                $column['Field'] === 'created_at' && strpos(strtolower($column['Type']), 'datetime') !== false) {
                $hasCreatedAt = true;
            }
            if ($column['Field'] === 'updated_at' && strpos(strtolower($column['Type']), 'timestamp') !== false ||
                $column['Field'] === 'updated_at' && strpos(strtolower($column['Type']), 'datetime') !== false) {
                $hasUpdatedAt = true;
            }
        }
        
        return $hasCreatedAt && $hasUpdatedAt;
    }
    
    /**
     * 获取表的详细信息（字符集、校对集、引擎、注释等）
     */
    protected function getTableInfo(string $table): array
    {
        $info = [];
        
        try {
            $result = Db::select("SHOW TABLE STATUS WHERE Name = '{$table}'");
            
            if (!empty($result)) {
                $tableStatus = (array)$result[0];
                
                // 提取引擎
                $info['engine'] = $tableStatus['Engine'] ?? '';
                
                // 提取注释
                $info['comment'] = $tableStatus['Comment'] ?? '';
                
                // 获取字符集和校对集
                $createTableResult = Db::select("SHOW CREATE TABLE `{$table}`");
                if (!empty($createTableResult)) {
                    $createTableSql = (array)$createTableResult[0];
                    $createTable = $createTableSql['Create Table'] ?? '';
                    
                    // 提取字符集
                    if (preg_match('/CHARACTER SET\s+([^\s]+)/i', $createTable, $matches)) {
                        $info['charset'] = $matches[1];
                    } elseif (preg_match('/CHARSET=([^\s]+)/i', $createTable, $matches)) {
                        $info['charset'] = $matches[1];
                    }
                    
                    // 提取校对集
                    if (preg_match('/COLLATE\s+([^\s]+)/i', $createTable, $matches)) {
                        $info['collation'] = $matches[1];
                    } elseif (preg_match('/COLLATE=([^\s]+)/i', $createTable, $matches)) {
                        $info['collation'] = $matches[1];
                    }
                }
            }
        } catch (\Exception $e) {
            // 忽略错误，返回空信息
        }
        
        return $info;
    }
    
    /**
     * 获取表的列信息
     */
    protected function getTableColumns(string $table): array
    {
        $columns = Db::select("SHOW FULL COLUMNS FROM `{$table}`");
        // 将stdClass对象转换为数组
        return array_map(function ($column) {
            return (array) $column;
        }, $columns);
    }
    
    /**
     * 获取表的索引信息
     */
    protected function getTableIndexes(string $table): array
    {
        $indexes = Db::select("SHOW INDEX FROM `{$table}`");
        // 将stdClass对象转换为数组
        $indexesArray = array_map(function ($index) {
            return (array) $index;
        }, $indexes);
        
        // 对联合索引进行分组和标记处理
        $groupedIndexes = [];
        $processedKeyNames = []; // 跟踪已处理的索引名称
        
        foreach ($indexesArray as $index) {
            $keyName = $index['Key_name'];
            if (!isset($groupedIndexes[$keyName])) {
                $groupedIndexes[$keyName] = [];
            }
            $groupedIndexes[$keyName][] = $index;
        }
        
        $processedIndexes = [];
        foreach ($groupedIndexes as $keyName => $indexes) {
            // 如果同一个Key_name有多个记录，说明是联合索引
            $isComposite = count($indexes) > 1;
            
            // 如果是复合索引，只为第一列生成索引代码
            if ($isComposite) {
                // 按照Seq_in_index排序
                usort($indexes, function($a, $b) {
                    return ($a['Seq_in_index'] ?? 0) - ($b['Seq_in_index'] ?? 0);
                });
                
                // 只保留第一列作为复合索引的代表
                $firstIndex = $indexes[0];
                $firstIndex['is_composite'] = true;
                $firstIndex['composite_columns'] = array_map(function($idx) {
                    return $idx['Column_name'];
                }, $indexes);
                $processedIndexes[] = $firstIndex;
            } else {
                // 单列索引
                $indexes[0]['is_composite'] = false;
                $processedIndexes[] = $indexes[0];
            }
        }
        
        return $processedIndexes;
    }
    
    /**
     * 根据列信息生成Schema列代码
     */
    protected function generateColumnCode(array $column): string
    {
        $field = $column['Field'];
        $type = strtolower($column['Type']);
        $nullable = $column['Null'] === 'YES';
        $default = $column['Default'];
        $comment = $column['Comment'] ?? '';
        
        // 解析类型和长度
        preg_match('/^([a-z]+)(?:\(([^)]+)\))?/', $type, $matches);
        $dataType = $matches[1] ?? '';
        $length = $matches[2] ?? null;
        
        // 映射MySQL数据类型到Schema方法
        $methodMap = [
            'int' => 'integer',
            'bigint' => 'bigInteger',
            'tinyint' => 'tinyInteger',
            'smallint' => 'smallInteger',
            'mediumint' => 'mediumInteger',
            'varchar' => 'string',
            'char' => 'char',
            'text' => 'text',
            'mediumtext' => 'mediumText',
            'longtext' => 'longText',
            'json' => 'json',
            'datetime' => 'dateTime',
            'timestamp' => 'timestamp',
            'date' => 'date',
            'time' => 'time',
            'decimal' => 'decimal',
            'float' => 'float',
            'double' => 'double',
            'boolean' => 'boolean',
            'enum' => 'enum',
        ];
        
        // 获取方法名
        $method = $methodMap[$dataType] ?? 'string';
        
        // 构建方法调用
        $code = "\$table->{$method}('{$field}'";
        
        // 添加长度参数（如果适用）
        if ($length && in_array($method, ['string', 'char'])) {
            $code .= ", {$length}";
        } elseif ($length && $method === 'decimal') {
            $parts = explode(',', $length);
            if (count($parts) === 2) {
                $code .= ", {$parts[0]}, {$parts[1]}";
            }
        } elseif ($length && $method === 'enum') {
            $values = explode(',', str_replace("'", '', $length));
            $valueString = "'" . implode("', '", $values) . "'";
            $code .= ", [{$valueString}]";
        }
        
        $code .= ')';
        
        // 添加修饰符
        if ($nullable) {
            $code .= '->nullable()';
        } else {
            $code .= '';
        }
        
        if ($default !== null) {
            if ($default === 'CURRENT_TIMESTAMP') {
                $code .= '->useCurrent()';
            } elseif (strtolower($default) === 'null' && $nullable) {
                // 如果默认值是NULL且列允许为空
                $code .= '->default(null)';
            } else {
                // 处理数字类型的默认值，避免引号
                if (is_numeric($default) && in_array($method, ['integer', 'bigInteger', 'tinyInteger', 'smallInteger', 'mediumInteger', 'decimal', 'float', 'double'])) {
                    $code .= "->default({$default})";
                } else {
                    $code .= "->default('{$default}')";
                }
            }
        }
        
        if (!empty($comment)) {
            $code .= "->comment('{$comment}')";
        }
        
        // 注意：自增主键现在在generateCreateTableSchemaCode方法中特殊处理
        // 这里不再处理自增列，避免重复
        
        return $code . ';';
    }
    
    /**
     * 根据索引信息生成Schema索引代码
     */
    protected function generateIndexCode(array $index): string
    {
        $keyName = $index['Key_name'];
        $column = $index['Column_name'];
        $nonUnique = $index['Non_unique'];
        $isComposite = isset($index['is_composite']) && $index['is_composite'];
        
        if ($keyName === 'PRIMARY') {
            return "\$table->primary('{$column}');";
        } else {
            // 处理复合索引
            if ($isComposite && isset($index['composite_columns'])) {
                $columns = $index['composite_columns'];
                $columnsStr = implode("', '", $columns);
                
                if ($nonUnique == 0) {
                    return "\$table->unique(['{$columnsStr}'], '{$keyName}');";
                } else {
                    return "\$table->index(['{$columnsStr}'], '{$keyName}');";
                }
            } else {
                // 处理单列索引
                if ($nonUnique == 0) {
                    return "\$table->unique('{$column}', '{$keyName}');";
                } else {
                    return "\$table->index('{$column}', '{$keyName}');";
                }
            }
        }
    }
    
    /**
     * 获取复合索引的所有列
     */
    protected function getCompositeIndexColumns(string $keyName): array
    {
        $columns = [];
        $allIndexes = $this->getAllIndexesCache();
        
        foreach ($allIndexes as $index) {
            if ($index['Key_name'] === $keyName) {
                $columns[] = $index['Column_name'];
            }
        }
        
        // 按照索引中的列顺序排序
        usort($columns, function($a, $b) use ($allIndexes, $keyName) {
            $seqA = 0;
            $seqB = 0;
            
            foreach ($allIndexes as $index) {
                if ($index['Key_name'] === $keyName) {
                    if ($index['Column_name'] === $a) {
                        $seqA = $index['Seq_in_index'] ?? 0;
                    }
                    if ($index['Column_name'] === $b) {
                        $seqB = $index['Seq_in_index'] ?? 0;
                    }
                }
            }
            
            return $seqA - $seqB;
        });
        
        return $columns;
    }

    // 用于缓存所有索引的属性
    private $allIndexesCache = null;
    
    /**
     * 获取所有索引的缓存
     */
    protected function getAllIndexesCache(): array
    {
        if ($this->allIndexesCache === null) {
            // 从数据库或其他来源获取索引信息
            $this->allIndexesCache = []; // 默认为空数组
        }
        return $this->allIndexesCache;
    }
    
    /**
     * 设置索引缓存
     */
    protected function setAllIndexesCache(array $indexes): void
    {
        $this->allIndexesCache = $indexes;
    }

    /**
     * Get the class name of a migration name.
     */
    protected function getClassName(string $name): string
    {
        return Str::studly($name);
    }

    /**
     * Get the full path to the migration.
     */
    protected function getPath(string $name, string $path): string
    {
        return $path . '/' . $this->getDatePrefix() . '_' . $name . '.php';
    }

    /**
     * Fire the registered post create hooks.
     */
    protected function firePostCreateHooks(?string $table)
    {
        foreach ($this->postCreate as $callback) {
            call_user_func($callback, $table);
        }
    }

    /**
     * Get the date prefix for the migration.
     */
    protected function getDatePrefix(): string
    {
        return date('Y_m_d_His');
    }

    /**
     * 格式化列信息，便于比较
     */
    protected function formatColumnsForComparison(array $columns): array
    {
        $formatted = [];
        foreach ($columns as $column) {
            $formatted[$column['Field']] = $column;
        }
        return $formatted;
    }
    
    /**
     * 生成修改表结构的Schema代码
     */
    protected function generateSchemaCode(string $table): array
    {
        // 查找最新的表迁移文件
        $latestMigration = $this->findLatestTableMigration($table);
        $previousColumns = [];
        $previousIndexes = [];
        $previousTableInfo = [
            'charset' => '',
            'collation' => '',
            'engine' => '',
            'comment' => '',
        ];
        
        // 如果找到了迁移文件，解析其中的表结构
        if ($latestMigration) {
            $migrationContent = $this->files->get($latestMigration);
            // 解析迁移文件中的列和索引
            list($previousColumns, $previousIndexes, $previousTableInfo) = $this->parseMigrationContent($migrationContent, $table);
        }
        
        // 获取当前数据库中的表结构
        $currentColumns = $this->getTableColumns($table);
        $currentIndexes = $this->getTableIndexes($table);
        $currentTableInfo = $this->getTableInfo($table);
        
        // 设置索引缓存，供generateIndexCode使用
        $this->setAllIndexesCache($currentIndexes);
        
        // 转换列信息为便于比较的格式
        $formattedPreviousColumns = $this->formatColumnsForComparison($previousColumns);
        $formattedCurrentColumns = $this->formatColumnsForComparison($currentColumns);
        
        // 转换索引信息为便于比较的格式
        $formattedPreviousIndexes = $this->formatIndexesForComparison($previousIndexes);
        $formattedCurrentIndexes = $this->formatIndexesForComparison($currentIndexes);
        
        // 计算差异（排除时间戳字段）
        $columnsToAdd = $this->getColumnsToAdd($formattedPreviousColumns, $formattedCurrentColumns);
        $columnsToModify = $this->getColumnsToModify($formattedPreviousColumns, $formattedCurrentColumns);
        $columnsToRemove = $this->getColumnsToRemove($formattedPreviousColumns, $formattedCurrentColumns);
        
        // 过滤掉created_at和updated_at字段，这些字段不需要处理
        $columnsToAdd = array_filter($columnsToAdd, function($column) {
            return !$this->isTimestampField($column['Field']);
        });
        $columnsToModify = array_filter($columnsToModify, function($column) {
            return !$this->isTimestampField($column['Field']);
        });
        $columnsToRemove = array_filter($columnsToRemove, function($column) {
            return !$this->isTimestampField($column['Field']);
        });
        
        // 主键处理
        $hasPrimaryKeyChange = $this->hasPrimaryKeyChange($formattedPreviousIndexes, $formattedCurrentIndexes);
        $primaryKeyColumns = $this->getPrimaryKeyColumns($formattedCurrentIndexes);
        $previousPrimaryKeyColumns = $this->getPrimaryKeyColumns($formattedPreviousIndexes);
        
        // 其他索引变更
        $indexesToAdd = $this->getIndexesToAdd($previousIndexes, $currentIndexes);
        $indexesToRemove = $this->getIndexesToRemove($previousIndexes, $currentIndexes);
        
        // 过滤主键相关的索引变更
        $indexesToAdd = array_filter($indexesToAdd, function($index) {
            return $index['Key_name'] !== 'PRIMARY';
        });
        
        $indexesToRemove = array_filter($indexesToRemove, function($index) {
            return $index['Key_name'] !== 'PRIMARY';
        });
        
        // 检查表信息是否有变化
        $tableInfoChanged = $this->tableInfoHasChanged($previousTableInfo, $currentTableInfo);
        
        // 生成Schema代码
        $upCode = "Schema::table('{$table}', function (Blueprint \$table) {\n";
        $downCode = "Schema::table('{$table}', function (Blueprint \$table) {\n";
        
        // 添加新列
        foreach ($columnsToAdd as $column) {
            $columnCode = $this->generateColumnCode($column);
            $upCode .= "            {$columnCode}\n";
            $downCode .= "            \$table->dropColumn('{$column['Field']}');\n";
        }
        
        // 修改列
        foreach ($columnsToModify as $column) {
            $columnCode = $this->generateColumnModifyCode($column);
            $upCode .= "            {$columnCode}\n";
            // 在down方法中，恢复原始列定义
            if (isset($formattedPreviousColumns[$column['Field']])) {
                $originalColumn = $formattedPreviousColumns[$column['Field']];
                $originalColumnCode = $this->generateColumnModifyCode($originalColumn);
                $downCode .= "            {$originalColumnCode}\n";
            }
        }
        
        // 删除列
        foreach ($columnsToRemove as $column) {
            $upCode .= "            \$table->dropColumn('{$column['Field']}');\n";
            $restoreColumnCode = $this->generateColumnCode($column);
            $downCode .= "            {$restoreColumnCode}\n";
        }
        
        // 处理主键变更
        if ($hasPrimaryKeyChange) {
            // 如果之前有主键，先删除它
            if (!empty($previousPrimaryKeyColumns)) {
                $upCode .= "            \$table->dropPrimary();\n";
                
                // 在down中恢复原来的主键
                if (count($previousPrimaryKeyColumns) === 1) {
                    $downCode .= "            \$table->primary('{$previousPrimaryKeyColumns[0]}');\n";
                } else {
                    $pkColsString = "'" . implode("', '", $previousPrimaryKeyColumns) . "'";
                    $downCode .= "            \$table->primary([{$pkColsString}]);\n";
                }
            }
            
            // 添加新主键
            if (!empty($primaryKeyColumns)) {
                if (count($primaryKeyColumns) === 1) {
                    $upCode .= "            \$table->primary('{$primaryKeyColumns[0]}');\n";
                } else {
                    $pkColsString = "'" . implode("', '", $primaryKeyColumns) . "'";
                    $upCode .= "            \$table->primary([{$pkColsString}]);\n";
                }
                
                // 在down中删除新主键
                $downCode .= "            \$table->dropPrimary();\n";
            }
        }
        
        // 添加索引
        foreach ($indexesToAdd as $index) {
            $indexCode = $this->generateIndexCode($index);
            $upCode .= "            {$indexCode}\n";
            $downCode .= "            \$table->dropIndex('{$index['Key_name']}');\n";
        }
        
        // 删除索引
        foreach ($indexesToRemove as $index) {
            $upCode .= "            \$table->dropIndex('{$index['Key_name']}');\n";
            $restoreIndexCode = $this->generateIndexCode($index);
            $downCode .= "            {$restoreIndexCode}\n";
        }
        
        // 更新表信息
        if ($tableInfoChanged) {
            // 设置字符集和校对集
            if ($currentTableInfo['charset'] !== $previousTableInfo['charset'] || 
                $currentTableInfo['collation'] !== $previousTableInfo['collation']) {
                $upCode .= "            \$table->charset = '{$currentTableInfo['charset']}';\n";
                $upCode .= "            \$table->collation = '{$currentTableInfo['collation']}';\n";
                
                $downCode .= "            \$table->charset = '{$previousTableInfo['charset']}';\n";
                $downCode .= "            \$table->collation = '{$previousTableInfo['collation']}';\n";
            }
            
            // 设置引擎
            if ($currentTableInfo['engine'] !== $previousTableInfo['engine']) {
                $upCode .= "            \$table->engine = '{$currentTableInfo['engine']}';\n";
                $downCode .= "            \$table->engine = '{$previousTableInfo['engine']}';\n";
            }
            
            // 设置表注释
            if ($currentTableInfo['comment'] !== $previousTableInfo['comment']) {
                $upCode .= "            \$table->comment('{$currentTableInfo['comment']}');\n";
                $downCode .= "            \$table->comment('{$previousTableInfo['comment']}');\n";
            }
        }
        
        $upCode .= "        });";
        $downCode .= "        });";
        
        return [
            'up' => $upCode,
            'down' => $downCode
        ];
    }
    
    /**
     * 查找给定表的最新迁移文件
     */
    protected function findLatestTableMigration(string $table): ?string
    {
        $migrationDir = BASE_PATH.DIRECTORY_SEPARATOR. 'databases'.DIRECTORY_SEPARATOR.'migrations'.DIRECTORY_SEPARATOR . $table;
        if (!file_exists($migrationDir)) {
            return null;
        }
        
        $migrationFiles = $this->files->glob($migrationDir . '/*.php');
        if (empty($migrationFiles)) {
            return null;
        }
        
        // 按文件名排序，找到最新的
        usort($migrationFiles, function($a, $b) {
            return strcmp($b, $a);
        });
        
        // 查找包含创建表的迁移
        foreach ($migrationFiles as $file) {
            $content = $this->files->get($file);
            if (strpos($content, "Schema::create('{$table}'") !== false || 
                strpos($content, "Schema::create(\"{$table}\"") !== false) {
                return $file;
            }
        }
        
        return null;
    }
    
    /**
     * 生成修改列的Schema代码
     */
    protected function generateColumnModifyCode(array $column): string
    {
        $code = $this->generateColumnCode($column);
        // 将代码的末尾的分号前添加->change()
        return substr($code, 0, -1) . '->change();';
    }
    
    /**
     * 获取需要添加的列
     */
    protected function getColumnsToAdd(array $previousColumns, array $currentColumns): array
    {
        $columnsToAdd = [];
        foreach ($currentColumns as $field => $column) {
            if (!isset($previousColumns[$field])) {
                $columnsToAdd[] = $column;
            }
        }
        return $columnsToAdd;
    }
    
    /**
     * 获取需要修改的列
     */
    protected function getColumnsToModify(array $previousColumns, array $currentColumns): array
    {
        $columnsToModify = [];
        foreach ($currentColumns as $field => $column) {
            if (isset($previousColumns[$field])) {
                $previous = $previousColumns[$field];
                // 比较列定义，检查是否有变化
                if ($this->columnHasChanged($previous, $column)) {
                    $columnsToModify[] = $column;
                }
            }
        }
        return $columnsToModify;
    }
    
    /**
     * 检查列是否有变化
     */
    protected function columnHasChanged(array $previous, array $current): bool
    {
        // 比较关键属性
        return $previous['Type'] !== $current['Type'] ||
               $previous['Null'] !== $current['Null'] ||
               $previous['Default'] !== $current['Default'] ||
               $previous['Comment'] !== $current['Comment'] ||
               $previous['Extra'] !== $current['Extra'];
    }
    
    /**
     * 获取需要删除的列
     */
    protected function getColumnsToRemove(array $previousColumns, array $currentColumns): array
    {
        $columnsToRemove = [];
        foreach ($previousColumns as $field => $column) {
            if (!isset($currentColumns[$field])) {
                $columnsToRemove[] = $column;
            }
        }
        return $columnsToRemove;
    }
    
    /**
     * 获取主键列
     */
    protected function getPrimaryKeyColumns(array $indexes): array
    {
        $primaryKeyColumns = [];
        foreach ($indexes as $keyName => $index) {
            if ($keyName === 'PRIMARY') {
                $primaryKeyColumns[] = $index['Column_name'];
            }
        }
        return $primaryKeyColumns;
    }
    
    /**
     * 检查主键是否发生变化
     */
    protected function hasPrimaryKeyChange(array $previousIndexes, array $currentIndexes): bool
    {
        $previousHasPrimary = isset($previousIndexes['PRIMARY']);
        $currentHasPrimary = isset($currentIndexes['PRIMARY']);
        
        // 如果主键存在性发生变化
        if ($previousHasPrimary !== $currentHasPrimary) {
            return true;
        }
        
        // 如果都有主键，检查主键列是否发生变化
        if ($previousHasPrimary && $currentHasPrimary) {
            $previousPrimaryColumn = $previousIndexes['PRIMARY']['Column_name'];
            $currentPrimaryColumn = $currentIndexes['PRIMARY']['Column_name'];
            return $previousPrimaryColumn !== $currentPrimaryColumn;
        }
        
        return false;
    }
    
    /**
     * 检查表信息是否有变化
     */
    protected function tableInfoHasChanged(array $previous, array $current): bool
    {
        return $previous['charset'] !== $current['charset'] ||
               $previous['collation'] !== $current['collation'] ||
               $previous['engine'] !== $current['engine'] ||
               $previous['comment'] !== $current['comment'];
    }
    
    /**
     * 获取需要添加的索引
     */
    protected function getIndexesToAdd(array $previousIndexes, array $currentIndexes): array
    {
        $formattedPrevious = $this->formatIndexesForComparison($previousIndexes);
        $formattedCurrent = $this->formatIndexesForComparison($currentIndexes);
        
        $indexesToAdd = [];
        foreach ($formattedCurrent as $keyName => $index) {
            if (!isset($formattedPrevious[$keyName])) {
                $indexesToAdd[] = $index;
            }
        }
        return $indexesToAdd;
    }
    
    /**
     * 获取需要删除的索引
     */
    protected function getIndexesToRemove(array $previousIndexes, array $currentIndexes): array
    {
        $formattedPrevious = $this->formatIndexesForComparison($previousIndexes);
        $formattedCurrent = $this->formatIndexesForComparison($currentIndexes);
        
        $indexesToRemove = [];
        foreach ($formattedPrevious as $keyName => $index) {
            if (!isset($formattedCurrent[$keyName])) {
                $indexesToRemove[] = $index;
            }
        }
        return $indexesToRemove;
    }
    
    /**
     * 格式化索引信息，便于比较
     */
    protected function formatIndexesForComparison(array $indexes): array
    {
        $formatted = [];
        
        // 直接使用处理好的索引信息
        foreach ($indexes as $index) {
            $keyName = $index['Key_name'];
            $formatted[$keyName] = $index;
        }
        
        return $formatted;
    }

    /**
     * 检查是否为时间戳字段（created_at或updated_at）
     */
    protected function isTimestampField(string $fieldName): bool
    {
        return $fieldName === 'created_at' || $fieldName === 'updated_at';
    }
}

