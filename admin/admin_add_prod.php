<?php
require __DIR__.'/lib/db.inc.php';
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

$res = ierg4210_cat_fetchall();
$options = '';

foreach ($res as $value){
    $options .= '<option value="'.htmlspecialchars($value["CATID"]).'"> '.htmlspecialchars($value["NAME"]).' </option>';
}
?>

<html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ktk020's IERG4210 Admin</title>
    <link rel="stylesheet" href="admin.css">
    <!-- Import Google Font-->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">
</head>
<body>
    <div class="title"><h1>IERG4210 Admin Page by ktk020</h1></div>
    <fieldset>
        <legend> New Product</legend>
        <form id="prod_insert" method="POST" action="admin-process.php?action=prod_insert"
        enctype="multipart/form-data">
            <label for="prod_catid"> Category *</label>
            <div> <select id="prod_catid" name="catid"><?php echo $options; ?></select></div>
            <label for="prod_name"> Name *</label>
            <div> <input id="prod_name" type="text" name="name" required="required" pattern="^[\w\-]+$"/></div>
            <label for="prod_price"> Price *</label>
            <div> <input id="prod_price" type="text" name="price" required="required" pattern="^\d+\.?\d*$"/></div>
            <label for="prod_desc"> Description *</label>
            <div> <input id="prod_desc" type="text" name="description" pattern="^[\w\-]+$"/> </div>
            <label for="prod_inv"> Inventory *</label>
            <div> <input id="prod_inv" type="text" name="inventory" required="required" pattern="^\d*$"> </div>
            <label for="prod_image"> Image * </label>
            <div id="drop_file_zone">
                <div id="drag_upload_file">
                    <p>Drop file here</p>
                    <p>or</p>
                    <input type="file" id="selectfile" name="file" required="true" accept="image/jpeg, image/png, image/gif" onchange="previewImage(event);">
                    <label class="fileButton" for="selectfile">Select File</label>
                </div>
            </div>
            <div id="preview">
                <img id="previewImg">
            </div>
            <input type="submit" value="Submit"/>
            <input type="hidden" name="nonce" value="<?php echo csrf_getNonce("prod_insert"); ?>"/>
        </form>
    </fieldset>
    <div class="manage">
        <a href="admin_index.php"><button class="indexButton">Back</button></a>
    </div>
    <script src="drag_and_drop.js"></script>
</body>
</html>
