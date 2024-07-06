<?php

declare(strict_types=1);

namespace Ray\Aop;

use PHPUnit\Framework\TestCase;
use Ray\Aop\Aspect\Fake\src\FakeMyClass;
use Ray\Aop\Matcher\AnyMatcher;
use Ray\Aop\Matcher\StartsWithMatcher;
use function get_class;

/** @requires PHP 8.1 */
class AspectTest extends TestCase
{
    private $aspect;

    protected function setUp(): void
    {
        $this->aspect = new Aspect(__DIR__ . '/Fake/src');
    }

    /**
     * @runInSeparateProcess
     *
     * isolated process is required to avoid side effects which can be caused by the aspect weaved classes
     */
    public function testWeave(): void
    {
        $this->aspect->bind(
            new AnyMatcher(),
            new StartsWithMatcher('my'),
            [new FakeMyInterceptor()]
        );
        $this->aspect->weave();
        // here we are testing the interception!
        $myClass = new FakeMyClass();
        $result = $myClass->myMethod();
        $this->assertSame(get_class($myClass), FakeMyClass::class);
        // the original method is intercepted
        $this->assertEquals('intercepted original', $result);
    }

    public function testNewInstance(): void
    {
        $this->aspect->bind(
            new AnyMatcher(),
            new StartsWithMatcher('my'),
            [new FakeMyInterceptor()]
        );
        $myClass = $this->aspect->newInstance(FakeMyClass::class);
        $this->assertNotSame(get_class($myClass), FakeMyClass::class);
        $result = $myClass->myMethod();
        // the original method is intercepted
        $this->assertEquals('intercepted original', $result);
    }

    public function testNewInstanceWithNoBound(): void
    {
        $insntance = $this->aspect->newInstance(FakeMyClass::class);
        $this->assertInstanceOf(FakeMyClass::class, $insntance);
    }
}
