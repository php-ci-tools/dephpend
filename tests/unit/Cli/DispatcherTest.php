<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Cli;

use Mihaeu\PhpDependencies\Analyser\StaticAnalyser;
use Mihaeu\PhpDependencies\Analyser\XDebugFunctionTraceAnalyser;
use Mihaeu\PhpDependencies\Dependencies\DependencyFilter;
use Mihaeu\PhpDependencies\OS\PhpFileFinder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleEvent;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * @covers Mihaeu\PhpDependencies\Cli\Dispatcher
 */
class DispatcherTest extends TestCase
{
    /** @var Dispatcher */
    private $dispatcher;

    /** @var StaticAnalyser | MockObject */
    private $staticAnalyser;

    /** @var XDebugFunctionTraceAnalyser | MockObject */
    private $xDebugFunctionTraceAnalyser;

    /** @var PhpFileFinder | MockObject */
    private $phpFileFinder;

    /** @var DependencyFilter | MockObject */
    private $dependencyFilter;

    protected function setUp()
    {
        $this->staticAnalyser = $this->createMock(StaticAnalyser::class);
        $this->xDebugFunctionTraceAnalyser = $this->createMock(XDebugFunctionTraceAnalyser::class);
        $this->phpFileFinder = $this->createMock(PhpFileFinder::class);
        $this->dependencyFilter = $this->createMock(DependencyFilter::class);

        $this->dispatcher = new Dispatcher(
            $this->staticAnalyser,
            $this->xDebugFunctionTraceAnalyser,
            $this->phpFileFinder,
            $this->dependencyFilter
        );
    }

    public function testTriggersOnlyOnNamedConsoleEvents()
    {
        $consoleEvent = $this->createMock(ConsoleEvent::class);
        $consoleEvent->expects(never())->method('getInput');
        $this->dispatcher->dispatch('other event', $consoleEvent);
    }

    public function testTriggersOnlyOnConsoleEvents()
    {
        $consoleEvent = $this->createMock(Event::class);
        assertEquals(
            $consoleEvent,
            $this->dispatcher->dispatch(ConsoleEvents::COMMAND, clone $consoleEvent)
        );
    }

    public function testInjectsDependenciesIntoConsoleEvents()
    {
        $command = $this->createMock(BaseCommand::class);

        $consoleEvent = $this->createMock(ConsoleEvent::class);
        $consoleEvent->method('getCommand')->willReturn($command);

        $input = $this->createMock(InputInterface::class);
        $input->method('getArgument')->with('source')->willReturn([]);
        $input->method('getOptions')->willReturn([]);
        $traceFile = sys_get_temp_dir();
        $input->method('getOption')->with('dynamic')->willReturn($traceFile);
        $consoleEvent->method('getInput')->willReturn($input);

        $this->xDebugFunctionTraceAnalyser->expects(once())->method('analyse')->with($traceFile);
        $command->expects(once())->method('setDependencies');
        $command->expects(once())->method('setPostProcessors');
        $this->dispatcher->dispatch(ConsoleEvents::COMMAND, clone $consoleEvent);
    }
}
