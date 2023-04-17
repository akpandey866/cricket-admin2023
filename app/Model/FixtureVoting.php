<?php
namespace App\Model; 

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Eloquent,DB,App;

class FixtureVoting extends Eloquent 
{
	protected $table = 'fixture_voting';
	protected $guarded = [];
}
