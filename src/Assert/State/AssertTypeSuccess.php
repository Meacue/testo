<?php

declare(strict_types=1);

namespace Testo\Assert\State;

use Testo\Assert\Support;

/**
 * Assertion record.
 */
final class AssertTypeSuccess implements CompositeRecord
{
    private array $records = [];
    private bool $success = true;

    /**
     * @param non-empty-string $assertion The assertion result (e.g., "Same: 42", "Assert `true`").
     * @param string $context Optional user-provided context describing what is being asserted.
     */
    private function __construct(
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
            'Assert type `%s` for %s.',
            $type,
            Support::stringify($actual),
        );

        return new self($assertion, $message);
    }

    #[\Override]
    public function isSuccess(): bool
    {
        return true;
    }

    #[\Override]
    public function getContext(): ?string
    {
        return $this->context !== '' ? $this->context : null;
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

    public function add(Record $record): void
    {
        $this->records[] = $record;
        // $record->isSuccess() or $this->success = false;
    }

    /**
     * Log a failed assertion and throw the given exception.
     */
    public function fail(Record&\Throwable $failure): never
    {
        // $this->success = false;
        $this->records[] = $failure;
        throw $failure;
    }

    #[\Override]
    public function getRecords(): array
    {
        return $this->records;
    }

    #[\Override]
    public function __toString(): string
    {
        return $this->assertion;
    }
}
