<?php
    include_once('../login/auth.php');
    include_once('../csrf.php');
    session_start();

    // validate session
    if (!auth()) {
        header('Location: ../login.php');
        exit();
    } else {
        // check user authentication
        if (!is_admin(auth())) {
            header('Location: ../index.php');
            exit();
        }
    }


header('Content-Type: application/json');

// input validation and sanitization
if (empty($_REQUEST['action']) || !preg_match('/^\w+$/', $_REQUEST['action'])) {
	echo json_encode(array('failed'=>'undefined'));
	exit();
}
$action = filter_var($_REQUEST['action'], FILTER_SANITIZE_SPECIAL_CHARS);

if (empty($_POST['nonce']) || !preg_match('/^\d+$/', $_POST['nonce'])) {
    echo json_encode(array('failed'=>'undefined'));
	exit();
}
$nonce = filter_var($_POST['nonce'], FILTER_SANITIZE_NUMBER_INT);

// The following calls the appropriate function based to the request parameter $_REQUEST['action'],
//   (e.g. When $_REQUEST['action'] is 'cat_insert', the function ierg4210_cat_insert() is called)
// the return values of the functions are then encoded in JSON format and used as output
try {
	if (($returnVal = call_user_func('ierg4210_' . $_REQUEST['action'])) === false) {
		if ($db && $db->errorCode()) 
			error_log(print_r($db->errorInfo(), true));
		echo json_encode(array('failed'=>'1'));
	}
    csrf_verifyNonce($action, $nonce);
	echo 'while(1);' . json_encode(array('success' => $returnVal));
} catch(PDOException $e) {
	error_log($e->getMessage());
	echo json_encode(array('failed'=>'error-db'));
} catch(Exception $e) {
	echo 'while(1);' . json_encode(array('failed' => $e->getMessage()));
}
?>