<?php
namespace App\Model; 

use Eloquent,Session,App,DB;

class Checklist extends Eloquent   
{
	public $timestamps = true;
	protected $table = 'checklists';
}
