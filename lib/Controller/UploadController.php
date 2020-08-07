<?php
 namespace OCA\VueExample\Controller;
 
 use OCP\AppFramework\Http;
 use OCA\VueExample\Http\BlankResponse;
use OCP\AppFramework\Http\TemplateResponse;
 use OCP\IRequest;
 use OCP\AppFramework\Controller;
 use OCA\VueExample\Service\Flow\Config;
 use OCA\VueExample\Service\Flow\Request;
 use OCA\VueExample\Service\Flow\File;
 use OCP\IUserSession;
 use OCP\IConfig;
 use OCP\IURLGenerator;
 
 use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

 class UploadController extends Controller {
	protected $appName;
	private $db;
     public function __construct(string $appName, IURLGenerator $urlGenerator, IRequest $request, IDBConnection $db, IUserSession $userSession,IConfig $config){
		 $this->db = $db;
         parent::__construct($appName, $request);
		 $this->appName = $appName;
		 $this->config = $config;
		 $this->userSession     = $userSession;
		 $this->urlGenerator = $urlGenerator;
     }
     
    private function calculatePaths() {
        $this->userhome = \OC_User::getHome(\OC::$server->getUserSession()->getUser()->getUID());
        $this->temp = $this->userhome.'/.flowupload_tmp/';
        
        $uploadTarget = $_REQUEST['target'] ?? '/VueExample/';
        
        // Initialize uploader
        $this->config = new Config();
        $this->config->setTempDir($this->temp);
        $this->config->setDeleteChunksOnSave(TRUE);
        $this->request = new Request();
        
        $fileRelativePath = $this->request->getRelativePath();
        
        // Filter paths
        $fileRelativePath = preg_replace('/(\.\.\/|~|\/\/)/i', '', $fileRelativePath);
        $fileRelativePath = html_entity_decode(htmlentities($fileRelativePath, ENT_QUOTES, 'UTF-8'));
        $fileRelativePath = trim($fileRelativePath, '/');
        
        $this->path = $uploadTarget . $fileRelativePath;
     }
     
     /**
      * @NoAdminRequired
      * @NoCSRFRequired
      */
     public function checkChunk() {
		 
		$response = new TemplateResponse($this->appName, 'main');
		return $response;
		
		
        $this->calculatePaths();
        
        $file = new File($this->config, $this->request);
        
        // Skip existing files
        /*if (\OC\Files\Filesystem::file_exists($this->path)) {
            return new BlankResponse(204);
        }*/
         
        if ($file->checkChunk()) {
            return new BlankResponse(200);
        } else {
            // The 204 response MUST NOT include a message-body, and thus is always terminated by the first empty line after the header fields.
            return new BlankResponse(204);
        }
    }
     
     /**
      * @NoAdminRequired
      * @NoCSRFRequired
      */
     public function upload($request) {
		
		$file_name =  $_FILES["file"]['name'];
		$file_size =  $_FILES["file"]['size'];
		$username='admin';
		$password='Admin?999';		
		$fp = fopen($_FILES["file"]["tmp_name"], 'r');

		$c = curl_init();
		curl_setopt($c, CURLOPT_URL, "http://66.175.217.67/nextclou/remote.php/webdav/dummy/".$file_name);
		curl_setopt($c, CURLOPT_USERPWD, $username . ':' . $password); 
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($c, CURLOPT_PUT, true); 
		curl_setopt($c, CURLOPT_INFILESIZE_LARGE, $file_size);
		curl_setopt($c, CURLOPT_INFILE, $fp);
		$ret = curl_exec($c);
		
		if (curl_errno($c)) {
			// this would be your first hint that something went wrong
			$response['code']="500";
			$response['msg']= curl_error($c);
		} else {
			// check the HTTP status code of the request
			$resultStatus = curl_getinfo($c, CURLINFO_HTTP_CODE);
			if ($resultStatus == 200) {
				$response['code']="200";
					$response['msg']="Uploaded";
			} else {
				// the request did not complete as expected. common errors are 4xx
				// (not found, bad request, etc.) and 5xx (usually concerning
				// errors/exceptions in the remote script execution)

				if($resultStatus=='201'){
					$response['code']="200";
					$response['msg']="Uploaded";
				}else{
					$response['code']=$resultStatus;
					$response['msg']="error";
				}
			}
		}

		curl_close($ch);
		
		/* Get All Files from Dummy Folder */
		$handle = curl_init();

		 $input_xml = '<d:propfind  xmlns:d="DAV:" xmlns:oc="http://owncloud.org/ns" xmlns:nc="http://nextcloud.org/ns">
		  <d:prop>
			<d:status /> 
		  </d:prop>
		</d:propfind>';


    $url = "http://66.175.217.67/nextclou/remote.php/dav/files/".$username."/dummy/";
	
    // Set the url
    curl_setopt($handle, CURLOPT_URL, $url);
	curl_setopt( $handle, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
	curl_setopt( $handle, CURLOPT_POST, true );
	curl_setopt($handle, CURLOPT_USERPWD, $username . ':' . $password); 
    // Set the result output to be a string.
    curl_setopt($handle, CURLOPT_CUSTOMREQUEST, "PROPFIND" ); 
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
	
	curl_setopt($handle, CURLOPT_POSTFIELDS, $input_xml);
	//curl_setopt($handle, CURLOPT_RETURNTRANSFER, 1);
    //curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 300);
	
    $output = curl_exec($handle);
	
	$output	=	strip_tags($output);
    curl_close($handle);

    $output	=	explode("HTTP/1.1 404 Not Found",$output);
	 unset($output[0]);
	  array_pop($output);
		foreach($output as $key=> $value)
		{
		  if (strpos($value,'.pdf') !== false) 
		 {
		   // print key containing searched string
		  }else{
			  unset($output[$key]);
		  }
		};
        return json_encode(array_merge($response,$output));
    }
    /**
      * @NoAdminRequired
      * @NoCSRFRequired
      */
	public function getPdf() {
		$username='admin';
		$password='Admin?999';
		
		$urls		=	array();
		$data		=	array();
		$urls[1]	=	"http://66.175.217.67/nextclou/remote.php/dav/files/".$username."/";
		$urls[2]	=	"http://66.175.217.67/nextclou/remote.php/dav/files/".$username."/dummy/";
		$urls[3]	=	"http://66.175.217.67/nextclou/remote.php/dav/files/".$username."/Documents/";
		foreach($urls as $url){
			$response	=	$this->getCurlData($url);
			$data		=	array_merge($data,$response);
		}
		$new_data	=	array();
		$count=0;
		foreach ($data as $urls){
			$url_breaks	= explode('/',$urls);
			$new_data[$count]['file_name']= utf8_decode(urldecode(end($url_breaks)));
			$new_data[$count]['file_url']= $urls;	
			$new_data[$count]	=	(object) $new_data[$count];
			$count++;
		}
		$user = $this->userSession->getUser()->getUID();
		
		//echo '<pre>';
		//print_r($new_data);
		$qb = $this->db->getQueryBuilder();
        $qb->select('*')->from('activity');
		
		
        $cursor = $qb->execute();
        $row = $cursor->fetchAll();
        $cursor->closeCursor();
		
		$all_created_files	=	array();
		$all_deleted_files	=	array();
		$c=0;
		foreach($row as $value){
			if($value['user']==$user && $value['type']=='file_created'){
				if (strpos($value['file'], '.pdf') !== false) {
					$raw_object	=	json_decode($value['subjectparams']);
					$raw_array	=	(array) $raw_object[0];
					$keys	=	array_keys($raw_array);
					$all_created_files[$c]['id']= $keys[0];		
					$file_name= explode('/',$value['file']);
					$all_created_files[$c]['file_name']	=	end($file_name);
					//$uri_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
					//$uri_segments = explode('/', $uri_path);
					$uri= $uri_segments[1];
					$path= $this->urlGenerator->getBaseUrl();
					$all_created_files[$c]['file_url']	=	$path.'/remote.php/dav/files/'.$user.$value['file'];
				}
			}elseif($value['user']==$user && $value['type']=='file_deleted'){
				if (strpos($value['file'], '.pdf') !== false) {
					$raw_object	=	json_decode($value['subjectparams']);
					$raw_array	=	(array) $raw_object[0];
					$keys	=	array_keys($raw_array);
					$all_deleted_files[]= $keys[0];
				}
			}
		$c++;	
		}
		$all_pdf_files	= array();
		//remove deleted files
		foreach($all_created_files as $files){
			if(in_array($files['id'], $all_deleted_files)){
				
			}else{
				unset($files['id']);
				$all_pdf_files[]	=	(object) $files;
			}
		}
		//print_r($all_pdf_files);
		//print_r($new_data);
		//die;
		
		//print_r($this->config);
		
		return json_encode($all_pdf_files,JSON_PRETTY_PRINT);
    }
	
	
	private function getCurlData($url) {
       $username='admin';
		$password='Admin?999';		
		
		/* Get All Files from Dummy Folder */
		$handle = curl_init();

		 $input_xml = '<d:propfind  xmlns:d="DAV:" xmlns:oc="http://owncloud.org/ns" xmlns:nc="http://nextcloud.org/ns">
		  <d:prop>
			<d:status /> 
		  </d:prop>
		</d:propfind>';
	
    // Set the url
    curl_setopt($handle, CURLOPT_URL, $url);
	curl_setopt( $handle, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
	curl_setopt( $handle, CURLOPT_POST, true );
	curl_setopt($handle, CURLOPT_USERPWD, $username . ':' . $password); 
    // Set the result output to be a string.
    curl_setopt($handle, CURLOPT_CUSTOMREQUEST, "PROPFIND" ); 
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
	
	curl_setopt($handle, CURLOPT_POSTFIELDS, $input_xml);
	//curl_setopt($handle, CURLOPT_RETURNTRANSFER, 1);
    //curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 300);
	
    $output = curl_exec($handle);
	
	$output	=	strip_tags($output);
    curl_close($handle);

    $output	=	explode("HTTP/1.1 404 Not Found",$output);
	 unset($output[0]);
	  array_pop($output);
	  
	  foreach($output as $key=> $value)
		{
		  if (strpos($value,'.pdf') !== false) 
		 {
		   // print key containing searched string
		  }else{
			  unset($output[$key]);
		  }
		}
		
	$url2 = "http://66.175.217.67/nextclou/remote.php/dav/files/".$username."/dummy/";
	
        return $output;
    }
	
    private function updateFileCache($path) {
        \OC\Files\Filesystem::touch($path);

	    \OC_Hook::emit(
		    \OC\Files\Filesystem::CLASSNAME,
		    \OC\Files\Filesystem::signal_post_create,
		    array( \OC\Files\Filesystem::signal_param_path => $path)
	    );
    }
 }