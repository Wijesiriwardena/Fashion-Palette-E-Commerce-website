<?php
global $pro_dir, $back_route;


// If not an admin, redirect to default route
if (!is_admin()) {
    header("Location: " . $back_route);
    exit();
}

$product_list_route = $pro_dir . 'admin/product';
$product_add_route = $product_list_route . '/create';
$product_del_route = $product_list_route . '?product_id=';
$product_edit_route = $product_list_route . '/update?product_id=';

$result = fetch_products();
$item_count = $result->num_rows;

// Product delete
if (isset($_GET['product_id'])) {
    delete_product();
}

function fetch_products()
{
    $conn = connect_db();
    $sql = "SELECT p.product_id, p.product_name, p.price, p.discount, p.stock, 
        g.gender_name as gender, c.category_name as category, b.brand_name as brand
        FROM product as p 
        LEFT JOIN gender as g on p.gender_id = g.gender_id
        LEFT JOIN category as c on p.category_id = c.category_id
        LEFT JOIN brand as b on p.brand_id = b.brand_id
        ORDER BY p.created_at DESC";

    $stmt = $conn->prepare($sql);
    if (!$stmt->execute()) {
        $_SESSION['form_data'] = ['error' => 'Error while retrieving products: ' . $stmt->error];
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

function delete_product()
{
    global $product_list_route;
    $product_id = intval($_GET['product_id']);
    $conn = connect_db();

    // Prepare the SQL statement to delete the product
    $stmt = $conn->prepare("DELETE FROM product WHERE product_id = ?");
    $stmt->bind_param('i', $product_id);

    if ($stmt->execute()) {
        $_SESSION["form_data"] = ['message' => 'Product deleted successfully'];
    } else {
        $_SESSION["form_data"] = ['error' => 'Failed to delete product: ' . $stmt->error];
    }

    $stmt->close();
    $conn->close();

    // Redirect back to product list page
    header("Location: " . $product_list_route);
}

?>

<div class="container my-5" style="min-height: 700px">
    <div class="flex-box mb-4">
        <div class="form-title">PRODUCT LIST</div>
        <button class="btn btn-sm btn-custom-primary" onclick="location='<?php echo $product_add_route ?>'">
            Create Product
        </button>
    </div>
    <hr>
    <?php
    // Check if there are any users
    if ($item_count > 0) { ?>
        <table class="table table-borderless">
            <thead class="thead-white">
            <tr>
                <th>Product Name</th>
                <th>Product For</th>
                <th>Category</th>
                <th>Brand</th>
                <th>Price</th>
                <th>Discount</th>
                <th>Current Stock</th>
                <th width="100px">Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php
            $products = $result->fetch_all(MYSQLI_ASSOC);
            // Loop through the products and create table rows
            foreach ($products as $product) : ?>
                <tr>
                    <td><?php echo htmlspecialchars($product['product_name']) ?></td>
                    <td><?php echo htmlspecialchars($product['gender']) ?></td>
                    <td><?php echo htmlspecialchars($product['category']) ?></td>
                    <td><?php echo htmlspecialchars($product['brand']) ?></td>
                    <td><?php echo htmlspecialchars('$' . $product['price']) ?></td>
                    <td><?php echo htmlspecialchars($product['discount'] . '%') ?></td>
                    <td><?php echo htmlspecialchars($product['stock']) ?></td>
                    <td>
                        <a class="mr-2" aria-hidden="true"
                           href="<?php echo $product_edit_route . $product['product_id'] ?>">
                            <i class="btn-edit fa fa-pen-to-square"></i></a>
                        <a class="mr-2" aria-hidden="true"
                           href="<?php echo $product_del_route . $product['product_id'] ?>"
                           onclick="<?php echo 'return confirm(\'Are you sure you want to delete this product?\')' ?>">
                            <i class="btn-delete fa fa-trash"></i></a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php } else { ?>
        <p>No products found.</p>
    <?php } ?>
</div>

