<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['cart'])) {
    $product_id = $_POST['product_id'];

    foreach ($_SESSION['cart'] as $key => $item) {
        if ($item['product_id'] == $product_id) {
            unset($_SESSION['cart'][$key]);
        }
    }

    $_SESSION['cart'] = array_values($_SESSION['cart']);
}


header('Location: cart.php');
exit;
?>