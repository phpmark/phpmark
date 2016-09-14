<?php

final class PHPMark
{

	const F_VERSION = 1.01;
	const I_TIME_LIMIT_PER_OPERATIONS = 1;
	const S_TEST_STRING_BENCHMARK = "The wizard quickly jinxed the gnomes before they vaporized.";
	const S_TEST_GD = "Fickle jinx bog dwarves spy math quiz.";
	const S_TEST_IMAGE = "empty.gif";

	/** @var array */
	protected static $aFileUnit = array('b', 'kb', 'mb', 'gb', 'tb', 'pb');

	/** @var array */
	protected static $aMathFunctions;

	/** @var array */
	protected static $aStringFunctions;

	/** @var array */
	protected static $aDatas;

	/** @var bool */
	protected static $bLoaded;

	/** @var int */
	protected static $iMicrotime;

	/** @var int */
	protected static $iScore;

	/** @var  string */
	protected static $sServerName;

	/** @var  string */
	protected static $sServerProtocol;

	/** @var  string */
	protected static $sUniqName;


	/**
	 * ------------------------------------------------------------------------------------------------
	 * PUBLIC METHODS
	 * ------------------------------------------------------------------------------------------------
	 */


	/**
	 * run
	 */
	public static function run()
	{
		self::init();
		self::launchMethods();
		self::calculateScore();
		self::buildDatas();
		self::render();
	}


	/**
	 * ------------------------------------------------------------------------------------------------
	 * PROTECTED METHODS
	 * ------------------------------------------------------------------------------------------------
	 */


	/**
	 * init
	 */
	protected static function init()
	{
		if (self::$bLoaded) {
			return;
		}
		self::$aMathFunctions = array("abs", "acos", "asin", "atan", "bindec", "floor", "exp", "sin", "tan", "pi", "is_finite", "is_nan", "sqrt");
		foreach (self::$aMathFunctions as $key => $function) {
			if (!function_exists($function)) unset(self::$aMathFunctions[$key]);
		}
		self::$aStringFunctions = array("addslashes", "chunk_split", "metaphone", "strip_tags", "md5", "sha1", "strtoupper", "strtolower", "strrev", "strlen", "soundex", "ord");
		foreach (self::$aStringFunctions as $key => $function) {
			if (!function_exists($function)) unset(self::$aStringFunctions[$key]);
		}
		self::$sUniqName = uniqid();
		self::$sServerName = (isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : '?') . '@' . (isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '?');
		self::$sServerProtocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https://' : 'http://';
		self::$bLoaded = true;
	}

	/**
	 * launchMethods
	 */
	protected static function launchMethods()
	{
		$methods = get_class_methods(__CLASS__);
		foreach ($methods as $method) {
			if (substr($method, 0, 4) == 'test') {
				self::$iScore = 0;
				self::startTimer();
				while (true) {
					self::$method();
					self::$iScore++;
					if (self::hasEndedTimer()) {
						break;
					}
				}
				self::$aDatas['methods'][$method] = self::$iScore;
			}
		}
	}

	/**
	 * calculateScore
	 */
	protected static function calculateScore()
	{
		self::$aDatas['iScoreTotal'] = 0;
		foreach (self::$aDatas['methods'] as $method => $iScore) {
			if (strpos($method, 'PHP') !== false) {
				self::$aDatas['iScoreTotal'] += (int)($iScore / 100);
			} else {
				self::$aDatas['iScoreTotal'] += (int)$iScore;
			}
		}
		self::$aDatas['iScoreTotal'] = self::$aDatas['iScoreTotal'] / self::I_TIME_LIMIT_PER_OPERATIONS;
	}

	/**
	 * buildDatas
	 */
	protected static function buildDatas()
	{
		$intMemory = memory_get_usage(true);
		self::$aDatas['iMemoryUsage'] = round($intMemory / pow(1024, ($i = floor(log($intMemory, 1024)))), 2) . self::$aFileUnit[$i];
		self::$aDatas['sDate'] = date("Y-m-d H:i:s");
		self::$aDatas['sServerName'] = self::$sServerName;
		self::$aDatas['sPHPVersion'] = PHP_VERSION;
		self::$aDatas['sPlatform'] = PHP_OS;
	}

	/**
	 * render
	 */
	protected static function render()
	{
		$sRenderMode = (isset($_GET['output'])) ? $_GET['output'] : 'txt';
		switch ($sRenderMode) {
			case 'txt':
				self::renderTxt();
				break;
			case 'simple':
				self::renderSimpleTxt();
				break;
			case 'json':
				self::renderJson();
				break;
			case 'var_export':
				self::renderVarExport();
				break;
		}
	}

	/**
	 * renderTxt
	 */
	protected static function renderTxt()
	{
		$sLine = str_pad("-", 40, "-");
		$sTxt = '';
		$sTxt .= "<pre>$sLine\n|" . str_pad(__CLASS__ . " v" . self::F_VERSION, 38, " ", STR_PAD_BOTH);
		$sTxt .= "|\n$sLine\n";
		$sTxt .= "Start: " . self::$aDatas['sDate'] . "\n";
		$sTxt .= "Server: " . self::$aDatas['sServerName'] . "\n";
		$sTxt .= "PHP version: " . self::$aDatas['sPHPVersion'] . "\n";
		$sTxt .= "Platform: " . self::$aDatas['sPlatform'] . "\n";
		$sTxt .= "Memory usage: " . self::$aDatas['iMemoryUsage'] . "\n";
		$sTxt .= $sLine . "\n";
		foreach (self::$aDatas['methods'] as $method => $iScore) {
			$sTxt .= str_pad(str_replace(array('test_', '_'), array('', ' '), $method), 29) . ": " . $iScore . " \n";
		}
		$sTxt .= str_pad("-", 40, "-") . "\n" . str_pad(__CLASS__ . ":", 29) . " : " . (int)self::$aDatas['iScoreTotal'] . " </pre>";
		echo $sTxt;
	}

	/**
	 * renderSimpleTxt
	 */
	protected static function renderSimpleTxt(){
		$sTxt = '<pre>';
		$sTxt .= __CLASS__ . " v" . self::F_VERSION."\n";
		$sTxt .= self::$aDatas['sDate'] . "\n";
		$sTxt .= self::$aDatas['sServerName'] . "\n";
		$sTxt .= self::$aDatas['sPHPVersion'] . "\n";
		$sTxt .= self::$aDatas['sPlatform'] . "\n";
		$sTxt .= self::$aDatas['iMemoryUsage'] . "\n";
		foreach (self::$aDatas['methods'] as $method => $iScore) {
			$sTxt .= $iScore . " \n";
		}
		$sTxt .= (int)self::$aDatas['iScoreTotal'] . " </pre>";
		echo $sTxt;
	}

	/**
	 * renderJson
	 */
	protected static function renderJson()
	{
		echo json_encode(self::$aDatas);
	}

	/**
	 * renderVarExport
	 */
	protected static function renderVarExport()
	{
		var_export(self::$aDatas);
	}

	/**
	 * startTimer
	 */
	protected static function startTimer()
	{
		self::$iMicrotime = microtime(true);
	}

	/**
	 * hasEndedTimer
	 * @return bool
	 */
	protected static function hasEndedTimer()
	{
		return (microtime(true) - self::$iMicrotime) >= self::I_TIME_LIMIT_PER_OPERATIONS;
	}


	/**
	 * ------------------------------------------------------------------------------------------------
	 * PRIVATE METHODS
	 * ------------------------------------------------------------------------------------------------
	 */


	/**
	 * test_PHP_Math
	 * @return int
	 */
	private static function test_PHP_Math()
	{
		foreach (self::$aMathFunctions as $function) {
			call_user_func_array($function, array(self::$iScore));
			self::$iScore++;
		}
		self::$iScore--;
	}

	/**
	 * test_PHP_String_Manipulation
	 * @return int
	 */
	private static function test_PHP_String_Manipulation()
	{
		foreach (self::$aStringFunctions as $function) {
			call_user_func_array($function, array(SELF::S_TEST_STRING_BENCHMARK));
			self::$iScore++;
		}
		self::$iScore--;
	}

	/**
	 * test_PHP_If_Else
	 * @return int
	 */
	private static function test_PHP_If_Else()
	{
		if (self::$iScore == rand(0, 2)) {
		} elseif (self::$iScore == rand(0, 2)) {
		} else if (self::$iScore == rand(0, 2)) {
		}

	}

	/**
	 * test_GD_Image
	 * @return int
	 */
	private static function test_GD_Image()
	{
		ob_start();
		$im = imagecreatetruecolor(100, 100);
		imagefilledrectangle($im, 0, 0, 99, 99, 0xFFFFFF);
		imagestring($im, 3, 40, 20, self::S_TEST_GD, 0xFFBA00);
		imagejpeg($im);
		ob_end_clean();
		imagedestroy($im);
	}

	/**
	 * test_IO_File
	 * @return int
	 */
	private static function test_IO_File()
	{
		$fp = fopen(self::$sUniqName . '.txt', 'w');
		fwrite($fp, ' ');
		fclose($fp);
		unlink(self::$sUniqName . '.txt');
	}

	/**
	 * test_IO_Zip
	 * @return int
	 */
	private static function test_IO_Zip()
	{
		$zip = new ZipArchive();
		$zip->open(self::$sUniqName . '.zip', ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE);
		$zip->addFile(dirname(__FILE__) . '/' . self::S_TEST_IMAGE);
		$zip->close();
		unlink(self::$sUniqName . '.zip');
	}

	/**
	 * test_Apache_Curl
	 * @return int
	 */
	private static function test_Apache_Curl()
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, self::$sServerProtocol . $_SERVER['HTTP_HOST'] . str_replace('index.php','',$_SERVER['SCRIPT_NAME']) . self::S_TEST_IMAGE);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_exec($ch);
		curl_close($ch);
	}

}


PHPMark::run();