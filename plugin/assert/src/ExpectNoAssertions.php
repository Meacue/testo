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
 * Placed on a single test — a method or a function. It is intentionally not allowed on a class:
 * "no test in this whole case asserts anything" is almost never a real contract, and a stray
 * class-level marker would silently turn every genuinely-asserting test into {@see Status::Risky}.
 *
 * @api
 */
#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_FUNCTION)]
#[FallbackInterceptor(ExpectationsInterceptor::class)]
final readonly class ExpectNoAssertions implements Interceptable {}
