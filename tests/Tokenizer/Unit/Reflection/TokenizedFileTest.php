<?php

declare(strict_types=1);

namespace Tests\Tokenizer\Unit\Reflection;

use Testo\Assert;
use Testo\Codecov\Covers;
use Testo\Test;
use Testo\Tokenizer\Reflection\TokenizedFile;

#[Test]
#[Covers(TokenizedFile::class)]
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

    public function topLevelClosuresAreNotRegisteredAsFreeFunctions(): void
    {
        $tokenized = self::tokenize('ClosuresInFreeFunctionScope.php');

        Assert::same($tokenized->getFunctions(), [
            'Tests\Tokenizer\Stub\realFreeFunction',
        ]);
        Assert::same($tokenized->getMethodsFQN(), []);
    }

    public function closuresInsideMethodsDoNotLeakIntoDeclarations(): void
    {
        $tokenized = self::tokenize('ClosuresInsideMethods.php');

        $methods = $tokenized->getMethodsFQN();
        \sort($methods);
        Assert::same($methods, [
            'Tests\Tokenizer\Stub\UsesClosures::build',
            'Tests\Tokenizer\Stub\UsesClosures::map',
        ]);
        Assert::same($tokenized->getFunctions(), []);
    }

    public function attributedAnonymousFunctionIsSkippedButNamedOneIsKept(): void
    {
        $tokenized = self::tokenize('AttributedClosure.php');

        Assert::same($tokenized->getFunctions(), [
            'Tests\Tokenizer\Stub\namedWithAttribute',
        ]);
        Assert::same($tokenized->getMethodsFQN(), []);
    }

    public function anonymousClassMethodsAtFileScopeDoNotLeakIntoFunctions(): void
    {
        $tokenized = self::tokenize('AnonymousClassInFreeScope.php');

        Assert::same($tokenized->getFunctions(), [
            'Tests\Tokenizer\Stub\freeAlongsideAnon',
        ]);
        Assert::same($tokenized->getMethodsFQN(), []);
        Assert::same($tokenized->getClasses(), []);
    }

    public function anonymousClassMethodsNestedInDeclarationsAreNotMisattributed(): void
    {
        $tokenized = self::tokenize('AnonymousClassNestedInDeclarations.php');

        Assert::same($tokenized->getFunctions(), [
            'Tests\Tokenizer\Stub\makesAnon',
        ]);
        $methods = $tokenized->getMethodsFQN();
        \sort($methods);
        Assert::same($methods, [
            'Tests\Tokenizer\Stub\HostsAnon::make',
            'Tests\Tokenizer\Stub\HostsAnon::plainMethod',
        ]);
        Assert::same($tokenized->getClasses(), ['Tests\Tokenizer\Stub\HostsAnon']);
    }

    public function anonymousClassWithAttributeIsStillAnonymous(): void
    {
        $tokenized = self::tokenize('NewWithAttributeAnonymousClass.php');

        Assert::same($tokenized->getFunctions(), []);
        Assert::same($tokenized->getMethodsFQN(), []);
        Assert::same($tokenized->getClasses(), []);
    }

    public function declarationsAreScopedToTheirBracedNamespace(): void
    {
        $tokenized = self::tokenize('BracedNamespaces.php');

        $functions = $tokenized->getFunctions();
        \sort($functions);
        Assert::same($functions, [
            'Tests\Tokenizer\Stub\First\alpha',
            'Tests\Tokenizer\Stub\Second\beta',
        ]);

        $methods = $tokenized->getMethodsFQN();
        \sort($methods);
        Assert::same($methods, [
            'Tests\Tokenizer\Stub\First\FirstClass::fa',
            'Tests\Tokenizer\Stub\Second\SecondClass::sb',
        ]);

        $classes = $tokenized->getClasses();
        \sort($classes);
        Assert::same($classes, [
            'Tests\Tokenizer\Stub\First\FirstClass',
            'Tests\Tokenizer\Stub\Second\SecondClass',
        ]);
    }

    public function keywordNamedMethodsAreDetectedByPosition(): void
    {
        $tokenized = self::tokenize('KeywordNamedDeclarations.php');

        $methods = $tokenized->getMethodsFQN();
        \sort($methods);
        Assert::same($methods, [
            'Tests\Tokenizer\Stub\KeywordNames::list',
            'Tests\Tokenizer\Stub\KeywordNames::print',
            'Tests\Tokenizer\Stub\KeywordNames::unset',
        ]);
        Assert::same($tokenized->getFunctions(), []);
    }

    public function nestedAnonymousClassBoundariesAreResolvedCorrectly(): void
    {
        $tokenized = self::tokenize('NestedAnonymousClasses.php');

        Assert::same($tokenized->getFunctions(), [
            'Tests\Tokenizer\Stub\afterParentAnon',
        ]);
        Assert::same($tokenized->getMethodsFQN(), [
            'Tests\Tokenizer\Stub\RealAfterAnon::realMethod',
        ]);
        Assert::same($tokenized->getClasses(), ['Tests\Tokenizer\Stub\RealAfterAnon']);
    }

    private static function tokenize(string $stub): TokenizedFile
    {
        $path = __DIR__ . '/../../Stub/' . $stub;
        return new TokenizedFile(new \SplFileInfo($path), $path);
    }
}
