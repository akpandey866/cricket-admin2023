<?php 
namespace App\Model; 
use App,DB;
use App\Model\BlogComment;
use Eloquent;


/**
 * Blog Model
 */
 
class Blog extends Eloquent   {
	
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
 
	protected $table = 'blogs';

	
	/**
	* scope function
	*
	* @param $query 	as query object
	* 
	* @return query
	*/	
	public static function scopeOfPage($query)
    {
        return $query->where('is_active',1);
    } //end scopeOfPage()
	
	/**
	 * hasMany bind function for  AdminBlockDescription model 
	 *
	 * @param null
	 * 
	 * @return query
	 */	
	public function description() {
        return $this->hasMany('App\Model\BlogDescription','parent_id');
    }// end description()
	
	/**
	* belongsTo function for bind AdminDropDown model  
	*
	* @param null
	* 
	* @return query
	*/		
	public  function category(){
		return $this->belongsTo('App\Model\DropDown')->select('name','id');
	} //end category()
	
	
	/**
	* Function for get category blogs
	*
	* @param $limit as limit
	* 
	* @return query
	*/
	public function getCategoryBlogs($limit = 3){
		$lang		=	App::getLocale();
		$allBlogs 	= 	DB::select( DB::raw("SELECT user_id,name,description,created_at,updated_at,image,slug,id,type,embedded_url,(SELECT name FROM dropdown_manager_descriptions WHERE parent_id= blogs.blog_category_id AND language_id = (select id from languages WHERE languages.lang_code = '$lang')) as category_name, (SELECT full_name FROM users WHERE id=blogs.user_id) as user_name,(SELECT slug FROM users WHERE id=blogs.user_id) as user_slug FROM blogs WHERE is_active = 1 AND is_approved = 1 ORDER BY updated_at DESC limit $limit"));
		return $allBlogs;
	}//end getCategoryBlogs()
	
	/**
	* Function for get category blogs
	*
	* @param $limit as limit
	* 
	* @return query
	*/
	public function getFeaturedBlogs($limit = 1){
		$lang		=	App::getLocale();
		$allBlogs 	= 	DB::select( DB::raw("SELECT user_id,name,description,created_at,image,slug,id,type,embedded_url,(SELECT name FROM 		dropdown_manager_descriptions WHERE parent_id= blogs.blog_category_id AND language_id = (select id from languages WHERE languages.lang_code = '$lang')) as category_name, (SELECT full_name FROM users WHERE id=blogs.user_id) as user_name,(SELECT slug FROM users WHERE id=blogs.user_id) as user_slug FROM blogs WHERE is_active = 1 AND is_approved = 1 AND is_featured = 1 ORDER BY updated_at DESC limit $limit"));
		return $allBlogs;
	}//end getCategoryBlogs()
	
	/**
	* Function for get category blogs
	*
	* @param $limit as limit
	* 
	* @return query
	*/
	public function getRandomBlogs($limit = 5){
		$lang		=	App::getLocale();
		$allBlogs 	= 	DB::select( DB::raw("SELECT user_id,name,description,created_at,image,slug,id,type,embedded_url,(SELECT name FROM 		dropdown_manager_descriptions WHERE parent_id= blogs.blog_category_id AND language_id = (select id from languages WHERE languages.lang_code = '$lang')) as category_name, (SELECT full_name FROM users WHERE id=blogs.user_id) as user_name,(SELECT slug FROM users WHERE id=blogs.user_id) as user_slug FROM blogs WHERE is_active = 1 AND is_approved = 1 ORDER BY rand() DESC limit $limit"));
		return $allBlogs;
	}//end getCategoryBlogs()
	
	/**
	* Function for get total Comment of blog
	*
	* @param null
	* 
	* @return query
	*/
	public function getTotalBlog($id = 0){
		$totalBlogs	=	Blog::where('is_active', '=',1)->where('is_approved', '=',1)->count();
		return $totalBlogs;
	}//end getTotalBlog()
	
	/**
	* Function for get blog detail
	*
	* @param null
	* 
	* @return query
	*/
	public function getBlogDetail($slug = null){
		
		$lang			= App::getLocale();
		

		$allBlogs 		= DB::select( DB::raw("SELECT id,user_id,name,description,created_at,image,slug,id,type,embedded_url,total_views,(SELECT name FROM 	dropdown_manager_descriptions WHERE parent_id= blogs.blog_category_id AND language_id = (select id from languages WHERE languages.lang_code = '$lang')) as category_name, (SELECT full_name FROM users WHERE id=blogs.user_id) as user_name,(SELECT image FROM users WHERE id = blogs.user_id) as user_image,(SELECT slug FROM users WHERE id=blogs.user_id) as user_slug FROM blogs WHERE slug = '$slug'"));
		
		return $allBlogs;
	}//end getBlogDetail()
	/**
	* Function for get front blog details
	*
	* @param null
	* 
	* @return query
	*/
	public function BlogDetails($slug = null){
		$lang		=	App::getLocale();
		$allBlogs 	= 	DB::select( DB::raw("SELECT id,user_id,name,description,created_at,total_views,image,slug,id,type,embedded_url,(SELECT name FROM 	dropdown_manager_descriptions WHERE parent_id= blogs.blog_category_id AND language_id = (select id from languages WHERE languages.lang_code = '$lang')) as category_name, (SELECT full_name FROM users WHERE id=blogs.user_id) as user_name,(SELECT image FROM users WHERE id = blogs.user_id) as user_image,(SELECT slug FROM users WHERE id=blogs.user_id) as user_slug FROM blogs WHERE slug = '$slug'"));
		return $allBlogs;
	}//end getBlogDetail()
	/**
	* Function for get total Comment of blog
	*
	* @param null
	* 
	* @return query
	*/
	public function getTotalComment($id = 0){
		$totalComments	=	BlogComment::where('blog_comments.blog_id', '=', $id)->count();
		return $totalComments;
	}//end getTotalComment()
	
	
	public	 function totalBlogViews(){
	$blogDetail->id;
	$total_views = $blogDetail->total_views;
	$totalView = $totalView + 1;	
	}
	
} // end Blog class