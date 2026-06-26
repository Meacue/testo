<?php

declare(strict_types=1);

namespace Testo\Assert;

use Testo\Assert\Internal\Middleware\ExpectationsInterceptor;
use Testo\Core\Value\Status;
use Testo\Pipeline\Attribute\FallbackInterceptor;
use Testo\Pipeline\Attribute\Interceptable;

/**
 * Declares that a test intentionally performs no assertions.
 *
 * The attribute is a two-way contract checked at the end of the run:
 *
 * - a test marked with it that records **no** assertion stays {@see Status::Passed} (without it such
 *   a test would be reported as {@see Status::Risky} — the framework would assume a forgotten assertion);
 * - a test marked with it that nevertheless records assertions is reported as {@see Status::Risky},
 *   because the declaration no longer holds (a stale or misapplied attribute). Note that expecting an
 *   exception (via {@see Expect::exception()} or {@see ExpectException}) is itself an assertion, so
 *   pairing it with this attribute is contradictory and comes out {@see Status::Risky}.
 *
 * Can be placed on a single test (method or function) or on a whole Test Case (class), in which case
 * it covers every test of that class.
 *
 * @api
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::TARGET_FUNCTION)]
#[FallbackInterceptor(ExpectationsInterceptor::class)]
final readonly class ExpectNoAssertions implements Interceptable {}
