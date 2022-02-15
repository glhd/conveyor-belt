<?php

namespace Glhd\ConveyorBelt\Tests;

use Glhd\ConveyorBelt\Tests\Commands\TestSpreadsheetCommand;
use Illuminate\Support\Str;
use RuntimeException;

class IteratesSpreadsheetTest extends TestCase
{
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
		
		if ('throw' === $exceptions) {
			$this->expectException(RuntimeException::class);
		}
		
		$command = $this->artisan(TestSpreadsheetCommand::class, [
			'filename' => $filename,
			'--step' => $step,
			'--throw' => 'throw' === $exceptions,
		]);
		
		if ($step && 'throw' === $exceptions) {
			// If we're throwing exceptions, we'll only have 1 successful iteration
			$command->expectsQuestion('Continue?', true);
		} elseif ($step) {
			// Otherwise we should have 4 iterations
			foreach (range(1, 4) as $_) {
				$command->expectsQuestion('Continue?', true);
			}
		}
		
		if ($exceptions) {
			$command->assertFailed();
		} else {
			$command->assertSuccessful();
		}
		
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
