<?php
function ierg4210_DB() {
	// connect to the database
	// TODO: change the following path if needed
	// Warning: NEVER put your db in a publicly accessible location
	$db = new PDO('sqlite:/var/www/cart.db');

	// enable foreign key support
	$db->query('PRAGMA foreign_keys = ON;');

	// FETCH_ASSOC:
	// Specifies that the fetch method shall return each row as an
	// array indexed by column name as returned in the corresponding
	// result set. If the result set contains multiple columns with
	// the same name, PDO::FETCH_ASSOC returns only a single value
	// per column name.
	$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

	return $db;
}

function ierg4210_cat_fetchall() {
    // DB manipulation
    global $db;
    $db = ierg4210_DB();
    $q = $db->prepare("SELECT * FROM CATEGORIES LIMIT 100;");
    if ($q->execute())
        return $q->fetchAll();
}

function ierg4210_cat_fetchOne($catid) {
    if (!preg_match('/^\d*$/', $catid))
        throw new Exception("invalid-catid");
    $catid = filter_var($catid, FILTER_SANITIZE_NUMBER_INT);

    // DB manipulation
    global $db;
    $db = ierg4210_DB();
    $q = $db->prepare("SELECT * FROM CATEGORIES WHERE CATID = ?;");
    $q->bindParam(1, $catid);
    if ($q->execute())
        return $q->fetchAll();
}

function ierg4210_prod_fetchall(){
    global $db;
    $db = ierg4210_DB();
    $q = $db->prepare("SELECT * FROM PRODUCTS LIMIT 100;");
    if ($q->execute())
        return $q->fetchAll();
}

function ierg4210_prod_fetchallForPage($first_prod_in_page, $prod_per_page){
    if (!preg_match('/^\d*$/', $first_prod_in_page) || !preg_match('/^\d*$/', $prod_per_page))
        throw new Exception("invalid-number");
    $first_prod_in_page = filter_var($first_prod_in_page, FILTER_SANITIZE_NUMBER_INT);
    $prod_per_page = filter_var($prod_per_page, FILTER_SANITIZE_NUMBER_INT);

    global $db;
    $db = ierg4210_DB();
    $q = $db->prepare("SELECT * FROM PRODUCTS LIMIT ?, ?;");
    $q->bindParam(1, $first_prod_in_page);
    $q->bindParam(2, $prod_per_page);
    if ($q->execute())
        return $q->fetchAll();
}

function ierg4210_prod_fetchAllInCat($catid) {
    if (!preg_match('/^\d*$/', $catid))
        throw new Exception("invalid-catid");
    $catid = filter_var($catid, FILTER_SANITIZE_NUMBER_INT);

    global $db;
    $db = ierg4210_DB();
    $q = $db->prepare("SELECT * FROM PRODUCTS WHERE CATID = ? LIMIT 100;");
    $q->bindParam(1, $catid);
    if ($q->execute())
        return $q->fetchAll();
}

function ierg4210_prod_fetchOne($pid) {
    if (!preg_match('/^\d*$/', $pid))
        throw new Exception("invalid-pid");
    $pid = filter_var($pid, FILTER_SANITIZE_NUMBER_INT);

    global $db;
    $db = ierg4210_DB();
    $q = $db->prepare("SELECT * FROM PRODUCTS WHERE PID = ?;");
    $q->bindParam(1, $pid);
    if ($q->execute())
        return $q->fetchAll();
}

function ierg4210_prod_fetchOneForCart($pid) {
    if (!preg_match('/^\d*$/', $pid))
        throw new Exception("invalid-pid");
    $pid = filter_var($pid, FILTER_SANITIZE_NUMBER_INT);

    global $db;
    $db = ierg4210_DB();
    $q = $db->prepare("SELECT NAME, PRICE FROM PRODUCTS WHERE PID = ?;");
    $q->bindParam(1, $pid);
    if ($q->execute())
        return $q->fetchAll();
}

// Since this form will take file upload, we use the tranditional (simpler) rather than AJAX form submission.
// Therefore, after handling the request (DB insert and file copy), this function then redirects back to admin.html
function ierg4210_prod_insert() {
    // input validation or sanitization

    // DB manipulation
    global $db;
    $db = ierg4210_DB();

    // TODO: complete the rest of the INSERT command
    if (!preg_match('/^\d*$/', $_POST['catid']))
        throw new Exception("invalid-catid");
    $_POST['catid'] = (int) $_POST['catid'];
    if (!preg_match('/^[\w\- ]+$/', $_POST['name']))
        throw new Exception("invalid-name");
    if (!preg_match('/^[\d\.]+$/', $_POST['price']))
        throw new Exception("invalid-price");
    if (!preg_match('/^[\w\- ]+$/', $_POST['description']))
        throw new Exception("invalid-textt");
    if (!preg_match('/^[\d*]+$/', $_POST['inventory']))
        throw new Exception("invalid-inventory");
        
    // Copy the uploaded file to a folder which can be publicly accessible at incl/img/[pid].jpg
    // max: 10mb
    // accepted file format: jpeg, png, gif
    if ($_FILES["file"]["error"] == 0
        && 
        ($_FILES["file"]["type"] == "image/jpeg" || // identify the file type
         $_FILES["file"]["type"] == "image/png" || 
         $_FILES["file"]["type"] == "image/gif" )
        && 
        (mime_content_type($_FILES["file"]["tmp_name"]) == "image/jpeg" || // use mime to confirm the file type
         mime_content_type($_FILES["file"]["tmp_name"]) == "image/png" ||
         mime_content_type($_FILES["file"]["tmp_name"]) == "image/gif" )
        && $_FILES["file"]["size"] < 11000000) { // 11mb max is set for buffer


        $catid = filter_var($_POST["catid"], FILTER_SANITIZE_NUMBER_INT);
        $name = filter_var($_POST["name"], FILTER_SANITIZE_SPECIAL_CHARS);
        $price = filter_var($_POST["price"], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $desc = filter_var($_POST["description"], FILTER_SANITIZE_SPECIAL_CHARS);
        $inv = filter_var($_POST['inventory'], FILTER_SANITIZE_NUMBER_INT);
        $sql="INSERT INTO PRODUCTS (CATID, NAME, PRICE, DESCRIPTION, INVENTORY) VALUES (?, ?, ?, ?, ?)";
        $q = $db->prepare($sql);
        $q->bindParam(1, $catid);
        $q->bindParam(2, $name);
        $q->bindParam(3, $price);
        $q->bindParam(4, $desc);
        $q->bindParam(5, $inv);
        $q->execute();
        $lastId = $db->lastInsertId();

        // for jpg file
        if ($_FILES["file"]["type"] == "image/jpeg") {
            // Note: Take care of the permission of destination folder (hints: current user is apache)
            if (move_uploaded_file($_FILES["file"]["tmp_name"], "/var/www/html/admin/lib/images/" . $lastId . ".jpg")) {
                // save img name into DB
                $sql="UPDATE PRODUCTS SET IMGNAME = ? WHERE PID = ?";
                $q = $db->prepare($sql);
                $img_name = $lastId . ".jpg";
                $q->bindParam(1, $img_name);
                $q->bindParam(2, $lastId);
                $q->execute();

                // redirect back to original page; you may comment it during debug
                header('Location: admin_index.php');
                exit();
            }
        }

        // for png file
        if ($_FILES["file"]["type"] == "image/png") {
            // Note: Take care of the permission of destination folder (hints: current user is apache)
            if (move_uploaded_file($_FILES["file"]["tmp_name"], "/var/www/html/admin/lib/images/" . $lastId . ".png")) {
                // save img name into DB
                $sql="UPDATE PRODUCTS SET IMGNAME = ? WHERE PID = ?";
                $q = $db->prepare($sql);
                $img_name = $lastId . ".png";
                $q->bindParam(1, $img_name);
                $q->bindParam(2, $lastId);
                $q->execute();
                
                // redirect back to original page; you may comment it during debug
                header('Location: admin_index.php');
                exit();
            }
        }

        // for gif file
        if ($_FILES["file"]["type"] == "image/gif") {
            // Note: Take care of the permission of destination folder (hints: current user is apache)
            if (move_uploaded_file($_FILES["file"]["tmp_name"], "/var/www/html/admin/lib/images/" . $lastId . ".gif")) {
                // save img name into DB
                $sql="UPDATE PRODUCTS SET IMGNAME = ? WHERE PID = ?";
                $q = $db->prepare($sql);
                $img_name = $lastId . ".gif";
                $q->bindParam(1, $img_name);
                $q->bindParam(2, $lastId);
                $q->execute();

                // redirect back to original page; you may comment it during debug
                header('Location: admin_index.php');
                exit();
            }
        }
    }
    // Only an invalid file will result in the execution below
    // To replace the content-type header which was json and output an error message
    header('Content-Type: text/html; charset=utf-8');
    echo 'Invalid file detected. <br/><a href="javascript:history.back();">Back to admin panel.</a>';
    exit();
}

// TODO: add other functions here to make the whole application complete
function ierg4210_cat_insert() {
    global $db;
    $db = ierg4210_DB();

    if (!preg_match('/^[\w\- ]+$/', $_POST['name']))
        throw new Exception("invalid-name");
    
    $name = filter_var($_POST["name"], FILTER_SANITIZE_SPECIAL_CHARS);
    $sql="INSERT INTO CATEGORIES (NAME) VALUES (?)";
    $q = $db->prepare($sql);
    $q->bindParam(1, $name);
    $q->execute();

    header('Location: admin_index.php');
    exit();
}

function ierg4210_cat_edit(){
    global $db;
    $db = ierg4210_DB();

    if (!preg_match('/^\d*$/', $_POST['catid']))
        throw new Exception("invalid-catid");
    $_POST['catid'] = (int) $_POST['catid'];
    if (!preg_match('/^[\w\- ]+$/', $_POST['name']))
        throw new Exception("invalid-name");

    $catid = filter_var($_POST["catid"], FILTER_SANITIZE_NUMBER_INT);
    $name = filter_var($_POST["name"], FILTER_SANITIZE_SPECIAL_CHARS);
    $sql="UPDATE CATEGORIES SET NAME = ? WHERE CATID = ?";
    $q = $db->prepare($sql);
    $q->bindParam(1, $name);
    $q->bindParam(2, $catid);
    $q->execute();

    header('Location: admin_index.php');
    exit();
}

function ierg4210_cat_delete(){
    global $db;
    $db = ierg4210_DB();

    if (!preg_match('/^\d*$/', $_POST['catid']))
        throw new Exception("invalid-catid");
    $_POST['catid'] = (int) $_POST['catid'];

    $catid = filter_var($_POST["catid"], FILTER_SANITIZE_NUMBER_INT);
    $sql="DELETE FROM PRODUCTS WHERE CATID = ?";
    $q = $db->prepare($sql);
    $q->bindParam(1, $catid);
    $q->execute();

    $sql="DELETE FROM CATEGORIES WHERE CATID = ?";
    $q = $db->prepare($sql);
    $q->bindParam(1, $catid);
    $q->execute();

    header('Location: admin_index.php');
    exit();
}

function ierg4210_prod_delete(){
    global $db;
    $db = ierg4210_DB();

    if (!preg_match('/^\d*$/', $_POST['pid']))
        throw new Exception("invalid-pid");
    $_POST['pid'] = (int) $_POST['pid'];

    $pid = filter_var($_POST["pid"], FILTER_SANITIZE_NUMBER_INT);
    $sql="DELETE FROM PRODUCTS WHERE PID = ?";
    $q = $db->prepare($sql);
    $q->bindParam(1, $pid);
    $q->execute();

    header('Location: admin_index.php');
    exit();
}

function ierg4210_prod_edit(){
    global $db;
    $db = ierg4210_DB();

    if (!preg_match('/^\d*$/', $_POST['pid']))
        throw new Exception("invalid-pid");
    $_POST['pid'] = (int) $_POST['pid'];
    if (!preg_match('/^[\w\- ]+$/', $_POST['name']))
        throw new Exception("invalid-name");
    if (!preg_match('/^[\d\.]+$/', $_POST['price']))
        throw new Exception("invalid-price");
    if (!preg_match('/^[\w\- ]+$/', $_POST['description']))
        throw new Exception("invalid-textt");
    if (!preg_match('/^[\d*]+$/', $_POST['inventory']))
        throw new Exception("invalid-inventory");

    // max: 10mb
    // accepted file format: jpeg, png, gif
    if ($_FILES["file"]["error"] == 0
        && 
        ($_FILES["file"]["type"] == "image/jpeg" || // identify the file type
         $_FILES["file"]["type"] == "image/png" || 
         $_FILES["file"]["type"] == "image/gif" )
        && 
        (mime_content_type($_FILES["file"]["tmp_name"]) == "image/jpeg" || // use mime to confirm the file type
         mime_content_type($_FILES["file"]["tmp_name"]) == "image/png" ||
         mime_content_type($_FILES["file"]["tmp_name"]) == "image/gif" )
        && $_FILES["file"]["size"] <= 11000000) {


        $pid = filter_var($_POST["pid"], FILTER_SANITIZE_NUMBER_INT);
        $name = filter_var($_POST["name"], FILTER_SANITIZE_SPECIAL_CHARS);
        $price = filter_var($_POST["price"], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $desc = filter_var($_POST["description"], FILTER_SANITIZE_SPECIAL_CHARS);
        $inv = filter_var($_POST['inventory'], FILTER_SANITIZE_NUMBER_INT);
        $sql="UPDATE PRODUCTS SET NAME = ?, PRICE = ?, DESCRIPTION = ?, INVENTORY = ? WHERE PID = ?";
        $q = $db->prepare($sql);
        $q->bindParam(1, $name);
        $q->bindParam(2, $price);
        $q->bindParam(3, $desc);
        $q->bindParam(4, $inv);
        $q->bindParam(5, $pid);
        $q->execute();

        // for jpg file
        if ($_FILES["file"]["type"] == "image/jpeg") {
            // save img name into DB
            $sql="UPDATE PRODUCTS SET IMGNAME = ? WHERE PID = ?";
            $q = $db->prepare($sql);
            $img_name = $pid . ".jpg";
            $q->bindParam(1, $img_name);
            $q->bindParam(2, $pid);
            $q->execute();
            
            // Note: Take care of the permission of destination folder (hints: current user is apache)
            if (move_uploaded_file($_FILES["file"]["tmp_name"], "/var/www/html/admin/lib/images/" . $pid . ".jpg")) {
                // redirect back to original page; you may comment it during debug
                header('Location: admin_index.php');
                exit();
            }
        }

        // for png file
        if ($_FILES["file"]["type"] == "image/png") {
            // save img name into DB
            $sql="UPDATE PRODUCTS SET IMGNAME = ? WHERE PID = ?";
            $q = $db->prepare($sql);
            $img_name = $pid . ".png";
            $q->bindParam(1, $img_name);
            $q->bindParam(2, $pid);
            $q->execute();

            // Note: Take care of the permission of destination folder (hints: current user is apache)
            if (move_uploaded_file($_FILES["file"]["tmp_name"], "/var/www/html/admin/lib/images/" . $pid . ".png")) {
                // redirect back to original page; you may comment it during debug
                header('Location: admin_index.php');
                exit();
            }
        }

        // for gif file
        if ($_FILES["file"]["type"] == "image/gif") {
            // save img name into DB
            $sql="UPDATE PRODUCTS SET IMGNAME = ? WHERE PID = ?";
            $q = $db->prepare($sql);
            $img_name = $pid . ".gif";
            $q->bindParam(1, $img_name);
            $q->bindParam(2, $pid);
            $q->execute();

            // Note: Take care of the permission of destination folder (hints: current user is apache)
            if (move_uploaded_file($_FILES["file"]["tmp_name"], "/var/www/html/admin/lib/images/" . $pid . ".gif")) {
                // redirect back to original page; you may comment it during debug
                header('Location: admin_index.php');
                exit();
            }
        }
    }
    // Only an invalid file will result in the execution below
    // To replace the content-type header which was json and output an error message
    header('Content-Type: text/html; charset=utf-8');
    echo 'Invalid file detected. <br/><a href="javascript:history.back();">Back to admin panel.</a>';
    exit();
}


// payment orders related functions

function ierg4210_order_fetch_orderID($orderID) {
    if (!preg_match('/^\d*$/', $orderID))
        throw new Exception("invalid-orderID");
    $orderID = filter_var($orderID, FILTER_SANITIZE_NUMBER_INT);

    $db = ierg4210_DB();
    $q = $db->prepare("SELECT * FROM orders WHERE orderID = ?;");
    $q->bindParam(1, $orderID);
    if ($q->execute())
        return $q->fetch();
}

function ierg4210_order_fetch_email($email) {
    if (empty($email) 
        || !preg_match("/^[\w=+\-\/][\w=\'+\-\/\.]*@[\w\-]+(\.[\w\-]+)*(\.[\w]{2,6})$/", $email)) {
            throw new Exception("invalid-email");
        }
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);

    $db = ierg4210_DB();
    // get the descending order to the latest 5 orders
    $q = $db->prepare("SELECT * FROM orders WHERE buyer = ? ORDER BY orderID DESC LIMIT 5;");
    $q->bindParam(1, $email);
    if ($q->execute())
        return $q->fetchAll();
}

function ierg4210_order_fetchAll() {
    $db = ierg4210_DB();
    // get the descending order to the latest 20 orders
    $q = $db->prepare("SELECT * FROM orders ORDER BY orderID DESC LIMIT 20;");
    if ($q->execute())
        return $q->fetchAll();
}
