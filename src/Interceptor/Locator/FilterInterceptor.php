<?php

declare(strict_types=1);

namespace Testo\Interceptor\Locator;

use Testo\Common\Filter;
use Testo\Interceptor\FileLocatorInterceptor;
use Testo\Module\Tokenizer\Reflection\TokenizedFile;

final class FilterInterceptor implements FileLocatorInterceptor
{
    public function __construct(
        private readonly Filter $filter,
    ) {}

    public function locateFile(TokenizedFile $file, callable $next): ?bool
    {
        $failed = false;

        $this->filter->names === [] or $failed = !$this->matchNames($file, $this->filter->names);

        return $failed ? false : $next($file);
    }

    /**
     * @param non-empty-list<non-empty-string> $names
     * @return bool True if any name matches, false otherwise.
     */
    private function matchNames(TokenizedFile $file, array $names): bool
    {
        foreach ($file->getFunctions() as $fqn) {
            foreach ($names as $name) {
                if (\preg_match('/\\b' . \preg_quote($name, '/') . '\\b/', $fqn) === 1) {
                    return true;
                }
            }
        }

        foreach ($file->getMethodsFQN() as $fqn) {
            foreach ($names as $name) {
                if (\preg_match('/\\b' . \preg_quote($name, '/') . '\\b/', $fqn) === 1) {
                    return true;
                }
            }
        }

        return false;
    }
}
