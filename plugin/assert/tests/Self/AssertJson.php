<?php

declare(strict_types=1);

namespace Tests\Assert\Self;

use Testo\Assert;
use Testo\Assert\Api\Json\JsonAbstract;
use Testo\Assert\Internal\Assertion\AssertJson as AssertJsonImpl;
use Testo\Assert\Internal\Assertion\Json\TypeMatcher;
use Testo\Assert\State\Assertion\AssertionException;
use Testo\Codecov\Covers;
use Testo\Data\DataProvider;
use Testo\Data\DataSet;
use Testo\Expect;
use Testo\Test;

/**
 * @see Assert::json()
 */
#[Covers(Assert::class, 'json')]
#[Covers(AssertJsonImpl::class)]
#[Covers(TypeMatcher::class)]
final class AssertJson
{
    public static function types(): iterable
    {
        yield ['false', 'bool'];
        yield ['true', 'bool'];
        yield ['42', 'int'];
        yield ['3.14', 'float'];
        yield ['"hello"', 'string'];
        yield ['"hello world"', 'non-empty-string'];
        yield ['"1234"', 'numeric-string'];
        yield [
            <<<JSON
                {
                    "id": 1,
                    "name": "test"
                }
                JSON,
            'array{id: int, name: non-empty-string}',
        ];
    }

    /**
     * @see \Testo\Assert\Api\Json\JsonCommon::matchesType()
     */
    #[Test]
    #[DataProvider('types')]
    public function matchesType(string $json, string $type): void
    {
        Assert::json($json)->matchesType($type);
    }

    /**
     * @param non-empty-string $json
     * @param non-empty-string $type
     */
    #[Test]
    #[DataSet(['42', 'string'], 'int is not string')]
    #[DataSet(['"hello"', 'int'], 'string is not int')]
    #[DataSet(['{"id": 1}', 'array{id: non-empty-string}'], 'shape value type mismatch')]
    public function matchesTypeFails(string $json, string $type): never
    {
        Expect::exception(AssertionException::class);
        Assert::json($json)->matchesType($type);
    }

    /**
     * @see \Testo\Assert\Api\Json\JsonStructure
     * @see \Testo\Assert\Api\Json\JsonArray
     * @see \Testo\Assert\Api\Json\JsonObject
     */
    #[Test]
    public function assertStructure(): void
    {
        $json = <<<JSON
            [
                { "id": 1, "name": "Alice" },
                { "id": 2, "name": "Bob" },
                { "id": 3, "name": "Charlie" }
            ]
            JSON;

        # Assert that the JSON is a list of objects with specific structure
        Assert::json($json)
            ->isStructure()
            ->count(3)
            ->matchesType('list<array{id: int<0, max>, name: non-empty-string}>');

        # As an array, assert the first element's structure
        Assert::json($json)
            ->isArray()
            ->count(3)
            ->assertPath('$[0]', static fn(JsonAbstract $json) => $json
                ->isObject()
                ->count(2)
                ->hasKeys(['id', 'name'])
                ->matchesType('array{id: int<0, max>, name: non-empty-string}'));
    }

    #[Test]
    public function invalidJson(): never
    {
        Expect::exception(AssertionException::class);
        Assert::json('{invalid}');
    }

    #[Test]
    public function decode(): void
    {
        Assert::same(Assert::json('42')->decode(), 42);
        Assert::same(Assert::json('"hello"')->decode(), 'hello');
        Assert::same(Assert::json('true')->decode(), true);
        Assert::same(Assert::json('null')->decode(), null);
    }

    #[Test]
    public function isObject(): void
    {
        Assert::json('{"id": 1}')->isObject();
        Assert::json('{}')->isObject();
    }

    /**
     * @param non-empty-string $json
     */
    #[Test]
    #[DataSet(['[1, 2, 3]'], 'array is not object')]
    #[DataSet(['42'], 'primitive is not object')]
    public function isObjectFails(string $json): never
    {
        Expect::exception(AssertionException::class);
        Assert::json($json)->isObject();
    }

    #[Test]
    public function isArray(): void
    {
        Assert::json('[1, 2, 3]')->isArray();
        Assert::json('[]')->isArray();
    }

    /**
     * @param non-empty-string $json
     */
    #[Test]
    #[DataSet(['{"id": 1}'], 'object is not array')]
    #[DataSet(['42'], 'primitive is not array')]
    public function isArrayFails(string $json): never
    {
        Expect::exception(AssertionException::class);
        Assert::json($json)->isArray();
    }

    #[Test]
    public function isPrimitive(): void
    {
        Assert::json('42')->isPrimitive();
        Assert::json('"hello"')->isPrimitive();
        Assert::json('true')->isPrimitive();
        Assert::json('null')->isPrimitive();
        Assert::json('3.14')->isPrimitive();
    }

    /**
     * @param non-empty-string $json
     */
    #[Test]
    #[DataSet(['{"id": 1}'], 'object is not primitive')]
    #[DataSet(['[1, 2, 3]'], 'array is not primitive')]
    public function isPrimitiveFails(string $json): never
    {
        Expect::exception(AssertionException::class);
        Assert::json($json)->isPrimitive();
    }

    #[Test]
    public function emptyStructures(): void
    {
        Assert::json('{}')->empty();
        Assert::json('[]')->empty();
    }

    /**
     * @param non-empty-string $json
     */
    #[Test]
    #[DataSet(['[1, 2, 3]'], 'non-empty array')]
    #[DataSet(['{"a": 1}'], 'non-empty object')]
    #[DataSet(['42'], 'primitive is not a structure')]
    public function emptyStructuresFails(string $json): never
    {
        Expect::exception(AssertionException::class);
        Assert::json($json)->empty();
    }

    #[Test]
    public function countElements(): void
    {
        Assert::json('[1, 2, 3]')->count(3);
        Assert::json('{"a": 1, "b": 2}')->count(2);
        Assert::json('[]')->count(0);
    }

    #[Test]
    public function countElementsFails(): never
    {
        Expect::exception(AssertionException::class)
            ->withMessageContaining('my wonderful message');
        Assert::json('[1, 2, 3]')->count(5, 'my wonderful message');
    }

    #[Test]
    public function hasKeys(): void
    {
        Assert::json('{"id": 1, "name": "test", "email": "a@b.com"}')
            ->hasKeys(['id', 'name'])
            ->hasKeys('email');
    }

    #[Test]
    public function hasKeysFails(): never
    {
        Expect::exception(AssertionException::class)
            ->withMessageContaining('my wonderful message');
        Assert::json('{"id": 1}')->hasKeys(['id', 'missing'], 'my wonderful message');
    }

    #[Test]
    public function maxDepth(): void
    {
        Assert::json('42')->maxDepth(1);
        Assert::json('[1, 2, 3]')->maxDepth(1);
        Assert::json('{"a": {"b": 1}}')->maxDepth(2);
        Assert::json('{"a": {"b": {"c": 1}}}')->maxDepth(3);
    }

    #[Test]
    public function maxDepthFails(): never
    {
        Expect::exception(AssertionException::class);
        Assert::json('{"a": {"b": {"c": 1}}}')->maxDepth(2);
    }

    #[Test]
    public function assertPathDotNotation(): void
    {
        $json = '{"user": {"name": "Alice", "age": 30}}';

        Assert::json($json)
            ->assertPath('$.user', static fn(JsonAbstract $json) => $json
                ->isObject()
                ->hasKeys(['name', 'age']));

        Assert::json($json)
            ->assertPath('$.user.name', static fn(JsonAbstract $json) => $json
                ->isPrimitive()
                ->matchesType('non-empty-string'));
    }

    #[Test]
    public function assertPathFails(): never
    {
        Expect::exception(AssertionException::class);
        Assert::json('{"user": {"name": "Alice"}}')
            ->assertPath('$.user.missing', static fn(JsonAbstract $json) => $json->isPrimitive());
    }

    #[Test]
    public function matchesTypeUnion(): void
    {
        Assert::json('42')->matchesType('int|string');
        Assert::json('"hello"')->matchesType('int|string');
        Assert::json('null')->matchesType('int|null');
    }

    #[Test]
    public function matchesTypeOptionalShape(): void
    {
        Assert::json('{"id": 1}')
            ->matchesType('array{id: int, name?: string}');

        Assert::json('{"id": 1, "name": "test"}')
            ->matchesType('array{id: int, name?: string}');
    }
}
