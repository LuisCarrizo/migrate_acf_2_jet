<?php
$rv = array();
try {
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		if (empty($_POST)) {
			$rv = json_decode(file_get_contents('php://input'), true);
		} else {
			$rv = $_POST;
		}
	}

	if ( empty($rv ) ){
		throw new Exception('Empty Params', 1);
	}

	// compose uploaded filename
	$tempFolder = __DIR__ . '/temp/';
	$jetName = $_POST['jetFileName'];
	$jetFilename = $tempFolder . $jetName;

	// ** read uploaded file
	$jetData = file_get_contents($jetFilename);

	// ** download new file
	header('Content-Description: File Transfer');
	header("Content-type: text/plain");
	header('Content-Disposition: attachment; filename="' . $jetName . '"');
	header('Expires: 0');
	header('Cache-Control: must-revalidate');
	header('Pragma: public');
	echo $jetData;


} catch (Exception $e) {
	header('Content-Type: application/json; charset=utf-8');
	$re['status'] = 'error';
	$re['error'] = true;
	$re['msg'] = $e->getMessage();
	echo json_encode($re);
	$rv['re'] = $re;
}

function lw($msg = '', $debugInfo = false)
{
	$msgError = date("Y-m-d H:i:s") . ' - ' . $msg . PHP_EOL;
	if (!empty($debugInfo)) {
		$msgError .= '*** Debug Info ' . var_export($debugInfo, true) . PHP_EOL;
	}

	$folder = __DIR__;
	$filename = $folder . '/_dl.log';
	file_put_contents($filename, $msgError, FILE_APPEND);
}