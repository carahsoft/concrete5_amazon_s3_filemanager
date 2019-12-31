<?php
namespace Concrete\Package\AmazonS3Filemanager\Src\File\StorageLocation\Configuration;


use \Concrete\Core\Error\Error as coreError,
	\Concrete\Core\File\StorageLocation\Configuration\Configuration as coreConfiguration,
	\Concrete\Core\File\StorageLocation\Configuration\ConfigurationInterface as coreConfigurationInterface,
	\Concrete\Core\Http\Request as coreRequest,
	\Concrete\Flysystem\Adapter\AwsS3 as coreAwsS3,
	Aws\S3\S3Client;



class S3Configuration extends coreConfiguration implements coreConfigurationInterface{


	protected $bucketname;
	protected $accesskey;
	protected $secretkey;
	protected $publicPath;
	protected $subfolder;


	protected $htaccessStartTag = '# -- s3 amazon filemanager rewrite start --';
	protected $htaccessEndTag = '# -- s3 amazon filemanager rewrite end --';


	public function setBucketname($str){
		$this->bucketname = $str;
	}

	public function getBucketname(){
		return $this->bucketname;
	}

	public function setAccesskey($str){
		$this->accesskey = $str;
	}

	public function getAccesskey(){
		return $this->accesskey;
	}

	public function setSecretkey($str){
		$this->secretkey = $str;
	}

	public function getSecretkey(){
		return $this->secretkey;
	}

	public function setPublicPath($str){
		$this->publicPath = $str;
	}

	public function getPublicPath(){
		return $this->publicPath;
	}

	public function setSubfolder($str){
		$this->subfolder = $str;
	}

	public function getSubfolder(){
		return $this->subfolder;
	}

	public function setEnablePublicPath($str){
		$this->enablePublicPath = $str;
	}

	public function getEnablePublicPath(){
		return $this->enablePublicPath;
	}

	public function setRegion($str){
		$this->region = $str;
	}

	public function getRegion(){
		return $this->region;
	}
	
	public function loadFromRequest(coreRequest $req){
		$data = $req->get('fslType');
		$this->bucketname = $data['bucketname'];
		$this->accesskey = $data['accesskey'];
		$this->secretkey = $data['secretkey'];
		$this->subfolder = $data['subfolder'];
		$this->region = $data['region'];
		$this->enablePublicPath = $data['enablePublicPath'];
		$this->publicPath = trim($data['publicPath'], "/");
	}
	
	public function validateRequest(coreRequest $req){
		$e = new coreError();
	
		$data = $req->get('fslType');
		$this->bucketname = $data['bucketname'];
		$this->accesskey = $data['accesskey'];
		$this->secretkey = $data['secretkey'];
		$this->subfolder = $data['subfolder'];
		$this->region = $data['region'];
		$this->enablePublicPath = $data['enablePublicPath'];
		$this->publicPath = trim($data['publicPath'], "/");

		if(!$this->bucketname) {
			$e->add(t("You must specify a S3 bucket name."));
		}else if(!$this->accesskey){
			$e->add(t("You must specify a S3 access key."));
		}else if(!$this->secretkey){
			$e->add(t("You must specify a S3 secret key."));
		}else if(!$this->testS3Connection()){
			$e->add(t("Connection failed. Please check your details."));
		}else if($this->enablePublicPath && !$this->publicPath){
			$e->add(t("You must specify a path to be displayed."));
		}

		if($this->enablePublicPath && $this->publicPath){
			$this->writeHtaccessEntry($this->getHtaccessEntry());
		}else{
			$this->writeHtaccessEntry('');
		}

		return $e;
	}

	private function testS3Connection(){
		try {
			$s3Client = S3Client::factory(array(
				'key' => $this->accesskey,
				'secret' => $this->secretkey
			));

			$bucketExist = $s3Client->doesBucketExist($this->bucketname);

			return $bucketExist;
		}catch(Exception $e){
			return false;
		}
	}

	private function writeHtaccessEntry($content = ''){
		$file = DIR_BASE.'/.htaccess';
		$current = file_get_contents($file);
		
		if(strpos($current,$this->htaccessStartTag) !== false)
			$current = $this->removeHtaccessEntry($current);

		$current .= $content;
		file_put_contents($file, $current);
	}

	private function removeHtaccessEntry($current){
		$beginPos = strpos($current, $this->htaccessStartTag);
		$endPos = strpos($current, $this->htaccessEndTag);

		if ($beginPos === false || $endPos === false)
			return $current;
		
		$textToDelete = substr($current, $beginPos , ($endPos + strlen($this->htaccessEndTag)) - $beginPos);
		return str_replace($textToDelete, '', $current);
	}
	
	private function getHtaccessEntry(){
		$strHt = '
		'.$this->htaccessStartTag.''
		. $this->getHtaccessRewriteRules() . '
		'.$this->htaccessEndTag.'
		';
		return preg_replace('/\t/', '', $strHt);
	}
	
	public function getHtaccessRewriteRules(){
		$strRules = '
		<IfModule mod_rewrite.c>
			RewriteEngine On
			RewriteBase /
			RewriteRule ^'.trim($this->publicPath,'/').'/(.*)$ '.$this->createExternalUrl().'$1 [L]
		</IfModule>';
		return $strRules;
	}

	public function getRelativePathToFile($file){
		if($this->enablePublicPath)
			return $this->publicPath.$file;
		return str_replace('//', '/', $this->createExternalUrl().$file);
	}

	public function hasPublicURL(){
		return true;
	}
	
	public function hasRelativePath(){
		if($this->enablePublicPath)
			return true;
		return false;
	}

	public function getPublicURLToFile($file){
	    $rel = $this->getRelativePathToFile($file);
        if(strpos($rel, '://')) {
            return $rel;
        }

        $url = \Core::getApplicationURL(true);
        $url = $url->setPath($rel);
        return trim((string) $url, '/');
	}


	public function getAdapter(){
		$client = S3Client::factory(array(
			'key' => $this->accesskey,
			'secret' => $this->secretkey
		));

		$AwsS3 = new coreAwsS3($client,$this->bucketname,($this->subfolder ? $this->subfolder : ''));
		return $AwsS3;
	}

	
	private function createExternalUrl(){
		return 'http://'.$this->bucketname.'.s3-website'.($this->region ? '-'.$this->region : '').'.amazonaws.com'.($this->subfolder ? $this->subfolder : '').'/';
	}
}