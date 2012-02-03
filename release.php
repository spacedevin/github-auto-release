<?php

error_reporting(E_ALL ^ E_NOTICE);


/* config */
$username = ''; // github username
$password = ''; // github password
$source = 'arzynik'; // username or organization name
$repo = 'jquery-ui'; // repository name
$key = 'yourkeyhere'; // key to send just for a tiny bit of security
$extractedFolder = 'ui.selectmenu'; // the name of the folder inside of the archive.

$base = 'https://raw.github.com/'.$source.'/'.$repo.'/selectmenu/';

$file['name'] = 'ui.selectmenu'; // name of the archive
$file['description'] = 'Auto Generated Release'; // a short description

// optional if you  have a version file
$version = trim(file_get_contents($base.'version.txt'));
$file['name'] .= '-'.$version;

// a list of files, and their paths as keys.
$files[$extractedFolder.'/jquery.ui.selectmenu.js'] = $base.'ui/jquery.ui.selectmenu.js';
$files[$extractedFolder.'/jquery.ui.theme.css'] = $base.'themes/base/jquery.ui.theme.css';
$files[$extractedFolder.'/readme.txt'] = $base.'readme';


// absolute path
$cache = sys_get_temp_dir().'/';





/* the stuff */

$file['path'] = $cache.$file['name'].'.zip';

$request = json_decode($_POST['payload']);

if ($key != $_REQUEST['key'] && $request->repository->url != 'https://github.com/'.$source.'/'.$repo) {
	//exit;
}



/* Github class that contains our curl shit */
class Github {
	
	public function __construct($params = array()) {
		$this->username = $params['username'];
		$this->password = $params['password'];
		$this->base = $params['base'];
	}
	
	public function request($params) {
		$params['headers'] = $params['headers'] ? true : false;

		$ch = curl_init();
		
		switch ($params['method']) {
			case 'delete':

				curl_setopt($ch, CURLOPT_URL,$params['url']);
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');		
			case 'post':
				switch ($params['format']) {
					case 'form':	
						// ghetto :p		
						$cmd = '/usr/bin/curl ';
						foreach ($params['data'] as $key => $item) {
							$cmd .= '-F "'.$key.'='.$item.'" ';
						}
						$cmd .= $params['url'];
						exec($cmd);
						return new Github_Response;
						
						break;

					case 'json':
					default:
						$datapost = json_encode($params['data']);
						curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
						break;
				}
				curl_setopt($ch, CURLOPT_POST, true); 
				curl_setopt($ch, CURLOPT_POSTFIELDS, $datapost);				
				curl_setopt($ch, CURLOPT_URL,$params['url']);

				break;
			default:
			case 'get':

				if (is_array($params['data'])) {
					foreach ($params['data'] as $key => $item) {
						$datapost .= ($datapost ? '&' : '?').$key.'='.@urlencode($item);
					}
				}
				curl_setopt($ch, CURLOPT_URL,$params['url'].$datapost);  
				curl_setopt($ch, CURLOPT_HTTPGET, true); 
		}
		

		curl_setopt($ch, CURLOPT_USERPWD, $this->username.':'.$this->password);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_AUTOREFERER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		
		if ($params['useragent']) {
			curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
		}
		
		if ($params['headers']) {
			curl_setopt($ch, CURLOPT_HEADER, true);
		}
		
		$output = curl_exec($ch);

		if ($params['headers']) {
			$sep = strpos($output, "\r\n\r\n") === false ? "\n\n" : "\r\n\r\n";
			list($headersRaw, $output) = explode($sep, $output, 2);
		}
		
		$response = new Github_Response(array(
			'url' => $params['url'],
			'request' => $datapost,
			'response' => $output,
			'headers' => $params['headers'] ? $this->_headers($headersRaw) : null,
			'error' => curl_error($ch)
		));

		curl_close ($ch);
		
		return $response;

	}
	
	private function _headers($headersRaw) {
		foreach (explode("\n",$headersRaw) as $header) {
			if (preg_match('/HTTP\//i',$header)) {
				$header = explode(' ',$header);
				$headers[$header[0]] = $header[1];
			} else {
				$header = explode(':',$header, 2);
				$headers[$header[0]] = $header[1];
			}
		}
		return $headers;
	}

}

/* response object */
class Github_Response {
	public $response;
	public $headers;
	public $error;
	
	public function __construct($params = array()) {
		$this->response = $params['response'];
		$this->request = $params['request'];
		$this->url = $params['url'];
		$this->headers = $params['headers'];
		$this->error = $params['error'];
	}
	
	public function response() {
		return json_decode($this->response);
	}
}

/* generic object builder */
class Github_Object {
	private $_github;
	public function __construct($params, $github = null) {
		$this->_github = $github;

		$this->load($params);
	}
	
	public function load($object) {
		if (is_object($object)) {
			foreach (get_object_vars($object) as $key => $value) {
				$this->$key = $value;
			}
		} elseif (is_array($object)) {
			foreach ($object as $key => $value) {
				$this->{'_'.$key} = $value;
			}
		}

	}

	public function github() {
		return $this->_github;
	}
	
	public function &__get($name) {
		if ($name{0} == '_') {
			return $this->{$name};
		} else {
			if (!$this->_properties['id']) {
				$this->info();
			}
			return $this->_properties[$name];
		}
	}
	
	public function __set($name, $value) {
		if ($name{0} == '_') {
			return $this->{$name} = $value;
		} else {
			return $this->_properties[$name] = $value;
		}
	}
	
	public function __isset($name) {
		return $name{0} == '_' ? isset($this->{$name}) : isset($this->_properties[$name]);
	}
}

/* repository object */
class Github_Repo extends Github_Object {
	public $_source;
	public $_repo;

	public function info() {
		if (!isset($this->id)) {
			$request = $this->github()->request(array(
				'url' => $this->github()->base.'/repos/'.$this->_source.'/'.$this->_repo,
				'method' => 'get'
			));

			$this->load($request->response());
			print_r($request->response);
		}
		return $this->_properties;
	}
	
	public function downloads() {
		if (!isset($this->_downloads)) {
			$request = $this->github()->request(array(
				'url' => $this->github()->base.'/repos/'.$this->_source.'/'.$this->_repo.'/downloads',
				'method' => 'get'
			));
			
			$this->_downloads = array();

			foreach ($request->response() as $download) {
				$download->_source = $this->_source;
				$download->_repo = $this->_repo;
				$this->_downloads[$download->id] = new Github_Download($download,$this->github());
			}

		}
		return $this->_downloads;
	}
}

/* download object */
class Github_Download extends Github_Object {
	public $_source;
	public $_repo;

	public function delete() {
		$request = $this->github()->request(array(
			'url' => $this->github()->base.'/repos/'.$this->_source.'/'.$this->_repo.'/downloads/'.$this->id,
			'method' => 'delete'
		));
		return $request->reponse;
	}
	
	public function upload($params = array()) {
		$request = $this->github()->request(array(
			'url' => 'https://github.s3.amazonaws.com/',
			'method' => 'post',
			'headers' => true,
			'format' => 'form',
			'data' => array(
				'key' => $this->path,
				'acl' => $this->acl,
				'success_action_status' => '201',
				'Filename' => $this->name,
				'AWSAccessKeyId' => $this->accesskeyid,
				'Policy' => $this->policy,
				'Signature' => $this->signature,
				'Content-Type' => $this->mime_type,
				'file' => '@'.$params['file']
			)
		));
	
	}
	
	public function create($params = array()) {
		$request = $this->github()->request(array(
			'url' => $this->github()->base.'/repos/'.$this->_source.'/'.$this->_repo.'/downloads',
			'method' => 'post',
			'headers' => true,
			'format' => 'json',
			'data' => array(
				'name' => $params['name'],
				'size' => filesize($params['file']),
				'description' => $params['description']
			)
		));

		$this->load($request->response());
		$this->upload($params);
		
	}
	
	public function info() {
		if (!isset($this->_properties)) {
			$request = $this->github()->request(array(
				'url' => $this->github()->base.'/repos/'.$this->_source.'/'.$this->_repo.'/downloads/'.$this->id,
				'method' => 'get'
			));
			$this->load($request->response());
		}
		return $this->_properties;
	}
}



class Zip {
	
	public function create($files = array(), $params = array()) {

		$this->_destination = $params['destination'];
		$this->_name = $params['name'];
		$this->_path = $this->_destination.$this->_name;
		
		if (!file_exists($this->_destination)) {
			throw new Exception('Destination directory "'.$this->_destination.'" does not exist.');
		}

		$valid_files = array();

		if (is_array($files)) {
			foreach($files as $filename => $file) {
				if (file_exists($file)) {
					$valid_files[$filename] = $file;
				}
			}
		}

		if (count($valid_files)) {

			$zip = new ZipArchive();
			
			if ($zip->open($this->_path, ZIPARCHIVE::OVERWRITE) !== true) {
				return array(
					'error'		=> 'failed to open',
					'file'		=> $this->_path
				);
			}

			foreach ($valid_files as $filename => $file) {
				$zip->addFile($file,$filename);
			}
			//debug
			//echo 'The zip archive contains ',$zip->numFiles,' files with a status of ',$zip->status;
			
			//close the zip -- done!
			$zip->close();
			chmod($this->_path,0777);
			
			//check to make sure the file exists
			return array(
				'error'		=> file_exists($this->_path) ? '' : 'failed to create',
				'file'		=> $this->_path
			);

		} else {
			return array(
				'error'		=> 'no valid files',
				'file'		=> $this->_path
			);
		}
	}

}





/* make the zip */

$zip = new Zip;
foreach ($files as $name => $zipfile) {
	$baseName = substr($name,0,strrpos($name,'/'));
	@mkdir($cache.$baseName);
	file_put_contents($cache.$name,file_get_contents($zipfile));
	$zipedFiles[$name] = $cache.$name;
}

$out = $zip->create($zipedFiles, array(
	'name' => $file['name'].'.zip',
	'destination' => $cache
));


/* delete old downloads */

$github = new Github(array(
	'base' => 'https://api.github.com',
	'username' => $username,
	'password' => $password
));

$repository = new Github_Repo(array(
	'source' => $source,
	'repo' => $repo
),$github);

foreach ($repository->downloads() as $download) {
	if ($download->name.'.zip' == $file['name'].'.zip') {
		$res = $download->delete();
	}
}

/* add the new download */

$download = new Github_Download(array(
	'source' => $source,
	'repo' => $repo
),$github);

$download->create(array(
	'name' => $file['name'].'.zip',
	'file' => $file['path'],
	'description' => $file['description']
));


/* delete everything */

foreach ($files as $name => $zipfile) {
	unlink($cache.$name);
}
unlink($file['path']);


echo 'success';