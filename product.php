<?php
require __DIR__.'/admin/lib/db.inc.php';
include_once('login/auth.php');
session_start();

$ParaExist = array_key_exists("pid", $_GET);
if ($ParaExist) {
    $OneProd_res = ierg4210_prod_fetchOne(trim($_GET["pid"]));
    $OneProd_options = '';
    $nav_menu_prod = '';

    foreach ($OneProd_res as $value) {
        $OneProd_options .= '<img src="/admin/lib/images/'.urlencode($value["IMGNAME"]).'">';
        $OneProd_options .= '<div class="product-info">';
        $OneProd_options .= '<h1>'.htmlspecialchars($value["NAME"]).'</h1>';
        $OneProd_options .= '<p>'.htmlspecialchars($value["DESCRIPTION"]).'</p>';
        $OneProd_options .= '<h1>$'.htmlspecialchars($value["PRICE"]).'</h1>';
        $OneProd_options .= '<div class="addToCart-in-product"><button onclick="add_to_cart('.htmlspecialchars($value["PID"]).')">Add To Cart</button></div>';
        $OneProd_options .= '<p class="inventory">Inventory: '.htmlspecialchars($value["INVENTORY"]).' left</p>';
        $OneProd_options .= '</div>';
        $nav_menu_prod .= '<p>></p>';
        $nav_menu_prod .= '<a href="product.php?pid='.urlencode($value["PID"]).'">'.htmlspecialchars($value["NAME"]).'</a>';
        $prodCat = ierg4210_cat_fetchOne($value["CATID"]);
    }

    $nav_menu_cat = '';
    
    foreach ($prodCat as $value) {
        $nav_menu_cat .= '<a href="index.php">Home</a>';
        $nav_menu_cat .= '<p>></p>';
        $nav_menu_cat .= '<a href="index.php?catid='.urlencode($value["CATID"]).'">'.htmlspecialchars($value["NAME"]).'</a>';
    }
    $nav_menu_cat .= $nav_menu_prod;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ktk020's IERG4210</title>
    <link rel="stylesheet" href="style.css">
    <!-- Import Google Font-->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">
</head>
<body onload="cart_update()">
    <header>
        <nav>
            <ul class="navbar">
                <a href="index.php"><li>Home</li></a>
                <?php
                    if (is_admin(auth())) {
                        echo '<a href="admin/admin_index.php"><li>Admin</li></a>';
                    }
                ?>
            </ul>
        </nav>
        <div class="login">
            <?php
                if (!auth()) echo '<p>Hi, Guest!</p>';
                else echo '<p>Hi, ' . htmlspecialchars(auth()) . '!</p>';
            ?>
        </div>
        <div class="leftNav">
            <div class="login">
                <?php
                    if (!auth()) echo '<a href="login.php">Login</a>';
                    else {
                        echo '<a href="payment_history.php">Payment History</a>';
                        echo '<a href="change_pw.php">Change Password</a>';
                        echo '<a href="auth-process.php?action=logout">Logout</a>';
                    }
                ?>
            </div>
            <div class="dropdown-cart">
                <button class="cart">Cart</button>
                <div class="cart-container">
                    <ul class="cart-list" id="cart-list"></ul>
                    <div class="total-price-box" id="total-price">
                        <p>Total: $0</p>
                    </div>
                    <form method="POST" id="paypalCart" action="https://www.sandbox.paypal.com/cgi-bin/webscr" onsubmit="cartSubmit(); event.preventDefault();">
			            <input type="hidden" name="cmd" value="_cart" />
			            <input type="hidden" name="upload" value="1" />
			            <input type="hidden" name="business" value="sb-ujnug15566433@business.example.com" />
			            <input type="hidden" name="currency_code" value="HKD" />
			            <input type="hidden" name="charset" value="utf-8" />
			            <input type="hidden" name="custom" value="0" />
			            <input type="hidden" name="invoice" value="0" />
                        <div class="checkout">
			                <input type="submit" value="Checkout" />
                        </div>
		            </form>
                </div>
            </div> 
        </div>
    </header>

    <div class="navmenu">
        <?php echo $nav_menu_cat ?>
    </div>

    <div class="product-page">
        <?php echo $OneProd_options ?>
    </div>
    <script type="text/javascript" src="shopping_cart.js"></script>
</body>
</html>