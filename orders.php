<?php
global $pro_dir, $back_route;


// If not an admin, redirect to default route
if (!is_admin()) {
    header("Location: " . $back_route);
    exit();
}

$order_list_route = $pro_dir . 'admin/order';
$order_delete_route = $order_list_route . '?order_id=';
$order_items_route = $order_list_route . '/items?order_id=';
$user_view_route = $pro_dir . 'admin/user/update?user_id=';

$result = fetch_orders();
$item_count = $result->num_rows;

// Order delete
if (isset($_GET['order_id'])) {
    delete_order();
}

function fetch_orders()
{
    $conn = connect_db();
    $sql = "SELECT o.order_id, o.status, o.total, o.shipping_discount, o.sales_tax, o.created_at, o.user_id, 
            u.email as user_email FROM `order` as o 
            LEFT JOIN user as u ON o.user_id = u.user_id 
            ORDER BY o.created_at DESC";

    $stmt = $conn->prepare($sql);
    if (!$stmt->execute()) {
        $_SESSION['form_data'] = ['error' => 'Error while retrieving orders: ' . $stmt->error];
        $stmt->close();
        $conn->close();
        header("Location: " . $_SERVER["REQUEST_URI"]);
        exit();
    }

    $result = $stmt->get_result();
    $stmt->close();
    $conn->close();
    return $result;
}

function delete_order()
{
    global $order_list_route;
    $order_id = intval($_GET['order_id']);
    $conn = connect_db();

    // Prepare the SQL statement to delete order
    $sql = "DELETE FROM `order` WHERE order_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $order_id);

    if ($stmt->execute()) {
        $_SESSION["form_data"] = ['message' => 'Order deleted successfully'];
    } else {
        $_SESSION["form_data"] = ['error' => 'Failed to delete order: ' . $stmt->error];
    }

    $stmt->close();
    $conn->close();

    // Redirect back to order list page
    header("Location: " . $order_list_route);
    exit();
}

?>

<div class="container my-5" style="min-height: 80vh">
    <div class="flex-box">
        <div class="form-title">ORDERS</div>
        <div class=""><?php echo $item_count ?> Items</div>
    </div>
    <hr>
    <?php
    // Check if there are any users
    if ($item_count > 0) { ?>
        <table class="table table-borderless">
            <thead class="thead-white">
            <tr>
                <th>Order ID</th>
                <th>Placed On</th>
                <th>Customer</th>
                <th>Status</th>
                <th>Total Bill</th>
                <th>Shipping Discount</th>
                <th>Sales Tax</th>
                <th width="100px">Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php
            $orders = $result->fetch_all(MYSQLI_ASSOC);
            // Loop through the order and create table rows
            foreach ($orders as $order) : ?>
                <tr>
                    <td><?php echo htmlspecialchars($order['order_id']) ?></td>
                    <td><?php echo htmlspecialchars($order['created_at']) ?></td>
                    <td>
                        <a class="form-link" style="font-size: 16px; float: left;"
                           href="<?php echo $user_view_route . $order['user_id'] ?>">
                            <?php echo htmlspecialchars($order['user_email']) ?></a>
                    </td>
                    <td><?php echo htmlspecialchars($order['status']) ?></td>
                    <td><?php echo htmlspecialchars('$' . $order['total']) ?></td>
                    <td><?php echo htmlspecialchars('$' . $order['shipping_discount']) ?></td>
                    <td><?php echo htmlspecialchars('$' . $order['sales_tax']) ?></td>
                    <td>
                        <a class="mr-2" aria-hidden="true"
                           href="<?php echo $order_items_route . $order['order_id'] ?>">
                            <i class="btn-edit fa fa-pen-to-square"></i></a>
                        <a class="mr-2" aria-hidden="true" href="<?php echo $order_delete_route . $order['order_id'] ?>"
                           onclick="<?php echo 'return confirm(\'Are you sure you want to delete this order?\')' ?>">
                            <i class="btn-delete fa fa-trash"></i></a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php } else { ?>
        <p>No orders found.</p>
    <?php } ?>
</div>

