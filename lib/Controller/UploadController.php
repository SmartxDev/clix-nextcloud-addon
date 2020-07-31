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

 class UploadController extends Controller {
	protected $appName;
     public function __construct(string $appName, IRequest $request){
         parent::__construct($appName, $request);
		 $this->appName = $appName;
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
		
		/* Get All Files from Dummy Folder */
		$handle = curl_init();

		 $input_xml = '<d:propfind  xmlns:d="DAV:" xmlns:oc="http://owncloud.org/ns" xmlns:nc="http://nextcloud.org/ns">
		  <d:prop>
			<d:status /> 
		  </d:prop>
		</d:propfind>';


    $url = "http://66.175.217.67/nextclou/remote.php/dav/files/".$username."/";
	
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
        return json_encode($output);
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