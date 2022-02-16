<?php

namespace Glhd\ConveyorBelt\Belts;

use Box\Spout\Common\Entity\Row;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Box\Spout\Reader\CSV\Reader as CsvReader;
use Box\Spout\Reader\ODS\Reader as OdsReader;
use Box\Spout\Reader\ReaderInterface;
use Box\Spout\Reader\XLSX\Reader as XlsxReader;
use Illuminate\Support\Enumerable;
use Illuminate\Support\LazyCollection;

/**
 * @property \Glhd\ConveyorBelt\IteratesSpreadsheet|\Symfony\Component\Console\Command\Command $command
 */
class SpreadsheetBelt extends ConveyorBelt
{
	protected ?array $headings = null;
	
	protected function collect(): Enumerable
	{
		return new LazyCollection(function() {
			$reader = $this->reader();
			
			$reader->open($this->command->getSpreadsheetFilename());
			
			foreach ($reader->getSheetIterator() as $sheet) {
				$this->headings = null;
				foreach ($sheet->getRowIterator() as $row) {
					if ($result = $this->mapRow($row)) {
						yield $result;
					}
				}
			}
			
			$reader->close();
		});
	}
	
	protected function mapRow(Row $row)
	{
		if (null === $this->headings && $this->command->shouldUseHeadings()) {
			$this->setHeadings($row);
			return null;
		}
		
		return $this->command->mapCells($row->getCells(), $this->headings);
	}
	
	protected function setHeadings(Row $row): void
	{
		$this->headings = $this->command->mapHeadings($row->getCells());
	}
	
	protected function reader(): ReaderInterface
	{
		$reader = ReaderEntityFactory::createReaderFromFile($this->command->getSpreadsheetFilename());
		
		return $this->configureReader($reader);
	}
	
	protected function configureReader(ReaderInterface $reader): ReaderInterface
	{
		if ($reader instanceof CsvReader) {
			$reader->setShouldFormatDates($this->command->shouldFormatDates());
			$reader->setShouldPreserveEmptyRows($this->command->shouldPreserveEmptyRows());
			$reader->setFieldDelimiter($this->command->getFieldDelimiter());
			$reader->setFieldEnclosure($this->command->getFieldEnclosure());
			$reader->setEncoding($this->command->getSpreadsheetEncoding());
		}
		
		if ($reader instanceof XlsxReader) {
			$reader->setTempFolder($this->command->getExcelTempDirectory());
			$reader->setShouldFormatDates($this->command->shouldFormatDates());
			$reader->setShouldPreserveEmptyRows($this->command->shouldPreserveEmptyRows());
		}
		
		if ($reader instanceof OdsReader) {
			$reader->setShouldFormatDates($this->command->shouldFormatDates());
			$reader->setShouldPreserveEmptyRows($this->command->shouldPreserveEmptyRows());
		}
		
		return $reader;
	}
}
