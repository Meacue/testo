<?php

declare(strict_types=1);

namespace Testo;

/**
 * Declares a class, method, or function as a test for the attribute-driven locator.
 *
 * This is the canonical way to opt into Testo's discovery. A class does not need to
 * extend any base class — placing this attribute is enough to make it (or its members)
 * visible to the locator.
 *
 * Behavior depends on the target:
 *
 * On a class — every public method with a {@see void} or {@see never} return type becomes a test.
 * Methods with any other return type are intentionally skipped so they can act as data
 * providers or helpers without an extra attribute. Abstract classes are not picked up.
 *
 * ```
 *  #[Test]
 *  final class OrderTest
 *  {
 *      public function createsOrder(): void { ... }   // discovered
 *      public function calculatesTotal(): void { ... } // discovered
 *
 *      public function orderProvider(): iterable { ... } // skipped (non-void return)
 *      protected function helper(): void { ... }         // skipped (not public)
 *  }
 * ```
 *
 * On a method — only that specific method is registered as a test. Use this when the
 * containing class mixes tests with non-test public methods, or when individual methods
 * need to be opted in explicitly.
 *
 * ```
 *  final class OrderTest
 *  {
 *      #[Test]
 *      public function createsOrder(): void { ... }
 *
 *      public function notATest(): void { ... } // ignored, no attribute
 *  }
 * ```
 *
 * On a function — registers a standalone, class-less test. All function-level tests
 * declared in the same file are grouped under a single synthetic case.
 *
 * ```
 *  #[Test]
 *  function creates_order(): void { ... }
 * ```
 *
 * @api
 */
#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_FUNCTION | \Attribute::TARGET_CLASS)]
final readonly class Test {}
