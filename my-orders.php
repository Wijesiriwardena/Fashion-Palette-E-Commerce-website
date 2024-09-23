<?php
global $pro_dir, $user_id, $result;


if (!is_logged_in()) {
    header('Location: ' . $pro_dir . 'login');
    exit();
}

$my_orders_route = $pro_dir . 'my/orders';

fetch_orders();
$item_count = $result->num_rows;

// Order delete
if (isset($_GET['order_id'])) {
    cancel_order();
}

function fetch_orders()
{
    global $user_id, $result;
    $conn = connect_db();

    // Write the SQL statement to fetch my orders
    $sql = "SELECT * FROM `order_item` as ot
            LEFT JOIN `order` as o on ot.order_id = o.order_id
            LEFT JOIN `product` as p on ot.product_id = p.product_id
            LEFT JOIN `item_spec` as isp on ot.item_spec_id = isp.item_spec_id
            LEFT JOIN `size` as s on isp.size_id = s.size_id
            WHERE ot.order_id IN (SELECT order_id FROM `order` WHERE user_id = ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);

    // Execute the statement
    if (!$stmt->execute()) {
        $_SESSION['form_data'] = ['error' => 'Error while retrieving order items: ' . $stmt->error];
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

<div class="container my-5">
    <div class="flex-box">
        <div class="form-title">MY ORDER ITEMS</div>
        <div class=""><?php echo $item_count ?> Items</div>
    </div>
    <hr>
    <?php
    // Check if there are any cart items
    if ($item_count > 0) {
        include_once __DIR__ . '/../includes/product.php';
        $order_items = $result->fetch_all(MYSQLI_ASSOC);
        // Loop through the products and render product cart views
        ?>
        <div class="container px-0 my-3" style="min-height: 75vh">
            <table class="table table-borderless cart-table">
                <thead class="thead-white">
                <tr>
                    <th style="width: auto;" class="table-header-min">PRODUCT</th>
                    <th style="width: 100px;" class="table-header-min">STATUS</th>
                    <th style="width: 100px;" class="table-header-min">QUANTITY</th>
                    <th style="width: 100px;" class="table-header-min text-right">PRICE</th>
                    <th style="width: 100px;" class="table-header-min text-right">TOTAL</th>
                </tr>
                </thead>
                <tbody id="cart-items">
                <?php foreach ($order_items as $order_item): ?>
                    <tr>
                        <td style="width=auto">
                            <?php echo order_card_view($order_item); ?>
                        </td>
                        <td style="width=100px" class="text-left">
                            <?php echo $order_item['status']; ?>
                        </td>
                        <td style="width=100px" class="text-center">
                            <?php echo $order_item['quantity']; ?>
                        </td>
                        <td style="width=100px" class="text-right">
                            $<?php echo $order_item['price']; ?>
                        </td>
                        <td style="width=100px" class="text-right">
                            $<?php echo $order_item['price'] * $order_item['quantity']; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <hr>
        </div>
    <?php } else { ?>
        <div>
            <div class="text-content">Your do not have any orders</div>
        </div>
    <?php } ?>
</div>

