<?php

namespace Glhd\ConveyorBelt;

use Glhd\ConveyorBelt\Belts\ConveyorBelt;
use Glhd\ConveyorBelt\Belts\SpreadsheetBelt;
use Glhd\ConveyorBelt\Belts\JsonBelt;
use RuntimeException;

/**
 * @property SpreadsheetBelt $conveyor_belt
 */
trait IteratesSpreadsheet
{
	use IteratesData;
	
	abstract public function csvFile(): string;
	
	public function csvHasHeadings(): bool
	{
		return true;
	}
	
	public function csvReadLength(): ?int
	{
		return 1000;
	}
	
	public function csvSeparator(): string
	{
		return ',';
	}
	
	public function csvEnclosure(): string
	{
		return '"';
	}
	
	public function csvEscape(): string
	{
		return '\\';
	}
	
	protected function makeConveyorBelt(): ConveyorBelt
	{
		return new SpreadsheetBelt($this);
	}
}
