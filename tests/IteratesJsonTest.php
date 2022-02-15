<?php

namespace Glhd\ConveyorBelt\Tests;

use Glhd\ConveyorBelt\Tests\Commands\TestIdQueryCommand;
use Glhd\ConveyorBelt\Tests\Commands\TestJsonFileCommand;
use Glhd\ConveyorBelt\Tests\Concerns\TestsCommonCommandVariations;
use Illuminate\Support\Str;
use RuntimeException;

class IteratesJsonTest extends TestCase
{
	use TestsCommonCommandVariations;
	
	/** @dataProvider dataProvider */
	public function test_it_reads_json_files(string $filename, ?string $pointer, bool $step, $exceptions): void
	{
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
		
		$command = $this->setUpCommandWithCommonAssertions($exceptions, $step, TestJsonFileCommand::class, [
			'filename' => $filename,
			'--pointer' => $pointer,
		]);
		
		$command->run();
		
		$this->assertEmpty($expectations);
		$this->assertHookMethodsWereCalledInExpectedOrder();
	}
	
	public function dataProvider()
	{
		$cases = [
			(object) ['filename' => __DIR__.'/sources/people.json', 'pointer' => null],
			(object) ['filename' => __DIR__.'/sources/people-nested.json', 'pointer' => '/results/people'],
		];
		
		foreach ($cases as $case) {
			foreach ([false, true] as $step) {
				foreach ([false, 'throw', 'collect'] as $exceptions) {
					$label = (implode('; ', array_filter([
						Str::of($case->filename)->afterLast('/')->beforeLast('.'),
						$step
							? 'step mode'
							: null,
						$exceptions
							? "{$exceptions} exceptions"
							: null,
					])));
					
					yield $label => [$case->filename, $case->pointer, $step, $exceptions];
				}
			}
		}
	}
}
