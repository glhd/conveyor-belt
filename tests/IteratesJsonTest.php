<?php

namespace Glhd\ConveyorBelt\Tests;

use Glhd\ConveyorBelt\Tests\Commands\TestJsonFileCommand;
use Glhd\ConveyorBelt\Tests\Concerns\CallsTestCommands;
use Illuminate\Support\Str;
use RuntimeException;

class IteratesJsonTest extends TestCase
{
	use CallsTestCommands;
	
	/** @dataProvider dataProvider */
	public function test_it_reads_json_files(string $filename, bool $step, $exceptions): void
	{
		$pointer = Str::contains($filename, '-nested')
			? '/results/people'
			: null;
		
		$expectations = [
			(object) ['full_name' => 'Chris Morrell', 'company' => 'Galahad, Inc.', 'quote' => '"I hate final classes."'],
			(object) ['full_name' => 'Bogdan Kharchenko', 'company' => 'Galahad, Inc.', 'quote' => '"It works."'],
			(object) ['full_name' => 'Mohamed Said', 'company' => 'Laravel LLC', 'quote' => null],
			(object) ['full_name' => 'Taylor Otwell', 'company' => 'Laravel LLC', 'quote' => '"No plans to merge."'],
		];
		
		$this->registerHandleRowCallback(function($row) use (&$expectations, $exceptions) {
			$expected = array_shift($expectations);
			$this->assertEquals($expected, $row);
			
			if ($exceptions && 'Bogdan Kharchenko' === $row->full_name) {
				throw new RuntimeException('This should be caught.');
			}
		});
		
		$this->callTestCommand(TestJsonFileCommand::class)
			->withArgument('filename', $filename)
			->withOption('pointer', $pointer)
			->withStepMode($step)
			->expectingSuccessfulReturnCode(false === $exceptions)
			->throwingExceptions('throw' === $exceptions)
			->run();
		
		$this->assertEmpty($expectations);
		$this->assertHookMethodsWereCalledInExpectedOrder();
	}
	
	public function dataProvider()
	{
		return $this->getDataProvider(
			['root json' => __DIR__.'/sources/people.json', 'nested json' => __DIR__.'/sources/people-nested.json'],
			['' => false, 'step mode' => true],
			['' => false, 'throw exceptions' => 'throw', 'collect exceptions' => 'collect'],
		);
	}
}
