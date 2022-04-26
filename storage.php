<?php
// Reference to admin-process.php
include_once('admin/lib/db.inc.php');
header('Content-Type: application/json');

// input validation
if (!preg_match('/^\d*$/', $_GET['pid']))
        throw new Exception("invalid-pid");
$pid = (int) $_GET['pid'];

try {
	if (($returnVal = call_user_func('ierg4210_prod_fetchOneForCart', $pid)) === false) {
		if ($db && $db->errorCode()) 
			error_log(print_r($db->errorInfo(), true));
		echo json_encode(array('failed'=>'1'));
	}
	else echo json_encode($returnVal); // output the product info with JSON if no error
	
} catch(PDOException $e) { // debug use
	error_log($e->getMessage());
	echo json_encode(array('failed'=>'error-db'));
} catch(Exception $e) { // debug use
	echo 'while(1);' . json_encode(array('failed' => $e->getMessage()));
}
?>