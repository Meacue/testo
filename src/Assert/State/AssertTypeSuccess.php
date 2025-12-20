<?php

declare(strict_types=1);

namespace Testo\Assert\State;

use Testo\Assert\Support;

/**
 * Assertion record.
 */
final class AssertTypeSuccess implements CompositeRecord, Assertion
{
    /** @var list<Assertion> */
    private array $records = [];

    private bool $success = true;

    /**
     * @param non-empty-string $value The actual value that was asserted.
     * @param non-empty-string $assertion The assertion result.
     * @param string $context Optional user-provided context describing what is being asserted.
     */
    private function __construct(
        private readonly string $value,
        public readonly string $assertion,
        public readonly string $context = '',
    ) {}

    /**
     * Success type assertion factory.
     *
     * @param non-empty-string $type The expected type.
     * @param mixed $actual The actual value to compare against the expected type.
     * @param string $message Short description about what exactly is being asserted.
     */
    public static function create(string $type, mixed $actual, string $message): self
    {
        $assertion = \sprintf(
            'Assert that `%s` is %s',
            $value = Support::stringify($actual),
            $type,
        );

        return new self($value, $assertion, $message);
    }

    #[\Override]
    public function isSuccess(): bool
    {
        return $this->success;
    }

    #[\Override]
    public function getContext(): string
    {
        return $this->context;
    }

    /**
     * @param non-empty-string $assertion The assertion result (e.g., "Same: 42", "Assert `true`").
     * @param string $context Optional user-provided context describing what is being asserted.
     */
    public function log(string $assertion, string $context = ''): Record
    {
        return $this->records[] = new Success(
            assertion: $assertion,
            context: $context,
        );
    }

    public function add(Assertion $record): void
    {
        $this->records[] = $record;
        $record->isSuccess() or $this->success = false;
    }

    /**
     * @param non-empty-string $assertion The assertion result (e.g., "greater than 42", "is not empty").
     * @param non-empty-string $reason The reason for the assertion failure.
     * @param string $context Optional user-provided context describing what is being asserted.
     * @param string $details The detailed assertion failure information (diff).
     */
    public function fail(
        string $assertion,
        string $reason,
        string $context = '',
        string $details = '',
    ): AssertionException {
        $err = new AssertionException(
            value: $this->value,
            assertion: $assertion,
            context: $context,
            reason: $reason,
            details: $details,
        );

        $this->success = false;
        return $this->records[] = $err;
    }

    #[\Override]
    public function getRecords(): array
    {
        return $this->records;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getAssertion(): string
    {
        return $this->assertion;
    }

    public function getFailReason(): string
    {
        // TODO: Implement getFailReason() method.
    }

    public function getFailDetails(): string
    {
        // TODO: Implement getFailDetails() method.
    }

    #[\Override]
    public function __toString(): string
    {
        $parts = [$this->assertion];
        foreach ($this->records as $record) {
            $parts[] = $record->__toString(); // todo getAssertion();
        }

        return \implode(', ', $parts) . '.';
    }
}
