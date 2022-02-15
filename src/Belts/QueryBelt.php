<?php

namespace Glhd\ConveyorBelt\Belts;

use Countable;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as BaseBuilder;
use Illuminate\Support\Arr;
use Illuminate\Support\Enumerable;
use Illuminate\Support\Facades\DB;
use SqlFormatter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;

/**
 * @property \Glhd\ConveyorBelt\IteratesQuery|\Illuminate\Console\Command $command
 */
class QueryBelt extends ConveyorBelt implements Countable
{
	/** @var BaseBuilder|EloquentBuilder|Relation|null */
	protected $query = null;
	
	public function count(): int
	{
		return $this->query()->count();
	}
	
	protected function prepare(): void
	{
		// We need to prepare for query logging first, because if this flag is
		// turned on, it will implicitly turn on the --step option as well
		$this->prepareForQueryLogging();
		
		parent::prepare();
		
		// Once everything else is prepared, we'll check for the --dump-sql
		// flag and if it's set, print the query and exit
		$this->dumpSqlAndAbortIfRequested();
	}
	
	protected function header(): void
	{
		$message = $this->command->useTransaction()
			? trans('conveyor-belt::messages.querying_with_transaction', ['records' => $this->command->rowNamePlural()])
			: trans('conveyor-belt::messages.querying_without_transaction', ['records' => $this->command->rowNamePlural()]);
		
		$this->info($message);
	}
	
	protected function collect(): Enumerable
	{
		return $this->command->queryToEnumerable($this->query());
	}
	
	protected function run(): void
	{
		if ($this->command->useTransaction()) {
			DB::transaction(fn() => $this->execute());
		} else {
			$this->execute();
		}
	}
	
	protected function execute(): void
	{
		$this->command->beforeFirstQuery();
		
		parent::execute();
	}
	
	protected function afterRow($item, array $original): void
	{
		$this->logSql();
		
		parent::afterRow($item, $original);
	}
	
	protected function logSql(): void
	{
		if (! $this->option('log-sql')) {
			return;
		}
		
		$table = collect(DB::getQueryLog())
			->map(fn($log) => [$this->getFormattedQuery($log['query'], $log['bindings']), $log['time']]);
		
		if ($table->isEmpty()) {
			return;
		}
		
		$this->newLine();
		$this->line(trans_choice('conveyor-belt::messages.queries_executed', $table->count()));
		$this->table([trans('conveyor-belt::messages.query_heading'), trans('conveyor-belt::messages.time_heading')], $table);
		
		DB::flushQueryLog();
	}
	
	protected function prepareForQueryLogging(): void
	{
		if ($this->option('log-sql')) {
			$this->input->setOption('step', true);
			DB::enableQueryLog();
		}
	}
	
	protected function dumpSqlAndAbortIfRequested(): void
	{
		if (! $this->option('dump-sql')) {
			return;
		}
		
		$query = $this->query();
		$this->printFormattedQuery($query->toSql(), $query->getBindings());
		
		$this->abort();
	}
	
	protected function printFormattedQuery(string $sql, array $bindings): void
	{
		$this->newLine();
		
		$this->line($this->getFormattedQuery($sql, $bindings));
	}
	
	protected function getFormattedQuery(string $sql, array $bindings): string
	{
		$bindings = Arr::flatten($bindings);
		
		$sql = preg_replace_callback('/\?/', static function() use (&$bindings) {
			return DB::getPdo()->quote(array_shift($bindings));
		}, $sql);
		
		return SqlFormatter::format($sql);
	}
	
	protected function addConveyorBeltOptions(InputDefinition $definition): void
	{
		parent::addConveyorBeltOptions($definition);
		
		$definition->addOption(new InputOption('dump-sql', null, null, 'Dump the SQL of the query this command will execute'));
		$definition->addOption(new InputOption('log-sql', null, null, 'Log all SQL queries executed and print them'));
	}
	
	/**
	 * @return BaseBuilder|EloquentBuilder|Relation
	 */
	protected function query()
	{
		return $this->query ??= $this->fetchQueryFromCommand();
	}
	
	protected function fetchQueryFromCommand()
	{
		if (! method_exists($this->command, 'query')) {
			$this->abort('You must implement '.class_basename($this->command).'::query()', Command::INVALID);
		}
		
		$query = $this->command->query();
		
		$expected = [
			BaseBuilder::class,
			EloquentBuilder::class,
			Relation::class,
		];
		
		foreach ($expected as $name) {
			if ($query instanceof $name) {
				return $query;
			}
		}
		
		$this->abort(class_basename($this->command).'::query() must return a query builder', Command::INVALID);
	}
}
