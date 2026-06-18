<?php

declare(strict_types=1);

namespace Tests\Output\Unit\Teamcity;

use Testo\Assert;
use Testo\Output\Teamcity\Teamcity\Formatter;
use Testo\Test;

#[Test]
final class FormatterTest
{
    public function testFailedWithoutComparisonHasNoExtraAttributes(): void
    {
        $msg = Formatter::testFailed(
            name: 'myTest',
            message: 'something failed',
        );

        Assert::string($msg)->contains("name='myTest'");
        Assert::string($msg)->contains("message='something failed'");
        Assert::string($msg)->notContains("type=");
        Assert::string($msg)->notContains("expected=");
        Assert::string($msg)->notContains("actual=");
    }

    public function testFailedWithComparisonFailureCarriesTypeAndDiffAttributes(): void
    {
        $msg = Formatter::testFailed(
            name: 'myTest',
            message: 'values differ',
            type: 'comparisonFailure',
            expected: 'one',
            actual: 'two',
        );

        Assert::string($msg)->contains("type='comparisonFailure'");
        Assert::string($msg)->contains("expected='one'");
        Assert::string($msg)->contains("actual='two'");
    }

    public function testStartedEmitsDescriptionAsMetainfo(): void
    {
        $msg = Formatter::testStarted(name: 'myTest', description: 'Verifies the widget');

        Assert::string($msg)->contains("name='myTest'");
        Assert::string($msg)->contains("metainfo='Verifies the widget'");
    }

    public function testStartedOmitsMetainfoWhenNoDescription(): void
    {
        $msg = Formatter::testStarted(name: 'myTest');

        Assert::string($msg)->notContains('metainfo=');
    }

    public function testFailedEscapesSpecialCharactersInExpectedAndActual(): void
    {
        $msg = Formatter::testFailed(
            name: 'myTest',
            message: 'fail',
            type: 'comparisonFailure',
            expected: "line1\nline2",
            actual: "[item|with'quote]",
        );

        Assert::string($msg)->contains("expected='line1|nline2'");
        Assert::string($msg)->contains("actual='|[item||with|'quote|]'");
    }
}
