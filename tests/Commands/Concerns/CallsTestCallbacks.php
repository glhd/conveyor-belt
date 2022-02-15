<?php

namespace Glhd\ConveyorBelt\Tests\Commands\Concerns;

trait CallsTestCallbacks
{
	public function handleRow($item)
	{
		$this->callTestCallback($item);
	}
	
	public function beforeFirstRow(): void
	{
		$this->callTestCallback();
	}
	
	public function afterLastRow(): void
	{
		$this->callTestCallback();
	}
	
	protected function callTestCallback(...$args)
	{
		[$_, $caller] = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
		
		return $this->callNamedTestCallback($caller['function'], $args);
	}
	
	protected function callNamedTestCallback(string $function, array $args)
	{
		app("test_callbacks.{$function}")(...$args);
		
		return $this;
	}
}
