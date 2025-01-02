<?php

namespace yzh52521\aop\Aop;

use PhpParser\Modifiers;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\MagicConst\Dir as MagicConstDir;
use PhpParser\Node\Scalar\MagicConst\File as MagicConstFile;
use PhpParser\Node\Scalar\MagicConst\Function_ as MagicConstFunction;
use PhpParser\Node\Scalar\MagicConst\Method as MagicConstMethod;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\Node\Stmt\TraitUse;
use PhpParser\NodeVisitorAbstract;
use yzh52521\aop\AopBootstrap;

/**
 * Class ProxyNodeVisitor.
 */
class ProxyNodeVisitor extends NodeVisitorAbstract
{

    private ProxyCollects $proxyCollects;

    private string $currentClass = '';

    private Identifier $class;

    private $extends = null;

    public function __construct(ProxyCollects $proxyCollects)
    {
        $this->proxyCollects = $proxyCollects;
    }

    public function beforeTraverse(array $nodes)
    {
        foreach ($nodes as $namespace) {
            foreach ($namespace->stmts as $class) {
                if ($class instanceof Node\Stmt\Class_) {
                    $this->class = $class->name;
                    $this->extends = $class->extends ?? null;
                    $this->currentClass = $namespace->name->toString() . '\\' . $class->name;
                    return null;
                }
            }
        }
        return null;
    }

    public function leaveNode(Node $node)
    {
        switch ($node) {
            case $node instanceof ClassMethod:
                if (!$this->shouldRewrite($node)) {
                    return $node;
                }
                return $this->rewriteMethod($node);
            case $node instanceof Trait_:
                // If the node is trait and php version >= 7.3, it can `use ProxyTrait` like class.
            case $node instanceof Enum_:
                // If the node is enum and php version >= 8.1, it can `use ProxyTrait` like class.
            case $node instanceof Class_ && $this->shouldUseTrait():
                // Add use proxy traits.
                $stmts = $node->stmts;
                if ($stmt = $this->buildProxyCallTraitUseStatement()) {
                    array_unshift($stmts, $stmt);
                }
                $node->stmts = $stmts;
                unset($stmts);
                return $node;
            case ($node instanceof StaticPropertyFetch || $node instanceof StaticCall) && $this->extends:
                if ($node->class instanceof Node\Name && 'parent' === $node->class->toString()) {
                    $node->class = new Name($this->extends->toCodeString());
                    return $node;
                }
            case $node instanceof MagicConstFunction:
                // Rewrite __FUNCTION__ to $__function__ variable.
                if ($this->shouldUseTrait()) {
                    return new Variable('__function__');
                }
                break;
            case $node instanceof MagicConstMethod:
                // Rewrite __METHOD__ to $__method__ variable.
                if ($this->shouldUseTrait()) {
                    return new Variable('__method__');
                }
                break;
            case $node instanceof MagicConstDir:
                // Rewrite __DIR__ as the real directory path
                if ($file = AopBootstrap::getComposerClassLoader()->findFile($this->currentClass)) {
                    return new String_(dirname(realpath($file)));
                }
                break;
            case $node instanceof MagicConstFile:
                // Rewrite __FILE__ to the real file path
                if ($file = AopBootstrap::getComposerClassLoader()->findFile($this->currentClass)) {
                    return new String_(realpath($file));
                }
                break;
        }
        return null;
    }

    /**
     * Format a normal class method of no need proxy call.
     * @return ClassMethod
     */
    private function formatMethod(ClassMethod $node)
    {
        if ('__construct' === $node->name->toString()) {
            // Rewrite parent::__construct to class::__construct.
            foreach ($node->stmts as $stmt) {
                if ($stmt instanceof Expression && $stmt->expr instanceof Node\Expr\StaticCall) {
                    $class = $stmt->expr->class;
                    if ($class instanceof Node\Name && 'parent' === $class->toString()) {
                        $stmt->expr->class = new Node\Name($this->extends->toCodeString());
                    }
                }
            }
        }

        return $node;
    }

    /**
     * Rewrite a normal class method to a proxy call method,
     * include normal class method and static method.
     */
    private function rewriteMethod(ClassMethod $node): ClassMethod
    {
        // Build the static proxy call method base on the original method.
        $shouldReturn = true;
        $returnType = $node->getReturnType();
        if ($returnType instanceof Identifier && 'void' === $returnType->name) {
            $shouldReturn = false;
        }
        $class = $this->class->toString();
        $staticCall = new StaticCall(new Name('self'), '_proxyCall', [
            // __CLASS__
            new Node\Arg(new ClassConstFetch(new Name($class), new Identifier('class'))),
            // __FUNCTION__
            new Arg(new MagicConstFunction()),
            // self::getParamMap(OriginalClass::class, __FUNCTION, func_get_args())
            new Arg(new StaticCall(new Name('self'), '_getArguments', [
                new Node\Arg(new ClassConstFetch(new Name($class), new Identifier('class'))),
                new Arg(new MagicConstFunction()),
                new Arg(new FuncCall(new Name('func_get_args'))),
            ])),
            // A closure that wrapped original method code.
            new Arg(new Closure([
                'params' => $this->filterModifier($node->getParams()),
                'uses' => [
                    new Variable('__function__'),
                    new Variable('__method__'),
                ],
                'stmts' => $node->stmts,
            ])),
        ]);
        $stmts = $this->unshiftMagicMethods([]);
        if ($shouldReturn) {
            $stmts[] = new Return_($staticCall);
        } else {
            $stmts[] = new Expression($staticCall);
        }
        $node->stmts = $stmts;
        return $node;
    }

    /**
     * @param Node\Param[] $params
     * @return Node\Param[]
     */
    private function filterModifier(array $params): array
    {
        return array_map(function (Node\Param $param) {
            $tempParam = clone $param;
            $tempParam->flags &= ~Modifiers::VISIBILITY_MASK & ~Modifiers::READONLY;
            return $tempParam;
        }, $params);
    }

    private function unshiftMagicMethods($stmts = []): array
    {
        $magicConstFunction = new Expression(new Assign(new Variable('__function__'), new MagicConstFunction()));
        $magicConstMethod = new Expression(new Assign(new Variable('__method__'), new MagicConstMethod()));
        array_unshift($stmts, $magicConstFunction, $magicConstMethod);
        return $stmts;
    }

    /**
     * Build `use ProxyTrait;`.
     */
    private function buildProxyCallTraitUseStatement(): ?TraitUse
    {
        $traits = [new Name('\\' . ProxyCallTrait::class)];
        return new TraitUse($traits);
    }

    private function shouldRewrite(Node $node): bool
    {
        return $this->currentClass && $this->proxyCollects->shouldRewrite($this->currentClass, $node->name->toString());
    }

    private function shouldUseTrait(): bool
    {
        return $this->currentClass && $this->proxyCollects->shouldUseTrait($this->currentClass);
    }
}
