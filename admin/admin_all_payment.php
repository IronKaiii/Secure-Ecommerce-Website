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

    $order_res = ierg4210_order_fetchAll();
    $order_options = '';

    foreach ($order_res as $value) {
        $order_options .= '<tr>';
        $order_options .= '<th>' .htmlspecialchars($value["orderID"]). '</th>';
        $order_options .= '<th>' .htmlspecialchars($value["buyer"]). '</th>';
        $order_options .= '<th>' .htmlspecialchars($value["product"]). '</th>';
        if ($value["txnID"] == "pending") {
            $order_options .= '<th>Unsuccessful</th>';
        } else {
            $order_options .= '<th>Successful, TxnID: ' .htmlspecialchars($value["txnID"]). '</th>';
        }
        $order_options .= '</tr>';
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
        <legend> Recent 20 orders</legend>
        <table class="order">
            <tr>
                <th>OrderID</th>
                <th>Buyer Email</th>
                <th>Product List</th>
                <th>Payment Status</th>
            </tr> 
            <?php echo $order_options; ?>
        </table>
    </fieldset>
    <div class="manage">
        <a href="admin_index.php"><button class="indexButton">Back</button></a>
    </div>
</body>
</html>