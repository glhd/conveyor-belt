<?php

namespace Glhd\ConveyorBelt\Support;

use Illuminate\Support\ServiceProvider;

class ConveyorBeltServiceProvider extends ServiceProvider
{
	public function boot()
	{
		$this->loadTranslationsFrom($this->packageTranslationsDirectory(), 'conveyor-belt');
		
		$this->publishes(
			[$this->packageConfigFile() => $this->app->configPath('conveyor-belt.php')],
			['conveyor-belt', 'conveyor-belt-config']
		);
		
		$this->publishes(
			[$this->packageTranslationsDirectory() => $this->app->resourcePath('lang/vendor/conveyor-belt')],
			['conveyor-belt', 'conveyor-belt-translations']
		);
	}
	
	public function register()
	{
		$this->mergeConfigFrom($this->packageConfigFile(), 'conveyor-belt');
	}
	
	protected function packageConfigFile(): string
	{
		return dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'config.php';
	}
	
	protected function packageTranslationsDirectory(): string
	{
		return dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'translations';
	}
}
