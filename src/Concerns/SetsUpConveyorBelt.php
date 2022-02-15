<?php

namespace Glhd\ConveyorBelt\Concerns;

use Closure;
use Glhd\ConveyorBelt\Belts\ConveyorBelt;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\OutputStyle;

trait SetsUpConveyorBelt
{
	protected ConveyorBelt $conveyor_belt;
	
	abstract protected function makeConveyorBelt(): ConveyorBelt;
	
	public function configure()
	{
		parent::configure();
		
		$this->conveyor_belt = $this->makeConveyorBelt();
	}
	
	protected function handleWithConveyorBelt(): int
	{
		return $this->conveyor_belt->handle();
	}
	
	protected function initialize(InputInterface $input, OutputInterface $output)
	{
		if (! $output instanceof OutputStyle) {
			throw new InvalidArgumentException('Conveyor Belt requires output to be of type "Symfony\Component\Console\Style\OutputStyle"');
		}
		
		parent::initialize($input, $output);
		
		$this->conveyor_belt->initialize($input, $output);
	}
	
	protected function useCommandPropertyIfExists(string $snake_name, $default)
	{
		if (property_exists($this, $snake_name)) {
			return $this->{$snake_name};
		}
		
		if (property_exists($this, $camel_name = Str::camel($snake_name))) {
			return $this->{$camel_name};
		}
		
		return value($default);
	}
}
