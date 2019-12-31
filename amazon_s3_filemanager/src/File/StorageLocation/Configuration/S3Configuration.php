<?php
namespace Concrete\Package\AmazonS3Filemanager\Src\File\StorageLocation\Configuration;

use Concrete\Core\Error\Error as coreError,
	Concrete\Core\File\StorageLocation\Configuration\Configuration,
	Concrete\Core\File\StorageLocation\Configuration\ConfigurationInterface,
	Concrete\Core\Http\Request,
	League\Flysystem\AwsS3v3\AwsS3Adapter,
	Aws\S3\S3Client;

class S3Configuration extends Configuration implements ConfigurationInterface{

	protected $accessKey;
	protected $secretKey;
	protected $bucketName;
	protected $endpoint;
	protected $endpointPathStyle;
	protected $region;
	protected $subfolder;
	protected $enablePublic;
	protected $enableRewrite;
	protected $rewritePath;

	protected $htaccessStartTag = '# -- s3 amazon filemanager rewrite start --';
	protected $htaccessEndTag = '# -- s3 amazon filemanager rewrite end --';

	public function setAccessKey($str){
		$this->accessKey = $str;
	}
	public function getAccessKey(){
		return $this->accessKey;
	}

	public function setSecretKey($str){
		$this->secretKey = $str;
	}
	public function getSecretKey(){
		return $this->secretKey;
	}

	public function setBucketName($str){
		$this->bucketName = $str;
	}
	public function getBucketName(){
		return $this->bucketName;
	}

	public function setEndpoint($str){
		$this->endpoint = $str;
	}
	public function getEndpoint(){
		return $this->endpoint;
	}

	public function setEndpointPathStyle($str){
		$this->endpointPathStyle = $str;
	}
	public function getEndpointPathStyle(){
		return $this->endpointPathStyle;
	}

	public function setRegion($str){
		$this->region = $str;
	}
	public function getRegion(){
		return $this->region;
	}

	public function setSubfolder($str){
		$this->subfolder = $str;
	}
	public function getSubfolder(){
		return $this->subfolder;
	}

	public function setEnablePublic($str){
		$this->enablePublic = $str;
	}
	public function getEnablePublic(){
		return $this->enablePublic;
	}

	public function setEnableRewrite($str){
		$this->enableRewrite = $str;
	}
	public function getEnableRewrite(){
		return $this->enableRewrite;
	}

	public function setRewritePath($str){
		$this->rewritePath = $str;
	}
	public function getRewritePath(){
		return $this->rewritePath;
	}
	


	public function loadFromRequest(\Concrete\Core\Http\Request $req){
		$data = $req->get('fslType');

		$this->accessKey = $data['accessKey'];
		$this->secretKey = $data['secretKey'];
		$this->bucketName = $data['bucketName'];
		$this->endpoint = $data['endpoint'];
		$this->endpointPathStyle = $data['endpointPathStyle'];
		$this->region = $data['region'];
		$this->subfolder = $data['subfolder'];
		$this->enablePublic = $data['enablePublic'];
		$this->enableRewrite = $data['enableRewrite'];
		$this->rewritePath = trim($data['rewritePath'], "/");
	}
	
	public function validateRequest(\Concrete\Core\Http\Request $req){
		$e = new coreError();
		$this->loadFromRequest($req);
	
		if(!$this->accessKey){
			$e->add(t("You must specify an access key."));
		}else if(!$this->secretKey){
			$e->add(t("You must specify a secret key."));
		}else if(!$this->bucketName){
			$e->add(t("You must specify a bucket name."));
		}else if(!$this->testS3Connection()){
			$e->add(t("Connection failed! Please check your details."));
		}else if($this->enableRewrite && !$this->enablePublic){
			$e->add(t("Rewrite can only be used with S3 website mode."));
		}else if($this->enableRewrite && !$this->rewritePath){
			$e->add(t("When rewrite is enabled a path must be specified."));
		}

		if($this->enablePublic && $this->enableRewrite && $this->rewritePath){
			$this->writeHtaccessEntry($this->getHtaccessEntry());
		}else{
			$this->writeHtaccessEntry('');
		}

		return $e;
	}

	private function testS3Connection(){
		try {
			$s3Client = new S3Client([
				'version' => 'latest',
				'region' => $this->region,
				'endpoint' => $this->endpoint,
				'use_path_style_endpoint' => $this->endpointPathStyle !== '',
				'credentials' => [
					'key' => $this->accessKey,
					'secret' => $this->secretKey
				]
			]);

			$bucketExist = $s3Client->doesBucketExist($this->bucketName);

			return $bucketExist;
		} catch(Exception $e){
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
		'.$this->htaccessStartTag.'
		'.$this->getHtaccessRewriteRules().'
		'.$this->htaccessEndTag.'
		';
		return preg_replace('/\t/', '', $strHt);
	}
	
	public function getHtaccessRewriteRules(){
		$strRules = '
		<IfModule mod_rewrite.c>
			RewriteEngine On
			RewriteBase /
			RewriteRule ^'.trim($this->rewritePath,'/').'/(.*)$ '.$this->createExternalUrl().'$1 [L]
		</IfModule>';
		return $strRules;
	}

	public function getRelativePathToFile($file){
		if($this->enablePublicPath)
			return $this->publicPath.$file;
		return str_replace('//', '/', $this->createExternalUrl().$file);
	}

	public function hasPublicURL(){
		return $this->enablePublic;
	}
	
	public function hasRelativePath(){
		return $this->enablePublic;
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
		$client = new S3Client([
				'version' => 'latest',
				'region' => $this->region,
				'endpoint' => $this->endpoint,
				'use_path_style_endpoint' => $this->endpointPathStyle !== '',
				'credentials' => [
					'key' => $this->accessKey,
					'secret' => $this->secretKey
				]
		]);

		$AwsS3 = new AwsS3Adapter($client, $this->bucketName, ($this->subfolder ? $this->subfolder : ''));
		return $AwsS3;
	}

	private function createExternalUrl(){
		return 'http://'.$this->bucketName.'.s3-website'.($this->region ? '-'.$this->region : '').'.amazonaws.com'.($this->subfolder ? $this->subfolder : '').'/';
	}
}