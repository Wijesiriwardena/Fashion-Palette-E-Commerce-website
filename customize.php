<?php
global $pro_dir, $user_id, $back_route, $img_src, $img_placeholder;

$queries = array();
parse_str($_SERVER['QUERY_STRING'], $queries);

// Check product id available in the query params
if (!isset($queries['product_id'])) {
    header('Location: ' . $back_route);
    exit();
}

$product_id = intval($queries['product_id']);

$cart_route = $pro_dir . 'cart';
$products_route = $pro_dir . 'product';

$product_sizes = get_product_sizes();
$color_options = get_color_options();
$sleeve_options = get_sleeve_options();
$fabric_options = get_fabric_options();
$neckline_options = get_neckline_options();

$product_id = intval($queries['product_id']);

fetch_product();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // User needs to be logged in inorder to add customized item to cart.
    if (!is_logged_in()) {
        header('Location: ' . $pro_dir . 'login');
        exit();
    }

    if (isset($_POST['add-to-cart'])) {
        add_to_cart();
    }
}

function fetch_product()
{
    global $data, $product_id, $img_src, $img_placeholder;

    $conn = connect_db();
    $sql = "SELECT * FROM product as p 
        LEFT JOIN gender as g on p.gender_id = g.gender_id
        LEFT JOIN category as c on p.category_id = c.category_id
        LEFT JOIN brand as b on p.brand_id = b.brand_id
        WHERE p.product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);

    if ($stmt->execute()) {
        $data = $stmt->get_result()->fetch_assoc();
        $img_src = isset($data['image']) ? 'data:image/*;base64,' . base64_encode($data['image']) : $img_placeholder;
    } else {
        $_SESSION['form_data'] = ['error' => "Error while retrieving product: " . $stmt->error];
    }

    $stmt->close();
    $conn->close();
}

function add_to_cart()
{
    global $user_id, $product_id, $cart_route;

    $conn = connect_db();

    // Insert product into cart
    $size_id = htmlspecialchars($_POST['size_id']);
    $color_id = htmlspecialchars($_POST['color_id']);
    $neckline_id = htmlspecialchars($_POST['neckline_id']);
    $sleeve_id = htmlspecialchars($_POST['sleeve_id']);
    $fabric_id = htmlspecialchars($_POST['fabric_id']);
    $quantity = 1;

    // Start transaction
    $conn->begin_transaction();
    try {
        // Insert item spec
        $sql = "INSERT INTO item_spec (color_id, size_id, sleeve_id, neckline_id, fabric_id) VALUES (?, ?, ?, ?, ?)";
        $stmt_item_spec = $conn->prepare($sql);
        $stmt_item_spec->bind_param("iiiii", $color_id, $size_id, $sleeve_id, $neckline_id, $fabric_id);

        if (!$stmt_item_spec->execute()) {
            throw new Exception("Item spec insert failed: " . $stmt_item_spec->error);
        }

        // Get the contact ID of the inserted contact (for both shipping and billing, assuming they are the same)
        $item_spec_id = $stmt_item_spec->insert_id;

        // Insert product into cart
        $sql = "INSERT INTO cart_item (user_id, product_id, item_spec_id, quantity) VALUES (?, ?, ?, ?)";
        $stmt_cart = $conn->prepare($sql);
        $stmt_cart->bind_param("iiii", $user_id, $product_id, $item_spec_id, $quantity);

        if (!$stmt_cart->execute()) {
            throw new Exception("Error while adding item to cart: " . $stmt_item_spec->error);
        }

        $stmt_item_spec->close();
        // Commit the transaction
        $conn->commit();
        $conn->close();

        // Redirect to cart page
        $_SESSION["form_data"] = ['message' => 'Product added to cart successfully'];
        header('Location: ' . $cart_route);
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION["form_data"] = [
            "error" => $e->getMessage(),
            "size_id" => $_POST["size_id"],
            "color_id" => $_POST["color_id"]
        ];
        // Redirect back to the form page
        header("Location: " . $_SERVER["REQUEST_URI"]);
        // Close the database connection
        $conn->close();
        exit();
    }
}

if (isset($data)) {
    $out_of_stock = $data['stock'] <= 0; ?>
    <div class="container custom-container custom-container-lg p-5">
        <div class="row">
            <div class="col-lg-7 col-md-12 img-container">
                <img alt="" src="<?php echo $img_src ?>" style="max-height: 580px">
            </div>
            <div class="col-lg-5 col-md-12">
                <form class="custom-form" action="" method="POST">
                    <div class="product-brand text-content text-center pt-2 mb-2">
                        CUSTOMIZE YOUR PRODUCT
                    </div>
                    <div class="form-title product-title text-content">
                        <?php echo $data['product_name'] ?>
                    </div>
                    <div class="product-brand text-content text-center mb-3">
                        <?php echo $data['gender_name'] . ' | ' . $data['brand_name'] ?>
                    </div>
                    <div>
                        <div class="product-price text-content mb-2">
                            <?php echo 'Price $' . $data['price'] ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-6 col-md-12 form-group required pr-1">
                            <div class="input-wrapper">
                                <label for="color_id" class="form-label control-label">Color</label>
                                <select class="custom-select" id="color_id" name="color_id">
                                    <?php foreach ($color_options as $color): ?>
                                        <option value="<?php echo $color['color_id'] ?>">
                                            <?php echo $color['color_name'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-12 form-group required pl-1">
                            <div class="input-wrapper">
                                <label for="size_id" class="form-label control-label">Size</label>
                                <select class="custom-select" id="size_id" name="size_id">
                                    <?php foreach ($product_sizes as $size): ?>
                                        <option value="<?php echo $size['size_id'] ?>">
                                            <?php echo $size['size_name'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group required pr-1">
                        <div class="input-wrapper">
                            <label for="neckline_id" class="form-label control-label">Neckline</label>
                            <select class="custom-select" id="neckline_id" name="neckline_id">
                                <?php foreach ($neckline_options as $neckline): ?>
                                    <option value="<?php echo $neckline['neckline_id'] ?>">
                                        <?php echo $neckline['neckline_name'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group required pr-1">
                        <div class="input-wrapper">
                            <label for="sleeve_id" class="form-label control-label">Sleeves</label>
                            <select class="custom-select" id="sleeve_id" name="sleeve_id">
                                <?php foreach ($sleeve_options as $sleeve): ?>
                                    <option value="<?php echo $sleeve['sleeve_id'] ?>">
                                        <?php echo $sleeve['sleeve_name'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group required pr-1">
                        <div class="input-wrapper">
                            <label for="fabric_id" class="form-label control-label">Fabric</label>
                            <select class="custom-select" id="fabric_id" name="fabric_id">
                                <?php foreach ($fabric_options as $fabric): ?>
                                    <option value="<?php echo $fabric['fabric_id'] ?>">
                                        <?php echo $fabric['fabric_name'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="d-flex w-100 mb-2">
                        <button style="width: 100%" type="submit" name="add-to-cart"
                                class="btn btn-sm btn-custom-secondary my-2"
                                onclose=""
                            <?php if ($out_of_stock) {
                                echo 'disabled';
                            } ?>>
                            Add to cart
                        </button>
                    </div>
                </form>
                <div class="form-link text-content my-3">
                    <a class="form-link" href="<?php echo $products_route ?>">Back to products</a>
                </div>
            </div>
        </div>
    </div>
<?php } else {
    include_once __DIR__ . '/../includes/not-found.php';
}
