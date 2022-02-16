<?php

namespace Glhd\ConveyorBelt\Tests;

use Glhd\ConveyorBelt\Support\ConveyorBeltServiceProvider;
use Glhd\ConveyorBelt\Tests\Commands\TestIdQueryCommand;
use Glhd\ConveyorBelt\Tests\Commands\TestJsonEndpointCommand;
use Glhd\ConveyorBelt\Tests\Commands\TestJsonFileCommand;
use Glhd\ConveyorBelt\Tests\Commands\TestQueryCommand;
use Glhd\ConveyorBelt\Tests\Commands\TestSpreadsheetCommand;
use Glhd\ConveyorBelt\Tests\Concerns\ArtificiallyFails;
use Glhd\ConveyorBelt\Tests\Concerns\ProvidesData;
use Glhd\ConveyorBelt\Tests\Concerns\RegistersTestCallbacks;
use Glhd\LaravelDumper\LaravelDumperServiceProvider;
use Illuminate\Console\Application;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
	use RegistersTestCallbacks;
	use ProvidesData;
	use ArtificiallyFails;
	
	/** @before */
	public function registerTestCommands(): void
	{
		Application::starting(function(Application $app) {
			$app->resolve(TestSpreadsheetCommand::class);
			$app->resolve(TestJsonFileCommand::class);
			$app->resolve(TestJsonEndpointCommand::class);
			$app->resolve(TestQueryCommand::class);
			$app->resolve(TestIdQueryCommand::class);
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
}
