<?php
    include_once('./admin/lib/db.inc.php');
    include_once('./login/auth.php');
    include_once('./csrf.php');
    session_start();

    function ierg4210_login() {
        if (empty($_POST['email']) || empty($_POST['password'])
        || !preg_match("/^[\w=+\-\/][\w=\'+\-\/\.]*@[\w\-]+(\.[\w\-]+)*(\.[\w]{2,6})$/", $_POST['email'])
        || !preg_match("/^[\w@#$%\^\&\*\-]+$/", $_POST['password']))
        throw new Exception('Wrong Credentials');
        else {
            $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
            $password = $_POST['password'];

            $db = ierg4210_DB();
            $q = $db->prepare("SELECT * FROM account WHERE email = ?;");
            $q->bindParam(1, $email);
            if ($q->execute() && ($res = $q->fetch()) && $res['password'] == hash_hmac('sha256', $password, $res['salt'])) {
                $exp = time() + 3600 * 24 * 3;
                $token = array(
                    'em' => $res['email'],
                    'exp' => $exp,
                    'k' => hash_hmac('sha256', $exp . $res['password'], $res['salt'])
                );
                setcookie('auth', json_encode($token), $exp, '', '', true, true);
                $_SESSION['auth'] = $token;

                // No session fixation vulnerabilities
                session_regenerate_id();

                if ($res['admin'] == 1) {
                    header('Location: admin/admin_index.php');
                    exit();
                } else {
                    header('Location: index.php');
                    exit();
                }
            } else throw new Exception('Wrong email or password');
        }
    }

    function ierg4210_logout() {
        setcookie('auth', '', time()-3600, '', '', true, true);
        unset($_COOKIE['auth']);
        $_SESSION['auth'] = null;

        header('Location: login.php', true, 302);
        exit();
    }

    function ierg4210_changepw() {
        if (empty($_POST['old_password']) || empty($_POST['new_password'])
        || !preg_match("/^[\w@#$%\^\&\*\-]+$/", $_POST['old_password'])
        || !preg_match("/^[\w@#$%\^\&\*\-]+$/", $_POST['new_password']))
            throw new Exception('Wrong Credentials');
        else {
            $email = auth();
            if (!$email) throw new Exception('Authentication Error');
            $email = filter_var($email, FILTER_SANITIZE_EMAIL);

            $old_password = $_POST['old_password'];
            $db = ierg4210_DB();
            $q = $db->prepare("SELECT * FROM account WHERE email = ?;");
            $q->bindParam(1, $email);

            if ($q->execute() && ($res = $q->fetch()) && $res['password'] == hash_hmac('sha256', $old_password, $res['salt'])) {
                $salt = mt_rand();
                $new_password = hash_hmac('sha256', $_POST['new_password'], $salt);
                $q = $db->prepare("UPDATE account SET password = ?, salt = ? WHERE email = ?;");
                $q->bindParam(1, $new_password);
                $q->bindParam(2, $salt);
                $q->bindParam(3, $email);
                $q->execute();
                ierg4210_logout();
                exit();
            } else throw new Exception('Wrong Password');
        }
    }

    // input validation and sanitization
    if (empty($_REQUEST['action']) || !preg_match('/^\w+$/', $_REQUEST['action'])) {
	    echo json_encode(array('failed'=>'undefined'));
	    exit();
    }
    $action = filter_var($_REQUEST['action'], FILTER_SANITIZE_SPECIAL_CHARS);

    // The following calls the appropriate function based to the request parameter $_REQUEST['action'],
    //   (e.g. When $_REQUEST['action'] is 'cat_insert', the function ierg4210_cat_insert() is called)
    // the return values of the functions are then encoded in JSON format and used as output
    try {
        if (($returnVal = call_user_func('ierg4210_' . $action)) === false) {
            if ($db && $db->errorCode()) 
                error_log(print_r($db->errorInfo(), true));
            echo json_encode(array('failed'=>'1'));
        }
        if ($action != 'logout') {
            if (empty($_POST['nonce']) || !preg_match('/^\d+$/', $_POST['nonce'])) {
                echo json_encode(array('failed'=>'undefined'));
                exit();
            }
            $nonce = filter_var($_POST['nonce'], FILTER_SANITIZE_NUMBER_INT);
            csrf_verifyNonce($action, $nonce);
        }
        echo 'while(1);' . json_encode(array('success' => $returnVal));
    } catch(PDOException $e) {
        error_log($e->getMessage());
        echo json_encode(array('failed'=>'error-db'));
    } catch(Exception $e) {
        echo 'while(1);' . json_encode(array('failed' => $e->getMessage()));
    }
?>
