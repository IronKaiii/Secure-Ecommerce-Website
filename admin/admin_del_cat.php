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
        <legend> Delete Category</legend>
        <form id="cat_delete" method="POST" action="admin-process.php?action=cat_delete"
        enctype="multipart/form-data">
            <label for="cat_catid"> Category *</label>
            <div> <select id="cat_catid" name="catid"><?php echo $options; ?></select></div>
            <input type="submit" value="Submit"/>
            <input type="hidden" name="nonce" value="<?php echo csrf_getNonce("cat_delete"); ?>"/>
        </form>
    </fieldset>
    <div class="manage">
        <a href="admin_index.php"><button class="indexButton">Back</button></a>
    </div>
</body>
</html>
