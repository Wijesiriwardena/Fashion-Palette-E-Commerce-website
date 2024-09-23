<?php
global $pro_dir, $user_id, $result;


if (!is_logged_in()) {
    header('Location: ' . $pro_dir . 'login');
    exit();
}

fetch_favourites();
$item_count = $result->num_rows;

// Order delete
if (isset($_GET['order_id'])) {
    cancel_order();
}

function fetch_favourites()
{
    global $user_id, $result;

    $conn = connect_db();

    // Write the SQL statement to fetch favourites
    $sql = "SELECT f.user_id, f.product_id, p.product_name, p.price, b.brand_name as brand FROM `favourite` as f
            LEFT JOIN `product` as p on f.product_id = p.product_id
            LEFT JOIN `brand` as b on p.brand_id = b.brand_id
            WHERE f.user_id = ? 
            ORDER BY f.created_at DESC ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);

    // Execute the statement
    if (!$stmt->execute()) {
        $_SESSION['form_data'] = ['error' => 'Error while retrieving favourites: ' . $stmt->error];
        $stmt->close();
        $conn->close();
        header("Location: " . $_SERVER["REQUEST_URI"]);
        exit();
    }

    $result = $stmt->get_result();
    $stmt->close();
    $conn->close();
}

function cancel_order()
{
    global $my_orders_route, $user_id;
    $order_id = intval($_GET['order_id']);
    $conn = connect_db();

    // Prepare the SQL statement to cancel order
    $sql = "UPDATE `order` SET status = ? WHERE order_id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $status = 'Cancelled';
    $stmt->bind_param('sii', $status, $order_id, $user_id);

    if ($stmt->execute()) {
        $_SESSION["form_data"] = ['message' => 'Order cancelled successfully'];
    } else {
        $_SESSION["form_data"] = ['error' => 'Failed to delete order: ' . $stmt->error];
    }

    $stmt->close();
    $conn->close();

    // Redirect back
    header("Location: " . $my_orders_route);
    exit();
}

?>

<div class="container my-5" style="min-height: 70vh">
    <div class="flex-box">
        <div class="form-title">MY FAVOURITES</div>
        <div class=""><?php echo $item_count ?> Items</div>
    </div>
    <hr>
    <?php
    // Check if there are any cart items
    if ($item_count > 0) {
        include_once __DIR__ . '/../includes/product.php';
        $favourites = $result->fetch_all(MYSQLI_ASSOC);
        // Loop through the products and render product cart views
        ?>
        <div class="container my-5">
            <div class="row">
                <?php foreach ($favourites as $favourite): ?>
                    <div class="col-lg-3 col-md-4 col-sm-12">
                        <?php echo product_favourite_view($favourite); ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php } else { ?>
        <div>
            <div class="text-content">You do not have any favourites</div>
        </div>
    <?php } ?>
</div>



