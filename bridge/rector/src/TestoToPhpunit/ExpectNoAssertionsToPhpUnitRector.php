<?php

declare(strict_types=1);

namespace Testo\Bridge\Rector\TestoToPhpunit;

use PhpParser\Node;
use PhpParser\Node\Attribute;
use PhpParser\Node\Name\FullyQualified;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Testo\Bridge\Rector\Testing\TestRectorFixtures;

/**
 * Rewrites Testo's `#[\Testo\Assert\ExpectNoAssertions]` attribute into PHPUnit's
 * `#[\PHPUnit\Framework\Attributes\DoesNotPerformAssertions]`.
 *
 * Both are no-argument, declarative markers for "this test performs no assertions",
 * so the rewrite is a plain attribute-name rename; unrelated attributes are left
 * untouched.
 *
 * Both sides are method/function-level only (Testo's `#[ExpectNoAssertions]` is not allowed on a
 * class, mirroring PHPUnit's `#[DoesNotPerformAssertions]`), so there is no class-level fan-out to
 * reconcile — the in-place rename is faithful.
 */
#[TestRectorFixtures('ExpectNoAssertionsToPhpUnitRector')]
final class ExpectNoAssertionsToPhpUnitRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Convert Testo #[\Testo\Assert\ExpectNoAssertions] attribute into PHPUnit #[DoesNotPerformAssertions]',
            [
                new CodeSample(
                    <<<'PHP'
                        #[\Testo\Assert\ExpectNoAssertions]
                        PHP,
                    <<<'PHP'
                        #[\PHPUnit\Framework\Attributes\DoesNotPerformAssertions]
                        PHP,
                ),
            ],
        );
    }

    #[\Override]
    public function getNodeTypes(): array
    {
        return [Attribute::class];
    }

    /**
     * @param Attribute $node
     */
    #[\Override]
    public function refactor(Node $node): ?Node
    {
        if (!$this->isName($node->name, 'Testo\\Assert\\ExpectNoAssertions')) {
            return null;
        }

        $node->name = new FullyQualified('PHPUnit\\Framework\\Attributes\\DoesNotPerformAssertions');

        return $node;
    }
}
