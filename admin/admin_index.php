<?php
    include_once('../login/auth.php');
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
    <div class="manage">
        <a href="admin_add_cat.php"><button class="indexButton">Add New Category</button></a>
        <a href="admin_edit_cat.php"><button class="indexButton">Edit Category</button></a>
        <a href="admin_del_cat.php"><button class="indexButton">Delete Category</button></a>
    </div>
    <div class="manage">
        <a href="admin_add_prod.php"><button class="indexButton">Add New Product</button></a>
        <a href="admin_edit_prod.php"><button class="indexButton">Edit Product</button></a>
        <a href="admin_del_prod.php"><button class="indexButton">Delete Product</button></a>
    </div>
    <div class="manage">
        <a href="admin_all_payment.php"><button class="indexButton">All Payment Record</button></a>
    </div>
    <div class="manage">
        <a href="../index.php"><button class="indexButton">Back to Shop</button></a>
    </div>
</body>
</html>
