<?php
    include_once('../admin/lib/db.inc.php');

    function auth() {
        if (!empty($_SESSION['auth']))
            return $_SESSION['auth']['em'];

        if (!empty($_COOKIE['auth'])) {
            if ($t = json_decode(stripslashes($_COOKIE['auth']), true)) {
                if (time() > $t['exp']) return false; // check expire

                $db = ierg4210_DB();
                $q = $db->prepare('SELECT * FROM account WHERE email = ?');
                $q->bindParam(1, $t['em']);
                if ($q->execute() 
                    && ($res = $q->fetch()) 
                    && $t['k'] == hash_hmac('sha256', $t['exp'] . $res['password'], $res['salt'])) {
                    // validate the token
                    $_SESSION['auth'] = $_COOKIE['auth'];
                    return $t['em'];
                }
            }
        }
        return false;
    }

    function is_admin($email) {

        $db = ierg4210_DB();
        $q = $db->prepare('SELECT * FROM account WHERE email = ?');
        $q->bindParam(1, $email);
        if ($q->execute() && ($res = $q->fetch())) {
            if ($res['admin'] == 1) return true;
            if ($res['admin'] == 0) return false;
        }
    }
?>