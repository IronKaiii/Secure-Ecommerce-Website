<?php
    include_once('./admin/lib/db.inc.php');
    include_once('./login/auth.php');
    header('Content-Type: application/json');
    session_start();

    function ierg4210_digest() {
        // digest's elements
        $currency = "HKD";
        $email = "sb-ujnug15566433@business.example.com";
        $salt = mt_rand();
        $cart = json_decode($_POST["cart"]);
        $totalPrice = 0;
        $prod_info = "";
        $product = array();
        $txnID = "pending";

        if (!auth()) $buyer = 'Guest';
        else $buyer = auth();

        // obtaining data from the cart
        foreach($cart as $pid => $quan) {
            // check whether pid and quantity is positive (>0)
            if (!preg_match('/^\d+$/', $pid)) continue;
            if (!preg_match('/^\d+$/', $quan)) continue;
            $pid = filter_var($pid, FILTER_SANITIZE_NUMBER_INT);
            $quan = filter_var($quan, FILTER_SANITIZE_NUMBER_INT);

            $res = ierg4210_prod_fetchOne($pid);
            foreach($res as $value) {
                $name = filter_var($value["NAME"], FILTER_SANITIZE_SPECIAL_CHARS);
                $price = filter_var($value["PRICE"], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            }
            $prod_info .= 'PID: ' . $pid . ' Quantity: ' . $quan . ' Price per unit: ' . $price . ' ';
            $totalPrice += $price * $quan;

            // product array for Paypal hidden form insertion (name, PID, price, quantity)
            array_push($product, [$name, $pid, $price, $quan]);
        }

        // Generating the value of digest
        $totalPrice = number_format($totalPrice, 2, '.', '');
        $digest = hash_hmac('sha256', $currency . $email . $salt . $prod_info . $totalPrice , $salt);

        // Storing all the elements into DB
        $db = ierg4210_DB();
        $q = $db->prepare('INSERT INTO orders VALUES (null, ?, ?, ?, ?, ?)');
        $q->bindParam(1, $buyer);
        $q->bindParam(2, $prod_info);
        $q->bindParam(3, $digest);
        $q->bindParam(4, $salt);
        $q->bindParam(5, $txnID);
        $q->execute();

        $invoice = $db->lastInsertId();
        // return selected products, invoice, custom(digest) into PayPal hidden form
        return array("product" => $product, "invoice" => $invoice, "custom" => $digest);
    }

    try {
        if (($returnVal = call_user_func('ierg4210_digest')) === false) {
            if ($db && $db->errorCode()) 
                error_log(print_r($db->errorInfo(), true));
            echo json_encode(array('failed'=>'1'));
        }
        else echo json_encode($returnVal); // output the product info with JSON if no error
    } catch(PDOException $e) {
        error_log($e->getMessage());
        echo json_encode(array('failed'=>'error-db'));
    } catch(Exception $e) {
        echo 'while(1);' . json_encode(array('failed' => $e->getMessage()));
    }
?>