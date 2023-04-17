<?php
namespace App\Http\Controllers;
use Auth,Blade,Config,Cache,Cookie,DB,File,Hash,Input,Mail,mongoDate,Redirect,Request,Response,Session,URL,View,Validator,Str,App,Log;
use mjanssen\BreadcrumbsBundle\Breadcrumbs as Breadcrumb;
use App\Model\EmailLog;
use App\Model\Clickmap;
use App\Model\Cms;
use App\Model\Meta;
use App\Model\Category;
use App\Model\DisplayView;
use App\Http\Controllers\upload;
use App\Model\User;
/**
* Base Controller
*
* Add your methods in the class below
*
* This is the base controller called everytime on every request
*/
class BaseController extends Controller {
	private $authAdminUserVar;
	private $authFrontUserVar;
	public function __construct() {
		
		$this->authFrontUserVar = Auth::guard('web')->user();
	}// end function __construct()
/**
* Setup the layout used by the controller.
*
* @return layout
*/
	protected function setupLayout(){
		if(Request::segment(1) != 'admin'){
			
		}
		if ( ! is_null($this->layout)){
			$this->layout = View::make($this->layout);
		}
	}//end setupLayout()
	
	/** 
	* Function to make slug according model from any certain field
	*
	* @param title     as value of field
	* @param modelName as section model name
	* @param limit 	as limit of characters
	* 
	* @return string
	*/
		
	public function getSlug($title, $fieldName,$modelName,$limit = 100){
		$slug 			= 	 substr(Str::slug($title),0 ,$limit);
		$Model			=	 "\App\Model\\$modelName";
		$slugCount 		=    $Model::where($fieldName,$title)->count();
		if($slugCount == 0){
			$slug 		= 	 substr(Str::slug($title),0 ,$limit);
		}else{
			$slug 		= 	 $slug."-".$slugCount;
		}
		return $slug;
	}//end getSlug()
	
/** 
* Function to make slug without model name from any certain field
*
* @param title     as value of field
* @param tableName as table name
* @param limit 	as limit of characters
* 
* @return string
*/	
	public function getSlugWithoutModel($title, $fieldName='' ,$tableName,$limit = 30){ 	
		$slug 		=	substr(Str::slug($title),0 ,$limit);
		$slug 		=	Str::slug($title);
		$DB 		= 	DB::table($tableName);
		$slugCount 	= 	count( $DB->whereRaw("$fieldName REGEXP '^{$slug}(-[0-9]*)?$'")->get() );
		return ($slugCount > 0) ? $slug."-".$slugCount: $slug;
	}//end getSlugWithoutModel()
	/** 
	* Function to search result in database
	*
	* @param data  as form data array
	*
	* @return query string
	*/		
	public function search($data){
		unset($data['display']);
		unset($data['_token']);
		$ret	=	'';
		if(!empty($data )){
			foreach($data as $fieldName => $fieldValue){
				$ret	.=	"where('$fieldName', 'LIKE',  '%' . $fieldValue . '%')";
			}
			return $ret;
		}
	}//end search()
	/** 
	* Function to send email form website
	*
	* @param string $to            as to address
	* @param string $fullName      as full name of receiver
	* @param string $subject       as subject
	* @param string $messageBody   as message body
	*
	* @return void
	*/
	public function sendMail($to,$fullName,$subject,$messageBody, $from = '',$files = false,$path='',$attachmentName='') {
		$data				=	array();
		$data['to']			=	$to;
		$data['from']		=	(!empty($from) ? $from : Config::get("Site.email"));
		$data['fullName']	=	$fullName;
		$data['subject']	=	$subject;
		$data['filepath']	=	$path;
		$data['attachmentName']	=	$attachmentName;
		if($files===false){
			Mail::send('emails.template', array('messageBody'=> $messageBody), function($message) use ($data){
				$message->to($data['to'], $data['fullName'])->from($data['from'])->subject($data['subject']);

			});
		}else{
			if($attachmentName!=''){
				Mail::send('emails.template', array('messageBody'=> $messageBody), function($message) use ($data){
					$message->to($data['to'], $data['fullName'])->from($data['from'])->subject($data['subject'])->attach($data['filepath'],array('as'=>$data['attachmentName']));
				});
			}else{
				Mail::send('emails.template', array('messageBody'=> $messageBody), function($message) use ($data){
					$message->to($data['to'], $data['fullName'])->from($data['from'])->subject($data['subject'])->attach($data['filepath']);
				});
			}
		}
		DB::table('email_logs')->insert(
			array(
				'email_to'	 => $data['to'],
				'email_from' => $data['from'],
				'subject'	 => $data['subject'],
				'message'	 =>	$messageBody,
				'created_at' => DB::raw('NOW()')
			)
		); 
	}
	
	public  function arrayStripTags($array){
		$result			=	array();
		foreach ($array as $key => $value) {
			// Don't allow tags on key either, maybe useful for dynamic forms.
			$key = strip_tags($key,ALLOWED_TAGS_XSS);
	 
			// If the value is an array, we will just recurse back into the
			// function to keep stripping the tags out of the array,
			// otherwise we will set the stripped value.
			if (is_array($value)) {
				$result[$key] = $this->arrayStripTags($value);
			} else {
				// I am using strip_tags(), you may use htmlentities(),
				// also I am doing trim() here, you may remove it, if you wish.
				$result[$key] = trim(strip_tags($value,ALLOWED_TAGS_XSS));
			}
		}
		
		return $result;
		
	}

/** 
 * Function to _update_all_status
 *
 * param source tableName,id,status,fieldName
 */	
	public function _update_all_status($tableName = null,$id = 0,$status= 0,$fieldName = 'is_active'){
		DB::beginTransaction();
		$response			=	DB::statement("CALL UpdateAllTableStatus('$tableName',$id,$status)");
		if(!$response) {
			DB::rollback();
			Session::flash('error', trans("messages.global.somethingwrong")); 
			return Redirect::back();
		}
		DB::commit();
	}// end _update_all_status()

/** 
 * Function to _delete_table_entry
 *
 * param source tableName,id,fieldName
 */
	public function _delete_table_entry($tableName = null,$id = 0,$fieldName = null){
		DB::beginTransaction();
		$response			=	DB::statement("CALL DeleteAllTableDataById('$tableName',$id,'$fieldName')");
		if(!$response) {
			DB::rollback();
			Session::flash('error', trans("messages.msg.error.something_went_wrong")); 
			return Redirect::back();
		}
		DB::commit();
	}// end _delete_table_entry()
	
	public function saveCkeditorImages() {
		if(isset($_GET['CKEditorFuncNum'])){
			$image_url				=	"";
			$msg					=	"";
			// Will be returned empty if no problems
			$callback = ($_GET['CKEditorFuncNum']);        // Tells CKeditor which function you are executing
			$image_details 				= 	getimagesize($_FILES['upload']["tmp_name"]);
			$image_mime_type			=	(isset($image_details["mime"]) && !empty($image_details["mime"])) ? $image_details["mime"] : "";
			if($image_mime_type	==	'image/jpeg' || $image_mime_type == 'image/jpg' || $image_mime_type == 'image/gif' || $image_mime_type == 'image/png'){
				$ext					=	$this->getExtension($_FILES['upload']['name']);
				$fileName				=	"ck_editor_".time().".".$ext;
				$upload_path			=	CK_EDITOR_ROOT_PATH;
				if(move_uploaded_file($_FILES['upload']['tmp_name'],$upload_path.$fileName)){
					$image_url 			= 	CK_EDITOR_URL. $fileName;    
				}
			}else{
				$msg =  'error : Please select a valid image. valid extension are jpeg, jpg, gif, png';
			}
			$output = '<script type="text/javascript">window.parent.CKEDITOR.tools.callFunction('.$callback.', "'.$image_url .'","'.$msg.'");</script>';
			echo $output;
			exit;
		}
	}


/** 
 * Function to get extension
 *
 * param $str as string
 */	
	function getExtension($str) {
		$i = strrpos($str,".");
		if (!$i) { return ""; }
		$l = strlen($str) - $i;
		$ext = substr($str,$i+1,$l);
		$ext = strtolower($ext);
		return $ext;
	}//end getExtension()
	
/** 
 * Function to render
 *
 * param source request,Exception as $e
 */
	public function render($request, Exception $e) {
		if($this->isHttpException($e)) {
			switch ($e->getStatusCode()) {
				// not found
				case 404:
					return \Response::view('errors.404',array(),404);
				break;
				// internal error
				case '500':
					return \Response::view('errors.404',array(),404);	
				break;
				default:
					return $this->renderHttpException($e);
				break;
			}
		}else {
			return parent::render($request, $e);
		}
	}// end render()


	function getPageView($club_id = null,$page_id=null)
    {    
      $isPaid = DisplayView::where('club_id',$club_id)->where('page',$page_id)->value('view');
      return $isPaid;
  	}

}// end BaseController class