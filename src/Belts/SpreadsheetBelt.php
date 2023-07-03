<?php

namespace Glhd\ConveyorBelt\Belts;

use Illuminate\Support\Enumerable;
use Illuminate\Support\LazyCollection;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Reader\CSV\Options as CsvOptions;
use OpenSpout\Reader\CSV\Reader as CsvReader;
use OpenSpout\Reader\ODS\Options as OdsOptions;
use OpenSpout\Reader\ODS\Reader as OdsReader;
use OpenSpout\Reader\ReaderInterface;
use OpenSpout\Reader\XLSX\Options as XlsxOptions;
use OpenSpout\Reader\XLSX\Reader as XlsxReader;

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
		$path = $this->command->getSpreadsheetFilename();
		
		$extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
		
		return match ($extension) {
			'csv' => $this->createCsvReader(),
			'xlsx' => $this->createXlsxReader(),
			'ods' => $this->createOdsReader(),
			default => $this->abort("Unable to determine spreadsheet type for '{$path}'"),
		};
	}
	
	protected function createCsvReader(): CsvReader
	{
		$options = new CsvOptions();
		$options->ENCODING = $this->command->getSpreadsheetEncoding();
		$options->FIELD_DELIMITER = $this->command->getFieldDelimiter();
		$options->FIELD_ENCLOSURE = $this->command->getFieldEnclosure();
		$options->SHOULD_PRESERVE_EMPTY_ROWS = $this->command->shouldPreserveEmptyRows();
		
		return new CsvReader($options);
	}
	
	protected function createXlsxReader(): XlsxReader
	{
		$options = new XlsxOptions();
		$options->SHOULD_PRESERVE_EMPTY_ROWS = $this->command->shouldPreserveEmptyRows();
		$options->SHOULD_FORMAT_DATES = $this->command->shouldFormatDates();
		$options->setTempFolder($this->command->getExcelTempDirectory());
		
		return new XlsxReader($options);
	}
	
	protected function createOdsReader(): OdsReader
	{
		$options = new OdsOptions();
		$options->SHOULD_FORMAT_DATES = $this->command->shouldFormatDates();
		$options->SHOULD_PRESERVE_EMPTY_ROWS = $this->command->shouldPreserveEmptyRows();
		
		return new OdsReader($options);
	}
}
