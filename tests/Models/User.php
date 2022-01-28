<?php

namespace Glhd\ConveyorBelt\Tests\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
	use HasFactory;
	
	protected $guarded = [];
	
	protected static function newFactory()
	{
		return new class extends Factory
		{
			public function modelName()
			{
				return User::class;
			}
			
			public function definition()
			{
				return [
					'name' => $this->faker->name(),
					'email' => $this->faker->email(),
					'company_id' => Company::class,
				];
			}
		};
	}
	
	public function company()
	{
		return $this->belongsTo(Company::class);
	}
}
