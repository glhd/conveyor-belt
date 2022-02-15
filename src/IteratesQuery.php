<?php

namespace Glhd\ConveyorBelt;

use Glhd\ConveyorBelt\Belts\ConveyorBelt;
use Glhd\ConveyorBelt\Belts\QueryBelt;
use Illuminate\Support\Enumerable;

/**
 * @property QueryBelt $conveyor_belt
 * @method \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Relations\Relation query()
 */
trait IteratesQuery
{
	use IteratesData;
	
	/**
	 * @param \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Relations\Relation $query
	 * @return \Illuminate\Support\Enumerable
	 */
	public function queryToEnumerable($query): Enumerable
	{
		return $query->lazy($this->chunkCount());
	}
	
	public function beforeFirstQuery(): void
	{
		// Implement this if you need to do some work before the initial
		// query is executed
	}
	
	public function chunkCount(): int
	{
		return config('conveyor-belt.chunk_count', 1000);
	}
	
	public function useTransaction(): bool
	{
		return false;
	}
	
	protected function makeConveyorBelt(): ConveyorBelt
	{
		return new QueryBelt($this);
	}
}
