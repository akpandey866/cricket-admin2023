<?php 
namespace App\Model; 
use Eloquent;

/**
 * BlogTag Model
 */
 
class BlogTag extends Eloquent  {
	
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
 
	protected $table = 'blog_tags';
	
	/**
	 * hasMany bind function for  AdminBlogTag model 
	 *
	 * @param null
	 * 
	 * @return query
	 */	
	public function blog_tag_name() {
        return $this->hasMany('App\Model\Tag','tag_id');
    }// end description()
	
}// end BlogTag class
