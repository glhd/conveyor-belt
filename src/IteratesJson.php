<?php

namespace Glhd\ConveyorBelt;

use Glhd\ConveyorBelt\Belts\ConveyorBelt;
use Glhd\ConveyorBelt\Belts\JsonBelt;
use GuzzleHttp\Psr7\StreamWrapper;
use Illuminate\Http\Client\Response as ClientResponse;
use Illuminate\Support\Facades\Http;
use JsonMachine\Items;

/**
 * @property JsonBelt $conveyor_belt
 * @property string|array $json_pointer
 * @property string $filename
 * @property string $json_endpoint
 */
trait IteratesJson
{
	use IteratesData;
	
	public function getItems(array $options): Items
	{
		if ($filename = $this->getJsonFilename()) {
			return Items::fromFile($filename, $options);
		}
		
		if ($endpoint = $this->getJsonEndpoint()) {
			$body = $this->prepareHttpRequest($endpoint)->toPsrResponse()->getBody();
			
			return Items::fromStream(StreamWrapper::getResource($body), $options);
		}
		
		$class_name = class_basename($this);
		$this->abort("Please implement {$class_name}::getItems(), add a 'json_endpoint' property to your command, or add a 'filename' argument or property to your command.");
	}
	
	public function getJsonPointer()
	{
		return $this->useCommandPropertyIfExists('json_pointer', null);
	}
	
	protected function getJsonFilename(): ?string
	{
		if ($this->hasArgument('filename') && $filename = $this->argument('filename')) {
			return $filename;
		}
		
		if ($filename = $this->useCommandPropertyIfExists('filename', null)) {
			return $filename;
		}
		
		return null;
	}
	
	protected function getJsonEndpoint(): ?string
	{
		return $this->useCommandPropertyIfExists('json_endpoint', null);
	}
	
	protected function prepareHttpRequest($endpoint): ClientResponse
	{
		return Http::get($endpoint);
	}
	
	protected function makeConveyorBelt(): ConveyorBelt
	{
		return new JsonBelt($this);
	}
}
