<?php

namespace Glhd\ConveyorBelt;

use Illuminate\Support\Enumerable;
use Illuminate\Support\Str;

trait IteratesIdQuery
{
	use IteratesQuery;
	
	/**
	 * @param \Illuminate\Database\Eloquent\Builder $query
	 * @return \Illuminate\Support\Enumerable
	 */
	public function queryToEnumerable($query): Enumerable
	{
		return $query->lazyById($this->chunkCount(), $this->idColumnName(), $this->idAliasName());
	}
	
	protected function idColumnName(): string
	{
		return 'id';
	}
	
	protected function idAliasName(): string
	{
		return Str::afterLast($this->idColumnName(), '.');
	}
}
