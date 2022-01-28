<?php

namespace Glhd\ConveyorBelt\Concerns;

use Glhd\ConveyorBelt\Support\ConveyorBelt;
use InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\OutputStyle;

trait SetsUpConveyorBelt
{
	protected ConveyorBelt $conveyor_belt;
	
	public function configure()
	{
		parent::configure();
		
		$this->conveyor_belt = new ConveyorBelt($this);
	}
	
	protected function handleWithConveyorBelt(): int
	{
		return $this->conveyor_belt->run();
	}
	
	protected function initialize(InputInterface $input, OutputInterface $output)
	{
		parent::initialize($input, $output);
		
		if (! $output instanceof OutputStyle) {
			throw new InvalidArgumentException('Conveyor Belt requires output of type "Symfony\Component\Console\Style\OutputStyle"');
		}
		
		$this->conveyor_belt->initialize($input, $output);
	}
}
