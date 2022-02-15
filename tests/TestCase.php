<?php

namespace Glhd\ConveyorBelt\Tests;

use Glhd\ConveyorBelt\Support\ConveyorBeltServiceProvider;
use Glhd\ConveyorBelt\Tests\Commands\PeopleFromSpreadsheetCommand;
use Glhd\ConveyorBelt\Tests\Commands\ShowUsersCommand;
use Glhd\ConveyorBelt\Tests\Models\Company;
use Glhd\ConveyorBelt\Tests\Models\User;
use Glhd\LaravelDumper\LaravelDumperServiceProvider;
use Illuminate\Console\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
	use RefreshDatabase;
	
	/** @before */
	public function seedModels(): void
	{
		// We're intentionally inserting data out of order so that we can easily confirm our 'by ID' sorting logic
		$this->afterApplicationCreated(function() {
			Company::factory()->create(['id' => 2, 'name' => 'Laravel LLC']);
			Company::factory()->create(['id' => 1, 'name' => 'Galahad, Inc.']);
			
			User::factory()->create(['id' => 2, 'name' => 'Bogdan Kharchenko', 'company_id' => 1]);
			User::factory()->create(['id' => 1, 'name' => 'Chris Morrell', 'company_id' => 1]);
			User::factory()->create(['id' => 4, 'name' => 'Mohamed Said', 'company_id' => 2]);
			User::factory()->create(['id' => 3, 'name' => 'Taylor Otwell', 'company_id' => 2]);
		});
	}
	
	/** @before */
	public function registerTestCommands(): void
	{
		Application::starting(function(Application $app) {
			$app->resolve(ShowUsersCommand::class);
			$app->resolve(PeopleFromSpreadsheetCommand::class);
		});
	}
	
	protected function getPackageProviders($app)
	{
		return [
			ConveyorBeltServiceProvider::class,
			LaravelDumperServiceProvider::class,
		];
	}
	
	protected function getPackageAliases($app)
	{
		return [];
	}
	
	protected function getApplicationTimezone($app)
	{
		return 'America/New_York';
	}
	
	protected function defineDatabaseMigrations()
	{
		$this->loadMigrationsFrom(__DIR__.'/database/migrations');
	}
}
