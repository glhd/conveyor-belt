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

To use Conveyor Belt, use one of the conveyor belt traits in your Laravel command:

### Databases

- `\Glhd\ConveyorBelt\IteratesIdQuery` — use this if your underlying query can be ordered by `id` (improves performance)
- `\Glhd\ConveyorBelt\IteratesQuery` — use this if your query **is not ordered by `id`**

### Files

- `\Glhd\ConveyorBelt\IteratesSpreadsheet` — use this to read CSV or Excel files
- `\Glhd\ConveyorBelt\IteratesJson` — use this to read JSON files or JSON API data

## Configuration

Most commands can be configured by setting public properties on the command itself. For example, if you want
to enable exception handling, you would add `public $collect_exceptions = true;` to your command. Each config
option can also be managed by overriding a function (if you need more dynamic control over its value). See the
source of each trait to find the appropriate function name.

### Common for all commands

- `$collect_exceptions` — set to `true` to have your command continue to run if an exception is triggered
  (the exception will be printed at the end of command execution)
- `$row_name` — set this to customize command output (e.g. if you're operating on `User` models you could
  set this to `"user"`)
- `$row_name_plural` — the plural of `$row_name` (usually not necessary, as we use `Str::plural` for you)

### `IteratesQuery`

- `$chunk_size` — the number of database records to load at one time 
- `$use_transaction` — whether to run the whole command inside a database transaction (can cause locking
  issues if your command runs for a long time)

### `IteratesIdQuery`

The `IteratesIdQuery` trait accepts all the options that `IteratesQuery` does, as well as:

- `$id_column` — the name of your ID column (if it is not `"id"`)
- `$id_alias` — the alias to your ID column in your query

### `IteratesSpreadsheet`

- `$use_headings` — whether to treat the first row of each sheet as headings
- `$preserve_empty_rows` — whether empty rows should be included
- `$format_dates` — whether date columns should be formatted (typically you don't need this because Conveyor Belt
  automatically converts date cells to `Carbon` instances for you)
- `$filename` — the file to load (only set if this is not dynamic in any way, which is unusual)
- `$excel_temp_directory` — set if you need to customize where temp files are stored
- `$field_delimiter` — change this if you need to import non-standard CSV files (e.g. tab-delimited)
- `$field_enclosure` — change this if you need to import non-standard CSV files (that don't use the `"` character)
- `$spreadsheet_encoding` — change this if you're dealing with non-UTF-8 data
- `$heading_format` — Change this to any `Str::` function to change the format of your array keys (`"snake"` by default)

### `IteratesJson`

- `$filename` — the file to load (only set if this is not dynamic in any way, which is unusual)
- `$json_endpoint` — the JSON endpoint to query for data (use `getJsonEndpoint` to set this dynamically)
- `$json_pointer` — use this to iterate over nested JSON data ([see spec](https://datatracker.ietf.org/doc/html/rfc6901))

## Examples

### Database Example

```php
class ProcessUnverifiedUsers extends Command
{
  use \Glhd\ConveyorBelt\IteratesIdQuery;
  
  // By setting $collect_exceptions to true, we tell Conveyor Belt to catch
  // and log exceptions for display, rather than aborting execution
  public $collect_exceptions = true;
  
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
}
```

### File Example

```php
class ProcessSignUpSheet extends Command
{
  use \Glhd\ConveyorBelt\IteratesSpreadsheet;
  
  // Conveyor Belt will automatically pick up a "filename" argument. If one
  // is missing you can set a $filename property or implement the getSpreadsheetFilename method
  protected $signature = 'process:sign-up-sheet {filename}';
  
  public function handleRow($item)
  {
    // $item is an object keyed by the spreadsheet headings in snake_case,
    // so for example, the following CSV:
    //
    // Full Name, Sign Up Date, Email
    // Chris Morrell, 2022-01-02, chris@mailinator.com
    //
    // Will result in a full_name, sign_up_date, and email property
    // on the $item object. You can change from snake case to any other
    // string helper format by setting $heading_format
  }
}
```

The `IteratesJson` trait works exactly the same as the `IteratesSpreadsheet` trait, just
with different configuration options.
