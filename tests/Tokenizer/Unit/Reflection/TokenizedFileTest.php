<?php

declare(strict_types=1);

namespace Tests\Tokenizer\Unit\Reflection;

use Testo\Assert;
use Testo\Test;
use Testo\Tokenizer\Reflection\TokenizedFile;

#[Test]
final class TokenizedFileTest
{
    public function methodsFqnIncludesAllVisibilityVariants(): void
    {
        $methods = self::tokenize('ClassWithMethods.php')->getMethodsFQN();

        \sort($methods);
        Assert::same($methods, [
            'Tests\Tokenizer\Stub\ClassWithMethods::__construct',
            'Tests\Tokenizer\Stub\ClassWithMethods::__invoke',
            'Tests\Tokenizer\Stub\ClassWithMethods::privateMethod',
            'Tests\Tokenizer\Stub\ClassWithMethods::protectedMethod',
            'Tests\Tokenizer\Stub\ClassWithMethods::publicMethod',
            'Tests\Tokenizer\Stub\ClassWithMethods::staticMethod',
        ]);
    }

    public function methodsFqnIsEmptyForClassWithoutMethods(): void
    {
        $tokenized = self::tokenize('EmptyClass.php');

        Assert::same($tokenized->getClasses(), ['Tests\Tokenizer\Stub\EmptyClass']);
        Assert::same($tokenized->getMethodsFQN(), []);
    }

    public function methodsFqnIsEmptyForFileWithOnlyFreeFunctions(): void
    {
        $tokenized = self::tokenize('OnlyFunctions.php');

        Assert::same($tokenized->getMethodsFQN(), []);
        $functions = $tokenized->getFunctions();
        \sort($functions);
        Assert::same($functions, [
            'Tests\Tokenizer\Stub\freeFunctionOne',
            'Tests\Tokenizer\Stub\freeFunctionTwo',
        ]);
    }

    public function methodsFqnDoesNotIncludeFreeFunctionsInMixedFile(): void
    {
        $tokenized = self::tokenize('ClassAndFreeFunction.php');

        Assert::same($tokenized->getMethodsFQN(), [
            'Tests\Tokenizer\Stub\ClassWithSingleMethod::aMethod',
        ]);
        Assert::same($tokenized->getFunctions(), [
            'Tests\Tokenizer\Stub\aFreeFunction',
        ]);
    }

    public function methodsFqnHandlesMultipleClassesInOneFile(): void
    {
        $methods = self::tokenize('TwoClassesInOneFile.php')->getMethodsFQN();

        \sort($methods);
        Assert::same($methods, [
            'Tests\Tokenizer\Stub\FirstClass::alpha',
            'Tests\Tokenizer\Stub\FirstClass::beta',
            'Tests\Tokenizer\Stub\SecondClass::gamma',
        ]);
    }

    public function methodsFqnIncludesTraitAndInterfaceMethods(): void
    {
        $methods = self::tokenize('TraitAndInterface.php')->getMethodsFQN();

        \sort($methods);
        Assert::same($methods, [
            'Tests\Tokenizer\Stub\MyInterface::fromInterface',
            'Tests\Tokenizer\Stub\MyTrait::fromTrait',
        ]);
    }

    public function methodsFqnHandlesGlobalNamespaceClasses(): void
    {
        $methods = self::tokenize('NoNamespace/GlobalClass.php')->getMethodsFQN();

        Assert::same($methods, ['TokenizerStubGlobalClass::foo']);
    }

    private static function tokenize(string $stub): TokenizedFile
    {
        $path = __DIR__ . '/../../Stub/' . $stub;
        return new TokenizedFile(new \SplFileInfo($path), $path);
    }
}
