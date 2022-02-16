<?php

namespace Glhd\ConveyorBelt\Tests;

use Glhd\ConveyorBelt\Tests\Commands\TestJsonEndpointCommand;
use Glhd\ConveyorBelt\Tests\Commands\TestJsonFileCommand;
use Glhd\ConveyorBelt\Tests\Concerns\CallsTestCommands;
use GuzzleHttp\Psr7\PumpStream;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class IteratesJsonTest extends TestCase
{
	use CallsTestCommands;
	
	/** @dataProvider fileDataProvider */
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
			
			if ($exceptions) {
				$this->triggerExceptionAfterTimes(1);
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
	
	public function fileDataProvider()
	{
		return $this->getDataProvider(
			['root json' => __DIR__.'/sources/people.json', 'nested json' => __DIR__.'/sources/people-nested.json'],
			['' => false, 'step mode' => true],
			['' => false, 'throw exceptions' => 'throw', 'collect exceptions' => 'collect'],
		);
	}
	
	/** @dataProvider endpointDataProvider */
	public function test_it_streams_json_api_data($step, $exceptions): void
	{
		$stub = file_get_contents(__DIR__.'/sources/botw.json');
		
		// This forces the JSON to be read in small chunks, which lets us test
		// whether the JsonMachine parser is working as expected
		$chunks = str_split($stub, random_int(50, 200));
		$stream = new PumpStream(function() use (&$chunks) {
			return count($chunks)
				? array_shift($chunks)
				: false;
		});
		
		Http::fake([
			'botw-compendium.herokuapp.com/*' => Http::response($stream, 200, ['content-type' => 'application/json']),
		]);
		
		$botw = json_decode($stub);
		$equipment = $botw->data->equipment;
		
		$this->registerHandleRowCallback(function($row) use ($exceptions, &$equipment) {
			$expected = array_shift($equipment);
			$this->assertEquals($expected, $row);
			
			if ($exceptions) {
				$this->triggerExceptionAfterTimes(1);
			}
		});
		
		$this->callTestCommand(TestJsonEndpointCommand::class)
			->expectingSuccessfulReturnCode(false === $exceptions)
			->throwingExceptions('throw' === $exceptions)
			->withStepMode($step, count($equipment))
			->run();
		
		$this->assertEmpty($equipment);
		$this->assertHookMethodsWereCalledInExpectedOrder();
	}
	
	public function endpointDataProvider()
	{
		return $this->getDataProvider(
			['' => false, 'step mode' => true],
			['no exceptions' => false, 'throw exceptions' => 'throw', 'collect exceptions' => 'collect'],
		);
	}
}
