<?php

namespace Glhd\ConveyorBelt;

use Glhd\ConveyorBelt\Concerns\InteractsWithOutputDuringProgress;
use Glhd\ConveyorBelt\Concerns\SetsUpConveyorBelt;
use Illuminate\Support\Str;

/**
 * @method handleRow(\Illuminate\Database\Eloquent\Model|mixed $item)
 * @property bool $collect_exceptions
 */
trait IteratesData
{
	use InteractsWithOutputDuringProgress;
	use RespectsVerbosity;
	use SetsUpConveyorBelt;
	
	public function handle()
	{
		return $this->handleWithConveyorBelt();
	}
	
	public function beforeFirstRow(): void
	{
		// Do nothing by default
	}
	
	public function afterLastRow(): void
	{
		// Do nothing by default
	}
	
	public function rowName(): string
	{
		return trans('conveyor-belt::messages.record');
	}
	
	public function rowNamePlural(): string
	{
		return Str::plural($this->rowName());
	}
	
	public function shouldCollectExceptions(): bool
	{
		return $this->useCommandPropertyIfExists(
			'collect_exceptions', 
			fn() => config('conveyor-belt.collect_exceptions', false)
		);
	}
}
