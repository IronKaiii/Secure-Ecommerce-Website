<?php
    include_once('./login/auth.php');
    include_once('./csrf.php');
    session_start();

    // validate user authentication
    if (auth()) {
        if(is_admin(auth())) {
            header('Location: admin/admin_index.php');
            exit();
        } else if (!is_admin(auth())) {
            header('Location: index.php');
            exit();
        }
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
        <form id="login" method="POST" action="auth-process.php?action=login" enctype="multipart/form-data">
            <h3>Login Here</h3>
            <label for="email"> Email</label>
            <div> <input id="email" type="email" name="email" placeholder="Email" required="required" pattern="^[\w=+\-\/][\w=\'+\-\/\.]*@[\w\-]+(\.[\w\-]+)*(\.[\w]{2,6})$"/></div>
            <label for="password"> Password</label>
            <div> <input id="password" type="password" name="password" placeholder="Password" required="required" pattern="^[\w@#$%\^\&\*\-]+$"/></div>
            <input type="hidden" name="nonce" value="<?php echo csrf_getNonce("login"); ?>"/>
            <input type="submit" value="Login"/>
        </form>
    </fieldset>
    <div class="manage">
        <a href="index.php"><button class="indexButton">Back</button></a>
    </div>
</body>
</html>
