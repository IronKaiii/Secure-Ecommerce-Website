<?php
    include_once('./login/auth.php');
    include_once('./csrf.php');
    session_start();

    // validate user authentication
    if (!auth()) {
        header('Location: index.php');
        exit();
    }
?>

<html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ktk020's IERG4210 Login</title>
    <link rel="stylesheet" href="login.css">
    <!-- Import Google Font-->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;600&display=swap" rel="stylesheet">
</head>
<body>
    <fieldset>
        <form id="changePW" method="POST" action="auth-process.php?action=changepw" enctype="multipart/form-data">
            <h3>Change your password here </h3>
            <label for="old_password"> Old Password</label>
            <div> <input id="old_password" type="password" name="old_password" placeholder="Enter your old password" required="required" pattern="^[\w@#$%\^\&\*\-]+$"/></div>
            <label for="new_password"> New Password</label>
            <div> <input id="new_password" type="password" name="new_password" placeholder="Enter your new password" required="required" pattern="^[\w@#$%\^\&\*\-]+$"/></div>
            <input type="hidden" name="nonce" value="<?php echo csrf_getNonce("changepw"); ?>"/>
            <input type="submit" value="Change"/>
        </form>
    </fieldset>
    <div class="manage">
        <a href="index.php"><button class="indexButton">Back</button></a>
    </div>
</body>
</html>
