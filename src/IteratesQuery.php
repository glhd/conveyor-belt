<?php

namespace Glhd\ConveyorBelt;

use Glhd\ConveyorBelt\Belts\ConveyorBelt;
use Glhd\ConveyorBelt\Belts\QueryBelt;
use Illuminate\Support\Enumerable;

/**
 * @property QueryBelt $conveyor_belt
 * @property int $chunk_size
 * @property bool $use_transaction
 * @method query()
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
		return $query->lazy($this->getChunkSize());
	}
	
	public function beforeFirstQuery(): void
	{
		// Implement this if you need to do some work before the initial
		// query is executed
	}
	
	public function getChunkSize(): int
	{
		return $this->useCommandPropertyIfExists(
			'chunk_size',
			config('conveyor-belt.chunk_count', 1000)
		);
	}
	
	public function shouldUseTransaction(): bool
	{
		return $this->useCommandPropertyIfExists('use_transaction', false);
	}
	
	protected function makeConveyorBelt(): ConveyorBelt
	{
		return new QueryBelt($this);
	}
}
