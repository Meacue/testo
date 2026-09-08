<?php

declare(strict_types=1);

namespace Tests\Test\Stub\Skip;

use Testo\Assert;
use Testo\Test;
use Testo\Test\Skip;

# Proves #[Skip] reaches a function-based case as well: the test is reported as Skipped and its
# message is built from the function FQN.
#[Test]
#[Skip('functional test is parked')]
function parked_function(): void
{
    throw new \LogicException('Must never run: the test is parked.');
}

# Control neighbor of the same case: an enabled function next to a skipped one still runs through
# the batch runner the interceptor installs on the case, and passes.
#[Test]
function enabled_function(): void
{
    Assert::true(true);
}
