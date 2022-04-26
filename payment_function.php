<?php
    include_once('./admin/lib/db.inc.php');

    function checkTxnid($txnid) {
        // check whether we've not already processed the transaction before
        // if txnID = "pending", this function returns true
        if (!preg_match('/^[\w\- ]+$/', $txnid))
            throw new Exception("invalid-txnid");
        $txnid = filter_var($txnid, FILTER_SANITIZE_SPECIAL_CHARS);

        $db = ierg4210_DB();
        $q = $db->prepare("SELECT * FROM orders WHERE txnID = ?;");
        $q->bindParam(1, $txnid);
        if ($q->execute())
            return !($q->fetch());
    }

    function verifyTransaction($data) {
        global $paypalUrl;

        $req = 'cmd=_notify-validate';
        foreach ($data as $key => $value) {
            $value = urlencode(stripslashes($value));
            $value = preg_replace('/(.*[^%^0^D])(%0A)(.*)/i', '${1}%0D%0A${3}', $value); // IPN fix
            $req .= "&$key=$value";
        }

        $ch = curl_init($paypalUrl);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
        curl_setopt($ch, CURLOPT_SSLVERSION, 6);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));
        $res = curl_exec($ch);
    
        if (!$res) {
            $errno = curl_errno($ch);
            $errstr = curl_error($ch);
            curl_close($ch);
            throw new Exception("cURL error: [$errno] $errstr");
        }
    
        $info = curl_getinfo($ch);
    
        // Check the http response
        $httpCode = $info['http_code'];
        if ($httpCode != 200) {
            throw new Exception("PayPal responded with http code $httpCode");
        }
    
        curl_close($ch);
    
        return $res === 'VERIFIED';
    }

    function addPayment($data) {
        // adding payment record into db
        // digest: $currency . $email . $salt . $prod_info('PID: ' . $pid . ' Quantity: ' . $quan . ' Price per unit: ' . $price . ' ') . $totalPrice
        // 'num_cart_items' - the number of product list
        // 'mc_gross' - full amount of payment
        if (is_array($data)) {
            $prod_info = "";
            for ($i = 1; $i <= $data['num_cart_items']; $i++) {
                $price = $data['mc_gross_'.$i] / $data['quantity_'.$i];
                // handling if the price is int
                if (strpos(strval($price), '.')) {
                    $prod_info .= 'PID: ' . $data['item_number_'.$i] . ' Quantity: ' . $data['quantity_'.$i] . ' Price per unit: ' . $price . ' ';
                } else {
                    $prod_info .= 'PID: ' . $data['item_number_'.$i] . ' Quantity: ' . $data['quantity_'.$i] . ' Price per unit: ' . $price . '.0' . ' ';;
                }
            }

            if ($res = ierg4210_order_fetch_orderID($data['invoice'])) {
                $digest = hash_hmac('sha256', $data['mc_currency'] . $data['receiver_email'] . $res['salt'] . $prod_info . $data['mc_gross'], $res['salt']);
                if ($digest == $res['digest']) {
                    $db = ierg4210_DB();
                    $q = $db->prepare('UPDATE orders SET txnID = ?, product = ? WHERE orderID = ?;');
                    $q->bindParam(1, $data['txn_id']);
                    $q->bindParam(2, $prod_info);
                    $q->bindParam(3, $data['invoice']);
                    $q->execute();

                    return $db->lastInsertId();
                }
            }
        }
        return false;
    }
?>