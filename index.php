<?php
require __DIR__.'/admin/lib/db.inc.php';
include_once('login/auth.php');
session_start();

// Pagination
$prod_per_page = 3;
$total_num_prod = sizeof(ierg4210_prod_fetchall());

$total_num_page = ceil($total_num_prod / $prod_per_page);
if (!array_key_exists("page", $_GET)) $page = 1;
else $page = trim($_GET["page"]);
$first_prod_in_page = ($page-1)*$prod_per_page;

$res = ierg4210_cat_fetchall();
$options = '';
$prod_res = ierg4210_prod_fetchallForPage($first_prod_in_page , $prod_per_page);
$prod_options = '';

foreach ($res as $value) {
    $options .= '<a href="index.php?catid='.htmlspecialchars($value["CATID"]).'">'.htmlspecialchars($value["NAME"]).'</a>';
}

foreach ($prod_res as $value) {
    $prod_options .= '<div class="product-card">';
    $prod_options .= '<a href="product.php?pid='.urlencode($value["PID"]).'"><img src="/admin/lib/images/'.urlencode($value["IMGNAME"]).'"></a>';
    $prod_options .= '<a href="product.php?pid='.urlencode($value["PID"]).'"><h1>'.htmlspecialchars($value["NAME"]).'</h1></a>';
    $prod_options .= '<h1>$'.htmlspecialchars($value["PRICE"]).'</h1>';
    $prod_options .= '<button onclick="add_to_cart('.htmlspecialchars($value["PID"]).')">Add To Cart</button>';
    $prod_options .= '</div>';
}


$ParaExist = array_key_exists("catid", $_GET);
if ($ParaExist) {
    $prodInCat_res = ierg4210_prod_fetchAllInCat(trim($_GET["catid"]));
    $prodInCat_options = '';
    $prodCat = ierg4210_cat_fetchOne(trim($_GET["catid"]));
    $nav_menu_cat = '';

    foreach ($prodInCat_res as $value) {
        $prodInCat_options .= '<div class="product-card">';
        $prodInCat_options .= '<a href="product.php?pid='.urlencode($value["PID"]).'"><img src="/admin/lib/images/'.urlencode($value["IMGNAME"]).'"></a>';
        $prodInCat_options .= '<a href="product.php?pid='.urlencode($value["PID"]).'"><h1>'.htmlspecialchars($value["NAME"]).'</h1></a>';
        $prodInCat_options .= '<h1>$'.htmlspecialchars($value["PRICE"]).'</h1>';
        $prodInCat_options .= '<button onclick="add_to_cart('.htmlspecialchars($value["PID"]).')">Add To Cart</button>';
        $prodInCat_options .= '</div>';
    }

    foreach ($prodCat as $value) {
        $nav_menu_cat .= '<p>></p>';
        $nav_menu_cat .= '<a href="index.php?catid='.urlencode($value["CATID"]).'">'.htmlspecialchars($value["NAME"]).'</a>';
    }
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
        <a href="index.php">Home</a>
        <?php if ($ParaExist) echo $nav_menu_cat ?>
    </div>

    <div class="catBar">
        <h3>Category</h3>
        <div class="catBarList">
            <?php echo $options ?>
        </div>
    </div>

    <div class="product-list">
        <?php 
        if (!$ParaExist) echo $prod_options;
        else echo $prodInCat_options;
        ?>
    </div>
    <div class="pagination">
        <?php
        if (!$ParaExist) {
            for ($pageIndex = 1; $pageIndex<=$total_num_page; $pageIndex++) {
                if (!array_key_exists("page", $_GET) && $pageIndex == 1) {
                    echo '<a class="active" href="index.php?page=' .urlencode($pageIndex). '">' .htmlspecialchars($pageIndex). '</a>';
                }
                else if ($pageIndex == trim($_GET["page"])) {
                    echo '<a class="active" href="index.php?page=' .urlencode($pageIndex). '">' .htmlspecialchars($pageIndex). '</a>';
                }
                else echo '<a href="index.php?page=' .urlencode($pageIndex). '">' .htmlspecialchars($pageIndex). '</a>';
            }
        }
        ?>
    </div>
    <script type="text/javascript" src="shopping_cart.js"></script>
</body>
</html>