<?php
namespace App\Model; 
use Eloquent;
class UserPremium extends Eloquent
{

	protected $table = 'user_premiums';
	protected $fillable = [];
	protected $guarded = [];
}
