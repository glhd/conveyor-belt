<?php

namespace Glhd\ConveyorBelt;

use Glhd\ConveyorBelt\Concerns\InteractsWithOutputDuringProgress;
use Glhd\ConveyorBelt\Concerns\SetsUpConveyorBelt;
use Glhd\ConveyorBelt\Exceptions\AbortConveyorBeltException;
use Illuminate\Support\Str;
use Symfony\Component\Console\Command\Command;

/**
 * @method beforeFirstRow()
 * @method afterLastRow()
 * @method handleRow(\Illuminate\Database\Eloquent\Model|mixed $item)
 * @property bool $collect_exceptions
 * @property string $row_name
 * @property string $row_name_plural
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
	
	public function getRowName(): string
	{
		return $this->useCommandPropertyIfExists(
			'row_name',
			trans('conveyor-belt::messages.record')
		);
	}
	
	public function getRowNamePlural(): string
	{
		return $this->useCommandPropertyIfExists(
			'row_name_plural',
			Str::plural($this->getRowName())
		);
	}
	
	public function shouldCollectExceptions(): bool
	{
		return $this->useCommandPropertyIfExists(
			'collect_exceptions',
			config('conveyor-belt.collect_exceptions', false)
		);
	}
	
	protected function abort(string $message = '', int $code = Command::FAILURE): void
	{
		throw new AbortConveyorBeltException($message, $code);
	}
}
