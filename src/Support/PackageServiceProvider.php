<?php

namespace Glhd\ConveyorBelt\Support;

use Illuminate\Support\ServiceProvider;

class PackageServiceProvider extends ServiceProvider
{
	protected string $base_dir;
	
	public function __construct($app)
	{
		parent::__construct($app);
		
		$this->base_dir = dirname(__DIR__, 2);
	}
	
	public function boot()
	{
		// require_once __DIR__.'/helpers.php';
		
		$this->bootConfig();
	}
	
	public function register()
	{
		$this->mergeConfigFrom("{$this->base_dir}/config.php", 'conveyor-belt');
	}
	
	protected function bootConfig() : self
	{
		if (method_exists($this->app, 'configPath')) {
			$this->publishes([
				"{$this->base_dir}/config.php" => $this->app->configPath('conveyor-belt.php'),
			], 'conveyor-belt-config');
		}
		
		return $this;
	}
}
