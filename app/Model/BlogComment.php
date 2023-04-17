<?php 
namespace App\Model; 
use Eloquent;


/**
 * BlogComment Model
 */
 
class BlogComment extends Eloquent   {
	
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
 
	protected $table = 'blog_comments';
}// end BlogComment class