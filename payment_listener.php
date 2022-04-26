<?php
    include_once('./payment_function.php');

    $enableSandbox = true;
    $paypalUrl = $enableSandbox ? 'https://www.sandbox.paypal.com/cgi-bin/webscr' : 'https://www.paypal.com/cgi-bin/webscr';


    // obtain data from Paypal POST
    $data = [
        'num_cart_items' => $_POST['num_cart_items'],
        'mc_gross' => $_POST['mc_gross'],
        'mc_currency' => $_POST['mc_currency'],
        'txn_id' => $_POST['txn_id'],
        'receiver_email' => $_POST['receiver_email'],
        'custom' => $_POST['custom'],
        'invoice' => $_POST['invoice']
    ];

    for ($i = 1; $i <= $data['num_cart_items']; $i++) {
        $data['item_number_'.$i] = $_POST['item_number'.$i];
        $data['quantity_'.$i] = $_POST['quantity'.$i];
        $data['mc_gross_'.$i] = $_POST['mc_gross_'.$i];
    }

    // We need to verify the transaction comes from PayPal and check we've not
    // already processed the transaction before adding the payment to our
    // database.
    if (verifyTransaction($_POST) && checkTxnid($data['txn_id'])) {
        if (addPayment($data) !== false) {
            // Payment successfully added into db.
            echo json_encode(array('success' => '1'));
        }
    } else{
        //Payment failed
        echo json_encode(array('failed' => '1'));
    }
?>