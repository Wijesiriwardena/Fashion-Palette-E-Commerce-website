<?php
global $pro_dir, $back_route;


// If not an admin, redirect to default route
if (!is_admin()) {
    header("Location: " . $back_route);
    exit();
}

// get order_id from query params
$queries = array();
parse_str($_SERVER['QUERY_STRING'], $queries);

if (!isset($queries['order_id'])) {
    header("Location: " . $back_route);
    exit();
}

$product_view_route = $pro_dir . 'admin/product/update?product_id=';
$order_id = intval($queries['order_id']);

$conn = connect_db();

// Write the SQL statement to fetch orders
$sql = "SELECT * FROM `order_item` as o
            LEFT JOIN `product` as p on o.product_id = p.product_id
            LEFT JOIN `item_spec` as isp on o.item_spec_id = isp.item_spec_id
            LEFT JOIN `size` as s on isp.size_id = s.size_id
            WHERE o.order_id = ?
            ORDER BY o.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $order_id);

// Execute the statement
if (!$stmt->execute()) {
    $_SESSION['form_data'] = ['error' => 'Error while retrieving order items: ' . $stmt->error];
    $stmt->close();
    $conn->close();
    header("Location: " . $_SERVER["REQUEST_URI"]);
    exit();
}

$result = $stmt->get_result();
$item_count = $result->num_rows;

$stmt->close();
$conn->close();

?>

<div class="container my-5" style="min-height: 80vh">
    <div class="flex-box mb-4">
        <div class="form-title">ORDER SUMMARY</div>
    </div>
    <hr>

    <div class="flex-box mb-4">
        <div class="text-content">Order Items</div>
        <div class="text-content"><?php echo $item_count ?> Items</div>
    </div>

    <?php
    // Check if there are any users
    if ($item_count > 0) { ?>
        <table class="table table-borderless">
            <thead class="thead-white">
            <tr>
                <th>Product Name</th>
                <th style="width: 150px" class="text-right">Size</th>
                <th style="width: 150px" class="text-right">Quantity</th>
                <th style="width: 150px" class="text-right">Cost Per Item</th>
                <th style="width: 150px" class="text-right">Total Cost</th>
            </tr>
            </thead>
            <tbody>
            <?php
            $order_items = $result->fetch_all(MYSQLI_ASSOC);
            // Loop through the users and create table rows
            foreach ($order_items as $order_item) : ?>
                <tr>
                    <td>
                        <a class="form-link" style="font-size: 16px; float: left;"
                           href="<?php echo $product_view_route . $order_item['product_id'] ?>">
                            <?php echo htmlspecialchars($order_item['product_name']) ?></a>
                    </td>
                    <td style="width: 150px" class="text-right">
                        <?php echo htmlspecialchars($order_item['size_code']) ?>
                    </td>
                    <td style="width: 150px" class="text-right">
                        <?php echo htmlspecialchars($order_item['quantity']) ?>
                    </td>
                    <td style="width: 150px" class="text-right">
                        <?php echo htmlspecialchars('$' . $order_item['price']) ?>
                    </td>
                    <td style="width: 150px" class="text-right">
                        <?php echo htmlspecialchars('$' . $order_item['price']) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php } else { ?>
        <p>No order items found.</p>
    <?php } ?>
</div>

