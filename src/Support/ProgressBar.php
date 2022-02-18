<?php

namespace Glhd\ConveyorBelt\Support;

use Closure;
use Illuminate\Console\Concerns\InteractsWithIO;
use Illuminate\Support\Str;
use Symfony\Component\Console\Helper\ProgressBar as SymfonyProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Style\OutputStyle;

class ProgressBar
{
	use InteractsWithIO;
	
	protected ?SymfonyProgressBar $bar = null;
	
	public function __construct(InputInterface $input, OutputStyle $output)
	{
		$this->input = $input;
		$this->output = $output;
	}
	
	public function enabled(): bool
	{
		return null !== $this->bar;
	}
	
	public function start(?int $count, string $row_singular = 'record', string $row_plural = 'records'): self
	{
		$this->newLine();
		
		$this->line(trans_choice('conveyor-belt::messages.processing_records', $count, [
			'count' => $count,
			'record' => $row_singular,
			'records' => $row_plural,
		]));
		
		$this->newLine();
		
		if (0 === $count || $this->output->isVerbose()) {
			return $this;
		}
		
		$this->bar = $this->output->createProgressBar();
		$this->bar->setFormat($this->getFormat(null !== $count));
		$this->bar->setMessage('');
		$this->bar->start($count);
		
		return $this;
	}
	
	public function advance(string $message = null): self
	{
		if ($this->bar) {
			$this->bar->advance();
		}
		
		if ($message) {
			$this->message($message);
		}
		
		return $this;
	}
	
	public function finish(): self
	{
		if ($this->bar) {
			$this->bar->display();
			$this->bar->finish();
			$this->bar = null;
		}
		
		$this->newLine();
		
		return $this;
	}
	
	public function message(string $message): self
	{
		if ($this->output->isVerbose()) {
			$this->newLine();
			$this->info($message);
			
			return $this;
		}
		
		$message = trim($message);
		$this->bar->setMessage($message);
		
		return $this;
	}
	
	public function subMessage(string $message): self
	{
		if ($this->output->isVerbose()) {
			$this->line(" - {$message}");
			return $this;
		}
		
		$message = trim($message);
		$this->bar->setMessage(Str::of($this->bar->getMessage())->before('→')->trim()->append(" → {$message}"));
		
		return $this;
	}
	
	public function pause(): self
	{
		if ($this->bar) {
			$this->bar->clear();
		}
		
		return $this;
	}
	
	public function resume(): self
	{
		if ($this->bar) {
			$this->bar->display();
		}
		
		return $this;
	}
	
	public function interrupt(Closure $callback)
	{
		$this->pause();
		
		$result = $callback();
		
		$this->resume();
		
		return $result;
	}
	
	protected function getFormat(bool $has_count): string
	{
		$format = $has_count
			? 'count'
			: 'base';
		
		if ($this->input->getOption('show-memory-usage')) {
			$format .= '_with_memory';
		}
		
		return config("conveyor-belt.progress_formats.{$format}", '%bar% %current% %message%');
	}
}
