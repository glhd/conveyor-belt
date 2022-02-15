<?php

namespace Glhd\ConveyorBelt;

use Glhd\ConveyorBelt\Belts\ConveyorBelt;
use Glhd\ConveyorBelt\Belts\JsonBelt;
use JsonMachine\Items;

/**
 * @property JsonBelt $conveyor_belt
 * @property string|array $json_pointer
 * @property string $filename
 */
trait IteratesJson
{
	use IteratesData;
	
	public function getItems(array $options): Items
	{
		if ($filename = $this->getJsonFilename()) {
			return Items::fromFile($filename, $options);
		}
		
		$class_name = class_basename($this);
		$this->abort("Please implement {$class_name}::getItems() or add a 'filename' argument or property to your command.");
	}
	
	public function getJsonPointer()
	{
		return $this->useCommandPropertyIfExists('json_pointer', null);
	}
	
	protected function getJsonFilename(): ?string
	{
		if ($filename = $this->argument('filename')) {
			return $filename;
		}
		
		if ($filename = $this->useCommandPropertyIfExists('filename', null)) {
			return $filename;
		}
		
		return null;
	}
	
	protected function makeConveyorBelt(): ConveyorBelt
	{
		return new JsonBelt($this);
	}
}
