<?php

namespace Glhd\ConveyorBelt;

use Glhd\ConveyorBelt\Belts\ConveyorBelt;
use Glhd\ConveyorBelt\Belts\SpreadsheetBelt;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Str;
use OpenSpout\Common\Entity\Cell\DateTimeCell;
use OpenSpout\Common\Helper\EncodingHelper;

/**
 * @property SpreadsheetBelt $conveyor_belt
 * @property bool $use_headings
 * @property bool $preserve_empty_rows
 * @property bool $format_dates
 * @property string $filename
 * @property string $excel_temp_directory
 * @property string $field_delimiter
 * @property string $field_enclosure
 * @property string $spreadsheet_encoding
 * @property string $heading_format
 * @method handleRow(\stdClass $item)
 */
trait IteratesSpreadsheet
{
	use IteratesData;
	
	public function getSpreadsheetFilename(): string
	{
		if ($filename = $this->argument('filename')) {
			return $filename;
		}
		
		if ($filename = $this->useCommandPropertyIfExists('filename', null)) {
			return $filename;
		}
		
		$class_name = class_basename($this);
		$this->abort("Please implement {$class_name}::getSpreadsheetFilename() or add a 'filename' argument or property to your command.");
	}
	
	/**
	 * @param \OpenSpout\Common\Entity\Cell[] $cells
	 * @return \stdClass
	 */
	public function mapCells(array $cells, array $headings)
	{
		$format = $this->getHeadingFormat();
		$result = [];
		
		foreach ($cells as $index => $cell) {
			$value = match ($cell::class) {
				DateTimeCell::class => Date::instance($cell->getValue()),
				default => $cell->getValue(),
			};
			
			$key = $headings[$index] ?? Str::{$format}('column '.($index + 1));
			
			$result[$key] = $value;
		}
		
		return (object) $result;
	}
	
	/**
	 * @param \OpenSpout\Common\Entity\Cell[] $cells
	 * @return array
	 */
	public function mapHeadings(array $cells): array
	{
		$format = $this->getHeadingFormat();
		$headings = [];
		
		foreach ($cells as $index => $cell) {
			$value = $cell->getValue();
			
			if (! is_string($value)) {
				$value = 'column '.($index + 1);
			}
			
			$value = trim($value);
			$heading = Str::{$format}($value);
			
			if (in_array($heading, $headings)) {
				$heading = Str::{$format}("$value $index");
			}
			
			$headings[] = $heading;
		}
		
		return $headings;
	}
	
	public function shouldUseHeadings(): bool
	{
		return $this->useCommandPropertyIfExists('use_headings', true);
	}
	
	public function shouldPreserveEmptyRows(): bool
	{
		return $this->useCommandPropertyIfExists('preserve_empty_rows', false);
	}
	
	public function shouldFormatDates(): bool
	{
		return $this->useCommandPropertyIfExists('format_dates', false);
	}
	
	public function getFieldDelimiter(): string
	{
		return $this->useCommandPropertyIfExists('field_delimiter', ',');
	}
	
	public function getFieldEnclosure(): string
	{
		return $this->useCommandPropertyIfExists('field_enclosure', '"');
	}
	
	public function getSpreadsheetEncoding(): string
	{
		return $this->useCommandPropertyIfExists('spreadsheet_encoding', EncodingHelper::ENCODING_UTF8);
	}
	
	public function getExcelTempDirectory(): string
	{
		return $this->useCommandPropertyIfExists('excel_temp_directory', sys_get_temp_dir());
	}
	
	public function getHeadingFormat(): string
	{
		return $this->useCommandPropertyIfExists('heading_format', 'snake');
	}
	
	protected function makeConveyorBelt(): ConveyorBelt
	{
		return new SpreadsheetBelt($this);
	}
}
