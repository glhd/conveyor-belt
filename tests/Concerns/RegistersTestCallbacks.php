<?php

namespace Glhd\ConveyorBelt\Tests\Concerns;

use Closure;

trait RegistersTestCallbacks
{
	protected array $test_callback_order = [];
	
	/** @before */
	public function registerDefaultTestCallbacks()
	{
		$this->afterApplicationCreated(function() {
			$this->registerHandleRowCallback(static fn() => null);
			$this->registerBeforeFirstRowCallback(static fn() => null);
			$this->registerAfterLastRowCallback(static fn() => null);
			$this->registerFilterRowCallback(static fn() => true);
			$this->registerRejectRowCallback(static fn() => false);
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
	
	public function registerFilterRowCallback(Closure $callback)
	{
		return $this->registerTestCallback('filterRow', $callback);
	}
	
	public function registerRejectRowCallback(Closure $callback)
	{
		return $this->registerTestCallback('rejectRow', $callback);
	}
	
	public function registerAfterLastRowCallback(Closure $callback)
	{
		return $this->registerTestCallback('afterLastRow', $callback);
	}
	
	protected function assertHookMethodsWereCalledInExpectedOrder()
	{
		$this->assertEquals([
			'beforeFirstRow' => 0,
			'filterRow' => 1,
			'rejectRow' => 2,
			'handleRow' => 3,
			'afterLastRow' => 4,
		], $this->test_callback_order);
	}
	
	protected function registerTestCallback(string $function, Closure $callback)
	{
		$this->app->instance("test_callbacks.{$function}", function(...$args) use ($function, $callback) {
			$this->test_callback_order[$function] ??= count($this->test_callback_order);
			return $callback(...$args);
		});
		
		return $this;
	}
}
