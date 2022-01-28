<?php

namespace Glhd\ConveyorBelt\Tests\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
	use HasFactory;
	
	protected $guarded = [];
	
	protected static function newFactory()
	{
		return new class extends Factory {
			public function modelName()
			{
				return Company::class;
			}
			
			public function definition()
			{
				return [
					'name' => $this->faker->company(),
				];
			}
		};
	}
	
	public function users()
	{
		return $this->hasMany(User::class);
	}
}
