<?php

namespace Glhd\ConveyorBelt\Tests;

use Glhd\ConveyorBelt\Tests\Commands\PeopleFromSpreadsheetCommand;

class IteratesSpreadsheetTest extends TestCase
{
	public function test_it_reads_a_csv_file(): void
	{
		$this->artisan(PeopleFromSpreadsheetCommand::class, ['--format' => 'csv'])
			->assertSuccessful();
		
		$expected = [
			'Chris Morrell from Galahad, Inc. says: "I hate final classes."',
			'Bogdan Kharchenko from Galahad, Inc. says: "It works."',
			'Mohamed Said from Laravel LLC says: ',
			'Taylor Otwell from Laravel LLC says: "No plans to merge."',
		];
		
		$this->assertEquals($expected, PeopleFromSpreadsheetCommand::$last_execution);
	}
	
	public function test_it_reads_an_excel_file(): void
	{
		$this->artisan(PeopleFromSpreadsheetCommand::class, ['--format' => 'xlsx'])
			->assertSuccessful();
		
		$expected = [
			'Chris Morrell from Galahad, Inc. says: "I hate final classes."',
			'Bogdan Kharchenko from Galahad, Inc. says: "It works."',
			'Mohamed Said from Laravel LLC says: ',
			'Taylor Otwell from Laravel LLC says: "No plans to merge."',
		];
		
		$this->assertEquals($expected, PeopleFromSpreadsheetCommand::$last_execution);
	}
}
