<?php

namespace Glhd\ConveyorBelt\Tests\Concerns;

use Closure;
use Illuminate\Testing\PendingCommand;

trait RegistersTestCallbacks
{
	protected array $test_callback_order = [];
	
	/** @before */
	public function registerDefaultTestCallbacks()
	{
		$this->afterApplicationCreated(function() {
			$this->registerHandleRowCallback(function() {});
			$this->registerBeforeFirstRowCallback(function() {});
			$this->registerAfterLastRowCallback(function() {});
		});
	}
	
	public function registerHandleRowCallback(Closure $callback)
	{
		return $this->registerTestCallback('handleRow', $callback);
	}
	
	public function registerBeforeFirstRowCallback(Closure $callback)
	{
		return $this->registerTestCallback('beforeFirstRow', $callback);
	}
	
	public function registerAfterLastRowCallback(Closure $callback)
	{
		return $this->registerTestCallback('afterLastRow', $callback);
	}
	
	protected function assertHookMethodsWereCalledInExpectedOrder()
	{
		$this->assertEquals([
			'beforeFirstRow' => 0,
			'handleRow' => 1,
			'afterLastRow' => 2,
		], $this->test_callback_order);
	}
	
	protected function registerTestCallback(string $function, Closure $callback)
	{
		$this->app->instance("test_callbacks.{$function}", function(...$args) use ($function, $callback) {
			$this->test_callback_order[$function] ??= count($this->test_callback_order);
			$callback(...$args);
		});
		
		return $this;
	}
}
