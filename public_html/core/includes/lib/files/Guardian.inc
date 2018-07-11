<?php

namespace core;

/**
 * Class Guardian
 *
 * Protection Observer, insures that connections are valid and available to host
 * @package core
 */
class Guardian  extends \core\coreObserver
{

	/**
	 * Information used for Self Creation
	 */
	public static $_info = [
		'name' => "Guardian",
		'listen' => 'eventDispatcher'
	];

	/**
	 * Guardian Constructor
	 */
	public function __construct(  ){
		parent::__construct();

	}

	public function start(){

	}

	/**
	 * Detects IP address Ensures IP comes from an Active session and HTTP Referer to Ensure that all traffic is coming for valid Domain
	 */
	public function Verify(){
		$valid = false;

		//TODO Detect sessioning
		$ip = (getenv(HTTP_X_FORWARDED_FOR)) ? getenv(HTTP_X_FORWARDED_FOR) : getenv(REMOTE_ADDR);

		if(isset($HTTP_REFERER)) {
			if ((preg_match("/.*?" . $_SERVER['HTTP_HOST'] . ".*?/i", $HTTP_REFERER))) {
				$valid = TRUE;
			}
		}

		return $valid;
	}

	public function guard($file){

	}

	/** TODO
	 * Returns Javascript file from folder inside the .HTAccess restricted area
	 *
	 * @param $Public_Path
	 */
	private function protectJS($Public_Path){
		if(self::Verify()) {
			header("Content-Type: text/javascript");

			$myFile = "../js/$Public_Path.js";

			$fh = fopen($myFile, 'r');
			$theData = (fread($fh, filesize($myFile)));
			fclose($fh);

			echo $theData;
		}
	}

	/**
	 * Returns CSS file from folder inside the .HTAccess restricted area
	 * @param $Public_Path
	 */
	private function protectCSS($Public_Path){
		if(self::Verify()) {
			header("Content-type: text/css");

			$myFile = "../css/$Public_Path.css";

			$fh = fopen($myFile, 'r');
			$theData = compress_css(fread($fh, filesize($myFile)));
			fclose($fh);
			//echo '// ' . $HTTP_REFERER . "\n" ;
			echo $theData;
		}

	}

	/**
	 * Returns Binary file from folder inside the .HTAccess restricted area
	 *
	 * @param $Public_Path
	 */
	private function protectBin($Public_Path){

		if(self::Verify()) {
			$myFile = "../img/$Public_Path";

			$fi = new finfo(FILEINFO_MIME, '/usr/share/file/magic');
			$mime_type = $fi->buffer(file_get_contents($myFile));

			Header('Content-Type: ' . $mime_type);
			header('Content-length: ' . filesize($myFile));

			$fh = @ fopen($myFile, 'rb');
			if ($fh) {
				fpassthru($fh);
			}
			fclose($fh);
		}

	}

	/** TODO
	 *
	 * Takes observed inputs
	 * */
	/*public function update(\SplSubject $event) {
		//echo "Gardian";
		//var_dump( $inputs->getLastEvent());
	}*/
}