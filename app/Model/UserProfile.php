<?php
namespace App\Model; 
use Eloquent;
class UserProfile extends Eloquent
{

	protected $table = 'user_profiles';
	protected $fillable = [];
	protected $guarded = [];
}
