<?php

namespace Glhd\ConveyorBelt\Tests\Commands;

use Glhd\ConveyorBelt\Tests\Models\User;
use Glhd\ConveyorBelt\IteratesIdQuery;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ShowUsersCommand extends Command
{
	use IteratesIdQuery;
	
	protected $signature = 'show-users {--fix-email}';
	
	public function query()
	{
		return User::query()->orderBy('id');
	}
	
	public function handleRow(User $user)
	{
		$this->progressMessage($user->name);
		
		if ($this->option('fix-email')) {
			$email = Str::of($user->name)->before(' ')->lower()->append('@foo.com');
			$user->update(['email' => $email]);
		}
	}
}
