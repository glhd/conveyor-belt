<?php

namespace Glhd\ConveyorBelt\Tests;

use Glhd\ConveyorBelt\Tests\Commands\TestQueryCommand;
use Glhd\ConveyorBelt\Tests\Commands\TestSpreadsheetCommand;
use Glhd\ConveyorBelt\Tests\Concerns\TestsCommonCommandVariations;
use Illuminate\Support\Str;
use RuntimeException;

class IteratesSpreadsheetTest extends TestCase
{
	use TestsCommonCommandVariations;
	
	/** @dataProvider dataProvider */
	public function test_it_reads_spreadsheets(string $filename, bool $step, $exceptions): void
	{
		// FIXME: Test dates
		
		$expectations = [
			(object) ['full_name' => 'Chris Morrell', 'company' => 'Galahad, Inc.', 'quote' => '"I hate final classes."'],
			(object) ['full_name' => 'Bogdan Kharchenko', 'company' => 'Galahad, Inc.', 'quote' => '"It works."'],
			(object) ['full_name' => 'Mohamed Said', 'company' => 'Laravel LLC', 'quote' => ''],
			(object) ['full_name' => 'Taylor Otwell', 'company' => 'Laravel LLC', 'quote' => '"No plans to merge."'],
		];
		
		$this->registerHandleRowCallback(function($row) use (&$expectations, $exceptions) {
			$expected = array_shift($expectations);
			$this->assertEquals($expected, $row);
			
			if ($exceptions && 'Bogdan Kharchenko' === $row->full_name) {
				throw new RuntimeException('This should be caught.');
			}
		});
		
		$command = $this->setUpCommandWithCommonAssertions($exceptions, $step, TestSpreadsheetCommand::class, [
			'filename' => $filename,
		]);
		
		$command->run();
		
		$this->assertEmpty($expectations);
		$this->assertHookMethodsWereCalledInExpectedOrder();
	}
	
	public function dataProvider()
	{
		$filenames = [
			__DIR__.'/sources/people.csv',
			__DIR__.'/sources/people.xlsx',
		];
		
		foreach ($filenames as $filename) {
			foreach ([false, true] as $step) {
				foreach ([false, 'throw', 'collect'] as $exceptions) {
					$label = (implode('; ', array_filter([
						Str::of($filename)->afterLast('.')->upper(),
						$step
							? 'step mode'
							: null,
						$exceptions
							? "{$exceptions} exceptions"
							: null,
					])));
					
					yield $label => [$filename, $step, $exceptions];
				}
			}
		}
	}
}
