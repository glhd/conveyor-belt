<?php

namespace Glhd\ConveyorBelt;

use Illuminate\Support\Enumerable;
use Illuminate\Support\Str;

/**
 * @property string $id_column
 * @property string $id_alias
 */
trait IteratesIdQuery
{
	use IteratesQuery;
	
	/**
	 * @param \Illuminate\Database\Eloquent\Builder $query
	 * @return \Illuminate\Support\Enumerable
	 */
	public function queryToEnumerable($query): Enumerable
	{
		return $query->lazyById($this->getChunkSize(), $this->getIdColumn(), $this->getIdAlias());
	}
	
	protected function getIdColumn(): string
	{
		return $this->useCommandPropertyIfExists('id_column', 'id');
	}
	
	protected function getIdAlias(): string
	{
		return $this->useCommandPropertyIfExists('id_alias', Str::afterLast($this->getIdColumn(), '.'));
	}
}
