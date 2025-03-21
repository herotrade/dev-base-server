<?php

/**
 * ModelRewriteGetterSetter.php
 * Author    chenmqq (machen7408@gmail.com)
 * Version   1.0
 * Date      2025/3/11
 * @link     algoquant.org
 * @document algoquant.org
 */


namespace Hyperf\Database\Commands\Ast;

use Hyperf\CodeParser\PhpParser;
use Hyperf\Database\Commands\Ast\AbstractVisitor;
use Hyperf\Database\Commands\ModelData;
use Hyperf\Database\Commands\ModelOption;
use Hyperf\Stringable\Str;
use PhpParser\Node;
use function Hyperf\Support\getter;
use function Hyperf\Support\setter;

class ModelRewriteGetterSetterVisitor extends AbstractVisitor
{
    /**
     * @var string[]
     */
    protected array $getters = [];

    /**
     * @var string[]
     */
    protected array $setters = [];

    public function __construct(ModelOption $option, ModelData $data)
    {
        parent::__construct($option, $data);
    }

    public function beforeTraverse(array $nodes)
    {
        $methods = PhpParser::getInstance()->getAllMethodsFromStmts($nodes);

        $this->collectMethods($methods);

        return null;
    }

    public function afterTraverse(array $nodes)
    {
        foreach ($nodes as $namespace) {
            if (!$namespace instanceof Node\Stmt\Namespace_) {
                continue;
            }

            foreach ($namespace->stmts as $class) {
                if (!$class instanceof Node\Stmt\Class_) {
                    continue;
                }

                array_push($class->stmts, ...$this->buildGetterAndSetter());
            }
        }

        return $nodes;
    }

    protected function getReturnType(string $data_type): string
    {
        return match ($data_type) {
            'tinyint', 'smallint', 'mediumint', 'int', 'bigint' => 'int',
            'float', 'double', 'real', 'decimal' => 'float',
            'bool', 'boolean' => 'bool',
            'varchar', 'char', 'text', 'longtext', 'tinytext' => 'string',
            'datetime' => '\Carbon\Carbon',
            'json' => 'array',
            default => 'mixed',
        };
    }

    /**
     * @return Node\Stmt\ClassMethod[]
     */
    protected function buildGetterAndSetter(): array
    {
        $stmts = [];
        foreach ($this->data->getColumns() as $column) {
            if ($name = $column['column_name'] ?? null) {
                $getter = getter($name);
                if (!in_array($getter, $this->getters)) {
                    $stmts[] = $this->createGetter($getter, $name, $this->getReturnType($column['data_type']));
                }
                $setter = setter($name);
                if (!in_array($setter, $this->setters)) {
                    $stmts[] = $this->createSetter($setter, $name);
                }
            }
        }

        return $stmts;
    }

    protected function createGetter(string $method, string $name, string $type): Node\Stmt\ClassMethod
    {
        $node = new Node\Stmt\ClassMethod($method, ['flags' => Node\Stmt\Class_::MODIFIER_PUBLIC, 'returnType' => $type]);
        $node->stmts[] = new Node\Stmt\Return_(
            new Node\Expr\PropertyFetch(
                new Node\Expr\Variable('this'),
                new Node\Identifier($name)
            )
        );

        return $node;
    }

    protected function createSetter(string $method, string $name): Node\Stmt\ClassMethod
    {
        $type = match (strtolower($name)) {
            'created_at', 'updated_at' => 'static',
            default => 'object'
        };
        $node = new Node\Stmt\ClassMethod($method, [
            'flags' => Node\Stmt\Class_::MODIFIER_PUBLIC,
            'params' => [new Node\Param(new Node\Expr\Variable('value'))],
            'returnType' => $type
        ]);
        $node->stmts[] = new Node\Stmt\Expression(
            new Node\Expr\Assign(
                new Node\Expr\PropertyFetch(
                    new Node\Expr\Variable('this'),
                    new Node\Identifier($name)
                ),
                new Node\Expr\Variable('value')
            )
        );
        $node->stmts[] = new Node\Stmt\Return_(
            new Node\Expr\Variable('this')
        );

        return $node;
    }

    protected function collectMethods(array $methods)
    {
        /** @var Node\Stmt\ClassMethod $method */
        foreach ($methods as $method) {
            $methodName = $method->name->name;
            if (Str::startsWith($methodName, 'get')) {
                $this->getters[] = $methodName;
            } elseif (Str::startsWith($methodName, 'set')) {
                $this->setters[] = $methodName;
            }
        }
    }
}
