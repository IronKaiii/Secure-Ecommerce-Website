<?php
    require __DIR__.'/admin/lib/db.inc.php';
    include_once('./login/auth.php');
    include_once('./csrf.php');
    session_start();

    // validate user authentication
    if (!auth()) {
        header('Location: index.php');
        exit();
    }

    $email = auth();
    $order_res = ierg4210_order_fetch_email($email);
    $order_options = '';

    foreach ($order_res as $value) {
    $order_options .= '<tr>';
    $order_options .= '<th>' .htmlspecialchars($value["orderID"]). '</th>';
    $order_options .= '<th>' .htmlspecialchars($value["buyer"]). '</th>';
    $order_options .= '<th>' .htmlspecialchars($value["product"]). '</th>';
    // Do not show the TxnID to normal user
    if ($value["txnID"] == "pending") {
        $order_options .= '<th>Unsuccessful</th>';
    } else {
        $order_options .= '<th>Successful</th>';
    }
    $order_options .= '</tr>';
}
?>

<html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ktk020's IERG4210 Login</title>
    <link rel="stylesheet" href="./admin/admin.css">
    <!-- Import Google Font-->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;600&display=swap" rel="stylesheet">
</head>
<body>
    <fieldset>
    <fieldset>
        <legend> Your most recent 5 orders</legend>
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
        <a href="index.php"><button class="indexButton">Back</button></a>
    </div>
</body>
</html>