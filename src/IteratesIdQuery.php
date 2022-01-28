<?php

namespace Glhd\ConveyorBelt;

use Closure;

trait IteratesIdQuery
{
	use IteratesQuery;
	
	public function iterateOverQuery($query, Closure $handler): void
	{
		$query->chunkById(
			$this->chunkCount(),
			$handler,
			$this->idColumnName(),
			$this->idAliasName()
		);
	}
	
	protected function idColumnName(): string
	{
		return 'id';
	}
	
	protected function idAliasName(): string
	{
		$column_segments = explode('.', $this->idColumnName());
		
		return array_pop($column_segments);
	}
}
