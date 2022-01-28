<?php

namespace Glhd\ConveyorBelt\Tests;

use Glhd\ConveyorBelt\Tests\Commands\ShowUsersCommand;
use SqlFormatter;

class IteratesIdQueryTest extends TestCase
{
	public function test_default_behavior(): void
	{
		$this->artisan(ShowUsersCommand::class)
			->doesntExpectOutput('Chris Morrell')
			->doesntExpectOutput('Bogdan Kharchenko')
			->doesntExpectOutput('Taylor Otwell')
			->doesntExpectOutput('Mohamed Said')
			->assertSuccessful();
	}
	
	public function test_default_verbose_behavior(): void
	{
		$this->artisan(ShowUsersCommand::class, ['-v' => true])
			->expectsOutput('Chris Morrell')
			->expectsOutput('Bogdan Kharchenko')
			->expectsOutput('Taylor Otwell')
			->expectsOutput('Mohamed Said')
			->assertSuccessful();
	}
	
	public function test_step_behavior(): void
	{
		$this->artisan(ShowUsersCommand::class, ['--step' => true])
			->expectsOutput('Chris Morrell')
			->expectsConfirmation('Continue?', 'yes')
			->expectsOutput('Bogdan Kharchenko')
			->expectsConfirmation('Continue?', 'yes')
			->expectsOutput('Taylor Otwell')
			->expectsConfirmation('Continue?', 'yes')
			->expectsOutput('Mohamed Said')
			->expectsConfirmation('Continue?', 'yes')
			->assertSuccessful();
	}
	
	public function test_dump_sql_behavior(): void
	{
		$formatted = SqlFormatter::format('select * from "users" order by "id" asc');
		
		$this->artisan(ShowUsersCommand::class, ['--dump-sql' => true])
			->expectsOutput($formatted)
			->assertFailed();
	}
}
