<?php

namespace Glhd\ConveyorBelt;

use Closure;
use Glhd\ConveyorBelt\Concerns\InteractsWithOutputDuringProgress;
use Glhd\ConveyorBelt\Concerns\SetsUpConveyorBelt;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

trait IteratesQuery
{
	use InteractsWithOutputDuringProgress;
	use SetsUpConveyorBelt;
	
	public function handle()
	{
		return $this->handleWithConveyorBelt();
	}
	
	public function iterateOverQuery($query, Closure $handler): void
	{
		$query->chunk($this->chunkCount(), $handler);
	}
	
	public function beforeFirstQuery(): void
	{
		// Do nothing by default
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
		return 'record';
	}
	
	public function rowNamePlural(): string
	{
		return Str::plural($this->rowName());
	}
	
	public function chunkCount(): int
	{
		return 1000;
	}
	
	public function prepareChunk(Collection $chunk): void
	{
	}
	
	public function useTransaction(): bool
	{
		return false;
	}
	
	public function collectExceptions(): bool
	{
		return false;
	}
}
