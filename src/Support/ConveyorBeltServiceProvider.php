<?php

namespace Glhd\ConveyorBelt\Support;

use Illuminate\Support\ServiceProvider;

class ConveyorBeltServiceProvider extends ServiceProvider
{
	protected string $base_dir;
	
	public function __construct($app)
	{
		parent::__construct($app);
		
		$this->base_dir = dirname(__DIR__, 2);
	}
	
	public function boot()
	{
		$this->publishes([
			"{$this->base_dir}/config.php" => $this->app->configPath('conveyor-belt.php'),
		], 'conveyor-belt-config');
	}
	
	public function register()
	{
		$this->mergeConfigFrom("{$this->base_dir}/config.php", 'conveyor-belt');
	}
}
