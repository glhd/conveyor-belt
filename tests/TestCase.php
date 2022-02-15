<?php

namespace Glhd\ConveyorBelt\Tests;

use Glhd\ConveyorBelt\Support\ConveyorBeltServiceProvider;
use Glhd\ConveyorBelt\Tests\Commands\ShowUsersCommand;
use Glhd\ConveyorBelt\Tests\Commands\TestJsonFileCommand;
use Glhd\ConveyorBelt\Tests\Commands\TestQueryCommand;
use Glhd\ConveyorBelt\Tests\Commands\TestSpreadsheetCommand;
use Glhd\LaravelDumper\LaravelDumperServiceProvider;
use Illuminate\Console\Application;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
	/** @before */
	public function registerTestCommands(): void
	{
		Application::starting(function(Application $app) {
			$app->resolve(TestSpreadsheetCommand::class);
			$app->resolve(TestJsonFileCommand::class);
			$app->resolve(TestQueryCommand::class);
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
