<div style="float: right;">
	<a href="https://github.com/glhd/conveyor-belt/actions" target="_blank">
		<img 
			src="https://github.com/glhd/conveyor-belt/workflows/PHPUnit/badge.svg" 
			alt="Build Status" 
		/>
	</a>
	<a href="https://codeclimate.com/github/glhd/conveyor-belt/test_coverage" target="_blank">
		<img 
			src="https://api.codeclimate.com/v1/badges/8a95e7f39eac3bc4e6cb/test_coverage" 
			alt="Coverage Status" 
		/>
	</a>
	<a href="https://packagist.org/packages/glhd/conveyor-belt" target="_blank">
        <img 
            src="https://poser.pugx.org/glhd/conveyor-belt/v/stable" 
            alt="Latest Stable Release" 
        />
	</a>
	<a href="./LICENSE" target="_blank">
        <img 
            src="https://poser.pugx.org/glhd/conveyor-belt/license" 
            alt="MIT Licensed" 
        />
    </a>
    <a href="https://twitter.com/inxilpro" target="_blank">
        <img 
            src="https://img.shields.io/twitter/follow/inxilpro?style=social" 
            alt="Follow @inxilpro on Twitter" 
        />
    </a>
</div>

# Conveyor Belt

Conveyor Belt provides all the underlying mechanics necessary to write artisan commands that process lots of data efficiently.

## Quickly process 1000's of records
![Screencast of default behavior](img/default.svg)

## Get verbose output when necessary
![Screencast of verbose behavior](img/verbose.svg)

## Step through execution & log operations if needed
![Screencast of step behavior](img/step.svg)

## See what data is changed by your commands
![Screencast of diff behavior](img/diff.svg)

## And so much more
![Screencast of help view](img/more.svg)

## Installation

```shell
# composer require glhd/conveyor-belt
```

## Usage

To use Conveyor Belt, use one of two traits in your Laravel command:

- `\Glhd\ConveyorBelt\IteratesIdQuery` — use this if your underlying query can be ordered by `id` (improves performance)
- `\Glhd\ConveyorBelt\IteratesQuery` — use this if your query **is not ordered by `id`**

### Basic Example

```php
class ProcessUnverifiedUsersCommand extends Command
{
  use \Glhd\ConveyorBelt\IteratesIdQuery;
  
  // First, set up the query for the data that your command will operate on.
  // In this example, we're querying for all users that haven't verified their emails.
  public function query()
  {
    return User::query()
      ->whereNull('email_verified_at')
      ->orderBy('id');
  }
  
  // Then, set up a handler for each row. Our example command is either going to
  // remind users to verify their email (if they signed up recently), or queue
  // a job to prune them from the database.
  public function handleRow(User $user)
  {
    // The `progressMessage()` method updates the progress bar in normal mode,
    // or prints the message in verbose/step mode
    $this->progressMessage("{$user->name} <{$user->email}>");
    
    $days = $user->created_at->diffInDays(now());
    
    // The `progressSubMessage()` method adds additional context. If you're in
    // normal mode, this gets appended to the `progressMessage()`. In verbose or
    // step mode, this gets added as a list item below your `progressMessage()`
    $this->progressSubMessage('Registered '.$days.' '.Str::plural('day', $days).' ago…');
    
    // Sometimes our command trigger exceptions. Conveyor Belt makes it easy
    // to handle them and not have to lose all our progress
    ThirdParty::checkSomethingThatMayFail();
    
    if (1 === $days) {
      $this->progressSubmessage('Sending reminder');
      Mail::send(new EmailVerificationReminderMail($user));
    }
    
    if ($days >= 7) {
      $this->progressSubmessage('Queuing to be pruned');
      PruneUnverifiedUserJob::dispatch($user);
    }
  }
  
  // By setting collectExceptions() to true, we tell Conveyor Belt to catch
  // and log exceptions for display at the end of the operation
  public function collectExceptions(): bool
  {
    return true;
  }
}
```
