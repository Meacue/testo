<?php

declare(strict_types=1);

namespace Tests\Filter\Unit\Internal;

use Testo\Assert;
use Testo\Codecov\Covers;
use Testo\Core\Definition\CaseDefinitions;
use Testo\Filter;
use Testo\Filter\Internal\FilterInterceptor;
use Testo\Test;
use Testo\Test\Internal\TestoAttributesLocatorInterceptor;
use Testo\Tokenizer\DefinitionLocator;
use Testo\Tokenizer\Reflection\FileDefinitions;
use Testo\Tokenizer\Reflection\TokenizedFile;
use Tests\Filter\Unit\Fixture\AnonymousExtendsFixture;
use Tests\Filter\Unit\Fixture\InheritedNameChild;

/**
 * Filtering by NAME must reach tests a subclass inherits from an abstract base, even though the
 * inherited methods are not tokenized in the subclass's own file.
 *
 * Two stages are exercised against {@see InheritedNameChild} (inherits `inheritedName` and
 * `alsoInheritedName`, declares `childName`):
 * - Stage 1 ({@see FilterInterceptor::locateFile()}): the token pre-filter must not drop the
 *   subclass file when the filter targets an inherited method — otherwise reflection never runs.
 * - Stage 2 ({@see FilterInterceptor::locateTestCases()}): once the file is loaded, reflection sees
 *   the inherited methods on the concrete case and matches them by name.
 *
 * @see \Tests\Filter\Feature\NameFilterFeatureTest for the end-to-end counterpart.
 */
#[Test]
#[Covers(FilterInterceptor::class)]
final class InheritedNameFilterTest
{
    // --- Stage 1: token pre-filter (locateFile) ---

    public function locateMatchesSubclassByClassName(): void
    {
        Assert::true($this->locate(['InheritedNameChild']));
    }

    public function locateMatchesSubclassByOwnMethod(): void
    {
        Assert::true($this->locate(['InheritedNameChild::childName']));
    }

    public function locateMatchesSubclassByInheritedClassMethod(): void
    {
        # The inherited method has no token in the subclass file, but the test it backs lives on the
        # concrete subclass — the pre-filter must keep the file so Stage 2 can resolve it.
        Assert::true($this->locate(['InheritedNameChild::inheritedName']));
    }

    public function locateMatchesSubclassByInheritedMethodFragment(): void
    {
        Assert::true($this->locate(['inheritedName']));
    }

    public function locateIgnoresAnonymousClassExtends(): void
    {
        # The fixture's only `extends` is on an anonymous class inside a method body. It declares no
        # named subclass, so a fragment matching nothing here must not keep the file: an anonymous
        # class can never be a discoverable test case.
        $file = self::tokenizedFileOf(AnonymousExtendsFixture::class);

        Assert::false($this->locate(['noSuchMethod'], $file));
        # Sanity: the fixture is otherwise a normal, matchable case.
        Assert::true($this->locate(['ownTest'], $file));
    }

    // --- Stage 2: case/test name matching (locateTestCases) ---

    public function selectMatchesInheritedTestByClassMethod(): void
    {
        Assert::same($this->select(['InheritedNameChild::inheritedName']), ['inheritedName']);
    }

    public function selectMatchesInheritedTestByFragment(): void
    {
        Assert::same($this->select(['inheritedName']), ['inheritedName']);
    }

    public function selectMatchesWholeSubclassCaseByClassName(): void
    {
        Assert::same(
            $this->select(['InheritedNameChild']),
            ['alsoInheritedName', 'childName', 'inheritedName'],
        );
    }

    /**
     * Run Stage 1 against a fixture file (the subclass fixture by default) and return the decision.
     *
     * @param list<non-empty-string> $names
     */
    private function locate(array $names, ?TokenizedFile $file = null): bool
    {
        return (bool) (new FilterInterceptor(new Filter(names: $names)))
            ->locateFile($file ?? self::childFile(), static fn(TokenizedFile $f): bool => true);
    }

    /**
     * Run the locator chain (discovery + name filter) for the subclass fixture and return the sorted
     * names of the tests that survive.
     *
     * @param list<non-empty-string> $names
     * @return list<string>
     */
    private function select(array $names): array
    {
        $file = self::childFile();
        $definition = new FileDefinitions(
            $file,
            classes: DefinitionLocator::getClasses($file),
            functions: DefinitionLocator::getFunctions($file),
        );

        (new TestoAttributesLocatorInterceptor())
            ->locateTestCases($definition, static fn(FileDefinitions $f): CaseDefinitions => $f->cases);

        $cases = (new FilterInterceptor(new Filter(names: $names)))
            ->locateTestCases($definition, static fn(FileDefinitions $f): CaseDefinitions => $f->cases);

        $names = [];
        foreach ($cases->getCases() as $case) {
            foreach ($case->tests->getTests() as $name => $_) {
                $names[] = $name;
            }
        }

        \sort($names);

        return $names;
    }

    private static function childFile(): TokenizedFile
    {
        return self::tokenizedFileOf(InheritedNameChild::class);
    }

    /**
     * @param class-string $class
     */
    private static function tokenizedFileOf(string $class): TokenizedFile
    {
        $path = (new \ReflectionClass($class))->getFileName();
        \assert($path !== false);

        return new TokenizedFile(file: new \SplFileInfo($path), path: $path);
    }
}
