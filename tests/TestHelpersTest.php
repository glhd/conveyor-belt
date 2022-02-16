<?php

namespace Glhd\ConveyorBelt\Tests;

class TestHelpersTest extends TestCase
{
	public function test_data_provider_helper_builds_all_possible_combinations(): void
	{
		$provided = $this->getDataProvider(
			['a' => 'eh', 'b' => 'bee'],
			['one' => 1, 'two' => 2],
			['yes' => true, 'no' => false],
		);
		
		$expected = [
			'a, one, yes' => ['eh', 1, true],
			'b, one, yes' => ['bee', 1, true],
			'a, two, yes' => ['eh', 2, true],
			'b, two, yes' => ['bee', 2, true],
			'a, one, no' => ['eh', 1, false],
			'b, one, no' => ['bee', 1, false],
			'a, two, no' => ['eh', 2, false],
			'b, two, no' => ['bee', 2, false],
		];
		
		$this->assertEquals($expected, $provided);
	}
}
