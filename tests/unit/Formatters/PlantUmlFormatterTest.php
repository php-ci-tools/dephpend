<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Formatters;

use Mihaeu\PhpDependencies\Dependencies\Clazz;
use Mihaeu\PhpDependencies\Dependencies\DependencyPair;
use Mihaeu\PhpDependencies\Dependencies\DependencyPairCollection;

/**
 * @covers Mihaeu\PhpDependencies\Formatters\PlantUmlFormatter
 */
class PlantUmlFormatterTest extends \PHPUnit_Framework_TestCase
{
    /** @var PlantUmlFormatter */
    private $plantUmlFormatter;

    public function setUp()
    {
        $this->plantUmlFormatter = new PlantUmlFormatter();
    }

    public function testFormat()
    {
        $dependencyCollection = (new DependencyPairCollection())
            ->add(new DependencyPair(new Clazz('ClassA'), new Clazz('ClassB')))
            ->add(new DependencyPair(new Clazz('ClassA'), new Clazz('ClassC')));
        $this->assertEquals("@startuml\n"
            ."ClassA --|> ClassB\n"
            ."ClassA --|> ClassC\n"
            .'@enduml', $this->plantUmlFormatter->format($dependencyCollection));
    }
}