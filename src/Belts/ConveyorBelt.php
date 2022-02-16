<?php

namespace Glhd\ConveyorBelt\Belts;

use Countable;
use Glhd\ConveyorBelt\Exceptions\AbortConveyorBeltException;
use Glhd\ConveyorBelt\Support\CollectedException;
use Glhd\ConveyorBelt\Support\ProgressBar;
use Illuminate\Console\Concerns\InteractsWithIO;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Enumerable;
use Illuminate\Support\Str;
use PHPUnit\Framework\Exception as PhpUnitException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\OutputStyle;
use Throwable;

abstract class ConveyorBelt
{
	use InteractsWithIO {
		table as defaultTable;
	}
	
	public ProgressBar $progress;
	
	/** @var \Glhd\ConveyorBelt\IteratesData|\Illuminate\Console\Command */
	protected $command;
	
	/** @var \Glhd\ConveyorBelt\Support\CollectedException[] */
	protected array $exceptions = [];
	
	abstract protected function collect(): Enumerable;
	
	public function __construct($command)
	{
		$this->command = $command;
		
		$this->addConveyorBeltOptions($command->getDefinition());
	}
	
	public function initialize(InputInterface $input, OutputStyle $output): void
	{
		$this->input = $input;
		$this->output = $output;
		
		$this->progress = new ProgressBar($input, $output);
	}
	
	public function handle(): int
	{
		$this->newLine();
		
		try {
			$this->prepare();
			$this->header();
			$this->start();
			$this->run();
			$this->finish();
			
			return Command::SUCCESS;
		} catch (AbortConveyorBeltException $exception) {
			if (! empty($message = $exception->getMessage())) {
				$this->error($message);
			}
			
			return $exception->getCode();
		} finally {
			$this->newLine();
		}
	}
	
	protected function prepare(): void
	{
		$this->verifyCommandSetup();
		$this->setVerbosityBasedOnOptions();
		
		if (method_exists($this->command, 'beforeFirstRow')) {
			$this->command->beforeFirstRow();
		}
	}
	
	protected function header(): void
	{
		$this->info(trans('conveyor-belt::messages.querying', ['records' => $this->command->getRowNamePlural()]));
	}
	
	protected function start(): void
	{
		$count = null;
		
		if ($this instanceof Countable && ! $count = $this->count()) {
			$this->command->info(trans('conveyor-belt::messages.no_matches', ['records' => $this->command->getRowNamePlural()]));
			return;
		}
		
		$this->progress->start($count, $this->command->getRowName(), $this->command->getRowNamePlural());
	}
	
	protected function run(): void
	{
		// Implementations may need to wrap the execution in another
		// process (like a transaction), so we'll add a layer here for extension
		
		$this->execute();
	}
	
	protected function execute(): void
	{
		$this->collect()->each(fn($item) => $this->handleRow($item));
	}
	
	protected function finish(): void
	{
		$this->progress->finish();
		
		if (method_exists($this->command, 'afterLastRow')) {
			$this->command->afterLastRow();
		}
		
		$this->showCollectedExceptions();
	}
	
	protected function abort(string $message = '', int $code = Command::FAILURE): void
	{
		throw new AbortConveyorBeltException($message, $code);
	}
	
	protected function handleRow($item): bool
	{
		$original = $this->getOriginalForDiff($item);
		
		try {
			$this->command->handleRow($item);
		} catch (PhpUnitException $exception) {
			throw $exception;
		} catch (Throwable $throwable) {
			$this->handleRowException($throwable, $item);
		}
		
		$this->progress->advance();
		
		$this->afterRow($item, $original);
		
		return true;
	}
	
	protected function afterRow($item, array $original): void
	{
		$this->logDiff($item, $original);
		$this->pauseIfStepping();
	}
	
	protected function handleRowException(Throwable $exception, $item): void
	{
		if ($this->shouldThrowRowException()) {
			$this->progress->finish();
			throw $exception;
		}
		
		$this->printError($exception);
		$this->pauseOnErrorIfRequested();
		
		if ($this->command->shouldCollectExceptions()) {
			$this->exceptions[] = new CollectedException($exception, $item);
		}
	}
	
	protected function shouldThrowRowException(): bool
	{
		return ! $this->command->shouldCollectExceptions()
			&& ! $this->option('pause-on-error');
	}
	
	protected function printError(Throwable $exception): void
	{
		if ($this->output->isVerbose()) {
			$this->progress->interrupt(fn() => $this->error($exception));
			return;
		}
		
		if ($this->option('pause-on-error')) {
			$this->progress->interrupt(fn() => $this->error(get_class($exception).': '.$exception->getMessage()));
		}
	}
	
	protected function pauseOnErrorIfRequested(): void
	{
		if (! $this->option('pause-on-error')) {
			return;
		}
		
		$this->progress->pause();
		
		if (! $this->confirm(trans('conveyor-belt::messages.confirm_continue'))) {
			$this->progress->finish();
			$this->abort(trans('conveyor-belt::messages.operation_cancelled'));
		}
		
		$this->progress->resume();
	}
	
	protected function getOriginalForDiff($item): array
	{
		if (! $item instanceof Model || ! $this->option('diff')) {
			return [];
		}
		
		return $item->getOriginal();
	}
	
	protected function logDiff($item, array $original): void
	{
		if (! $this->option('diff')) {
			return;
		}
		
		if (! $item instanceof Model) {
			$this->abort('The --diff flag requires Eloquent models');
		}
		
		if (empty($changes = $item->getChanges())) {
			return;
		}
		
		$table = collect($changes)->map(fn($value, $key) => ["<info>{$key}</info>", $original[$key] ?? null, $value]);
		
		$this->progress->pause();
		
		$this->newLine();
		
		$this->line(trans('conveyor-belt::messages.changes_to_record', ['record' => $this->command->getRowName()]));
		$this->table([trans('conveyor-belt::messages.before_heading'), trans('conveyor-belt::messages.after_heading')], $table);
		
		$this->progress->resume();
	}
	
	protected function pauseIfStepping(): void
	{
		if ($this->option('step') && ! $this->confirm(trans('conveyor-belt::messages.confirm_continue'), true)) {
			$this->abort(trans('conveyor-belt::messages.operation_cancelled'));
		}
	}
	
	protected function verifyCommandSetup(): void
	{
		if (! method_exists($this->command, 'handleRow')) {
			$this->abort('You must implement '.class_basename($this->command).'::handleRow()', Command::INVALID);
		}
	}
	
	protected function setVerbosityBasedOnOptions(): void
	{
		if ($this->option('step')) {
			$this->output->setVerbosity(OutputInterface::VERBOSITY_VERBOSE);
		}
	}
	
	protected function showCollectedExceptions(): void
	{
		if (! $count = count($this->exceptions)) {
			return;
		}
		
		$this->newLine();
		
		$this->error(trans_choice('conveyor-belt::messages.exceptions_triggered', $count));
		
		$headers = [
			Str::title($this->command->getRowName()),
			trans('conveyor-belt::messages.exception_heading'),
			trans('conveyor-belt::messages.message_heading'),
		];
		
		$rows = collect($this->exceptions)
			->map(fn(CollectedException $exception) => [$exception->key, get_class($exception->exception), (string) $exception]);
		
		$this->table($headers, $rows);
		
		$this->abort();
	}
	
	public function table($headers, $rows, $tableStyle = 'box', array $columnStyles = [])
	{
		$this->defaultTable($headers, $rows, $tableStyle, $columnStyles);
	}
	
	protected function addConveyorBeltOptions(InputDefinition $definition): void
	{
		$definition->addOption(new InputOption('step', null, null, "Step through each {$this->command->getRowName()} one-by-one"));
		$definition->addOption(new InputOption('diff', null, null, 'See a diff of any changes made to your models'));
		$definition->addOption(new InputOption('show-memory-usage', null, null, 'Include the command’s memory usage in the progress bar'));
		$definition->addOption(new InputOption('pause-on-error', null, null, 'Pause if an exception is thrown'));
	}
}
