<?php

namespace Glhd\ConveyorBelt\Belts;

use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Box\Spout\Reader\CSV\Reader;
use Box\Spout\Reader\ReaderInterface;
use Illuminate\Support\Enumerable;
use Illuminate\Support\LazyCollection;
use RuntimeException;

/**
 * @property \Glhd\ConveyorBelt\IteratesSpreadsheet|\Symfony\Component\Console\Command\Command $command
 */
class SpreadsheetBelt extends ConveyorBelt
{
	protected function collect(): Enumerable
	{
		return new LazyCollection(function() {
			$handle = fopen($filename = $this->command->csvFile(), 'rb');
			
			if (false === $handle) {
				throw new RuntimeException("Unable to read CSV file '{$filename}'");
			}
			
			$length = $this->command->csvReadLength();
			$separator = $this->command->csvSeparator();
			$enclosure = $this->command->csvEnclosure();
			$escape = $this->command->csvEscape();
			
			if ($this->command->csvHasHeadings()) {
				$headings = fgetcsv($handle, $length, $separator, $enclosure, $escape);
				$column_count = count($headings);
				
				while ($row = fgetcsv($handle, $length, $separator, $enclosure, $escape)) {
					yield array_combine($headings, array_pad($row, $column_count, null));
				}
			} else {
				while ($row = fgetcsv($handle, $length, $separator, $enclosure, $escape)) {
					yield $row;
				}
			}
			
			fclose($handle);
		});
	}
	
	protected function reader(): ReaderInterface
	{
		return ReaderEntityFactory::createReaderFromFile($this->command->csvFile());
	}
}
