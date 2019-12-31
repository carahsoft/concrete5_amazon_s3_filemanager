<?php
namespace Concrete\Package\AmazonS3Filemanager;

use Package,
	Concrete\Core\File\StorageLocation\Type\Type as StorageLocationType;

defined('C5_EXECUTE') or die(_("Access Denied."));

class Controller extends Package {

	protected $pkgHandle = 'amazon_s3_filemanager';
	protected $appVersionRequired = '5.7.0';
	protected $pkgVersion = '1.0.0';
	static protected $regions = array(
					"" 					=> "",
					"us-east-2"			=> "US East (Ohio)",
					"us-east-1"			=> "US East (N. Virginia)",
					"us-west-1"			=> "US West (N. California)",
					"us-west-2"			=> "US West (Oregon)",
					"ap-east-1"			=> "Asia Pacific (Hong Kong)",
					"ap-south-1"		=> "Asia Pacific (Mumbai)",
					"ap-northeast-3"	=> "Asia Pacific (Osaka-Local)",
					"ap-northeast-2"	=> "Asia Pacific (Seoul)",
					"ap-southeast-1"	=> "Asia Pacific (Singapore)",
					"ap-southeast-2"	=> "Asia Pacific (Sydney)",
					"ap-northeast-1"	=> "Asia Pacific (Tokyo)",
					"ca-central-1"		=> "Canada (Central)",
					"eu-central-1"		=> "Europe (Frankfurt)",
					"eu-west-1"			=> "Europe (Ireland)",
					"eu-west-2"			=> "Europe (London)",
					"eu-west-3"			=> "Europe (Paris)",
					"eu-north-1"		=> "Europe (Stockholm)",
					"me-south-1"		=> "Middle East (Bahrain)",
					"sa-east-1"			=> "South America (São Paulo)",
				);

	public function getPackageName(){
		return t("Amazon S3 File Manager");
	}

	public static function getRegions(){
		return self::$regions;
	}

	public function getPackageDescription(){
		return t("Adds S3 or compatible storage location to file manager");
	}

	public function on_start(){
		require_once(__DIR__ . '/vendor/autoload.php');
	}

	public function install() {
		$pkg = parent::install();
		$this->install_AddNewStorageLocation($pkg);
	}

	private function install_AddNewStorageLocation($pkg){
		StorageLocationType::add('s3', 'Amazon S3', $pkg);
	}

	public function uninstall() {
		parent::uninstall();
	}
}