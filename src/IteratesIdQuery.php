<?php

namespace Glhd\ConveyorBelt;

use Closure;
use Illuminate\Support\Str;

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
		return Str::afterLast($this->idColumnName(), '.');
	}
}
