<?php
global $pro_dir, $user_id;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // User needs to be logged in inorder to add item to cart or favourite.
    if (!is_logged_in()) {
        header('Location: ' . $pro_dir . 'login');
        exit();
    }

    if (isset($_POST['add-to-cart'])) {
        add_to_cart();
    }

    if (isset($_POST['add-to-favourite'])) {
        add_to_favourite();
    }

    if (isset($_POST['remove-from-favourite'])) {
        remove_from_favourite();
    }
}

function add_to_favourite()
{
    global $user_id;

    $conn = connect_db();
    $product_id = htmlspecialchars($_POST['product_id']);

    // Insert product into favourite
    $sql = "SELECT user_id, product_id FROM `favourite` WHERE user_id = ? AND product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $product_id);

    if (!$stmt->execute()) {
        $_SESSION["form_data"] = ['message' => 'Product added to favourites successfully'];
        $stmt->close();
        $conn->close();

        // Redirect back
        header("Location: " . $_SERVER["REQUEST_URI"]);
        exit();
    }

    $data = $stmt->get_result()->fetch_assoc();
    if ($data) {
        $_SESSION["form_data"] = ['warn' => 'Product already available in favourites'];
        $stmt->close();
        $conn->close();

        // Redirect back
        header("Location: " . $_SERVER["REQUEST_URI"]);
        exit();
    }

    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("INSERT INTO `favourite` (user_id, product_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $user_id, $product_id);

        if (!$stmt->execute()) {
            throw new Exception("Error while adding product to favourites: " . $stmt->error);
        }

        $stmt = $conn->prepare("UPDATE `product` SET favourite_count = favourite_count + 1 WHERE product_id = ?");
        $stmt->bind_param("i", $product_id);

        if (!$stmt->execute()) {
            throw new Exception("Error while updating product favourite count: " . $stmt->error);
        }

        // Commit the transaction
        $conn->commit();
        $_SESSION["form_data"] = ["message" => 'Item added to favourites successfully'];
    } catch (Exception $e) {
        $_SESSION["form_data"] = ["error" => $e->getMessage()];
    }

    $stmt->close();
    $conn->close();

    // Redirect back
    header("Location: " . $_SERVER["REQUEST_URI"]);
    exit();
}

function remove_from_favourite()
{
    global $user_id;

    $conn = connect_db();
    $product_id = htmlspecialchars($_POST['product_id']);

    $conn->begin_transaction();
    try {
        // Remove product from favourite
        $stmt = $conn->prepare("DELETE FROM `favourite` WHERE  user_id = ? AND product_id = ?");
        $stmt->bind_param("ii", $user_id, $product_id);

        if (!$stmt->execute()) {
            throw new Exception("Error while removing item from favourites: " . $stmt->error);
        }

        $stmt = $conn->prepare("UPDATE `product` SET favourite_count = favourite_count - 1 WHERE product_id = ?");
        $stmt->bind_param("i", $product_id);

        if (!$stmt->execute()) {
            throw new Exception("Error while updating product's favourite count: " . $stmt->error);
        }

        $conn->commit();
        $_SESSION["form_data"] = ['message' => 'Item removed from favourites successfully'];
    } catch (Exception $e) {
        $_SESSION["form_data"] = ["error" => $e->getMessage()];
    }

    $stmt->close();
    $conn->close();

    // Redirect back
    header("Location: " . $_SERVER["REQUEST_URI"]);
    exit();
}

function add_to_cart()
{
    global $user_id;

    $conn = connect_db();
    $product_id = htmlspecialchars($_POST['product_id']);
    $quantity = 1;
    $color_id = 1;
    $size_id = 1;

    // Start transaction
    $conn->begin_transaction();
    try {
        // Insert item spec
        $sql = "INSERT INTO item_spec (color_id, size_id) VALUES (?, ?)";
        $stmt_item_spec = $conn->prepare($sql);
        $stmt_item_spec->bind_param("ii", $color_id, $size_id);

        if (!$stmt_item_spec->execute()) {
            throw new Exception("Item spec insert failed: " . $stmt_item_spec->error);
        }

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
        header("Location: " . $_SERVER["REQUEST_URI"]);
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

function product_card_view($data)
{
    global $pro_dir, $img_placeholder;
    $product_dir = $pro_dir . 'product?product_id=';
    $img_src = isset($data['image']) ? 'data:image/*;base64,' . base64_encode($data['image']) : $img_placeholder;
    $favourite_count = isset($data["favourite_count"]) && $data["favourite_count"] > 0 ?
        $data["favourite_count"] : 0;

    ob_start(); ?>
    <html lang="en">
    <body>
    <div class="card mb-2" style="border: none">
        <div class="image-card-container">
            <img alt="" class="card-image" src="<?php echo $img_src ?>"
                 onclick="location='<?php echo $product_dir . $data["product_id"] ?>'"/>
            <div class="image-card-favourite-container">
                <span class="badge favourite-badge">
                    <i class="fa-solid fa-heart"></i><?php echo $favourite_count ?>
                </span>
            </div>
            <div class="image-card-buttons-container">
                <form action="" method="POST">
                    <input name="product_id" value="<?php echo $data["product_id"] ?>" hidden>
                    <button id="add-to-favourite" name="add-to-favourite"
                            class="btn btn-sm image-card-button" type="submit">
                        <i class="fa-solid fa-heart"></i>
                    </button>
                    <button id="add-to-cart" name="add-to-cart"
                            class="btn btn-sm image-card-button" type="submit">
                        <i class="fa-solid fa-cart-shopping"></i>
                    </button>
                </form>
            </div>
        </div>
        <div class="card-body px-0">
            <p class="card-title" style="font-size: 14px; font-weight: 600"><?php echo $data["product_name"] ?></p>
            <div class="product-flex-row">
                <div class="product-price-sm text-content">$<?php echo $data['price'] ?></div>
                <div class="text-content" style="font-size: 14px"><?php echo $data['brand'] ?></div>
            </div>
        </div>
    </div>
    </html>
    <?php
    return ob_get_clean();
}

function product_cart_view($data)
{
    global $pro_dir, $img_placeholder;
    $cart_dir = $pro_dir . 'cart?cart_item_id=';
    $product_dir = $pro_dir . 'product?product_id=';
    $img_src = isset($data['image']) ? 'data:image/*;base64,' . base64_encode($data['image']) : $img_placeholder;

    ob_start(); ?>
    <html lang="en">
    <body>
    <div class="card" style="border: none; width: 100%">
        <div class="card-horizontal">
            <img alt="" class="card-image card-image-small" src="<?php echo $img_src ?>"
                 onclick="location='<?php echo $product_dir . $data["product_id"] ?>'"/>
            <div class="card-body py-0">
                <p class="card-title title-tag mb-1"><?php echo $data["product_name"] ?></p>
                <?php if (isset($data["neckline_name"])): ?>
                    <p class="customize-info">NECKLINE: <?php echo $data["neckline_name"] ?></p>
                <?php endif;
                if (isset($data["sleeve_name"])): ?>
                    <p class="customize-info">SLEEVE: <?php echo $data["sleeve_name"] ?></p>
                <?php endif;
                if (isset($data["fabric_name"])): ?>
                    <p class="customize-info">FABRIC: <?php echo $data["fabric_name"] ?></p>
                <?php endif; ?>
                <a href="<?php echo $cart_dir . $data['cart_item_id'] ?>" class="remove-link"
                   onclick="confirm('Are you sure you want to delete this item from shopping cart?')">
                    remove
                </a>
            </div>
        </div>
    </div>
    </html>
    <?php
    return ob_get_clean();
}

function product_favourite_view($data)
{
    global $pro_dir, $img_placeholder;
    $product_dir = $pro_dir . 'product?product_id=';
    $img_src = isset($data['image']) ? 'data:image/*;base64,' . base64_encode($data['image']) : $img_placeholder;

    ob_start(); ?>
    <html lang="en">
    <body>
    <div class="card mb-2" style="border: none">
        <div class="image-card-container">
            <img alt="" class="card-image" src="<?php echo $img_src ?>"
                 onclick="location='<?php echo $product_dir . $data["product_id"] ?>'"/>
            <div class="image-card-buttons-container">
                <form action="" method="POST"
                      onsubmit="return confirm('Do you really want to remove this item from favourites?');">
                    <input name="product_id" value="<?php echo $data["product_id"] ?>" hidden>
                    <button id="remove-from-favourite" name="remove-from-favourite"
                            class="btn btn-sm image-card-button" type="submit">
                        <i class="fa-solid fa-heart"></i>
                    </button>
                    <button id="add-to-cart" name="add-to-cart"
                            class="btn btn-sm image-card-button" type="submit">
                        <i class="fa-solid fa-cart-shopping"></i>
                    </button>
                </form>
            </div>
        </div>
        <div class="card-body px-0">
            <p class="card-title" style="font-size: 14px; font-weight: 600"><?php echo $data["product_name"] ?></p>
            <div class="product-flex-row">
                <div class="product-price-sm text-content">$<?php echo $data['price'] ?></div>
                <div class="text-content" style="font-size: 14px">'<?php echo $data['brand'] ?></div>
            </div>
        </div>
    </div>
    </html>
    <?php
    return ob_get_clean();
}

function order_card_view($data)
{
    global $pro_dir, $img_placeholder;
    $my_orders_dir = $pro_dir . 'my/orders?order_id=';
    $product_dir = $pro_dir . 'product?product_id=';
    $img_src = isset($data['image']) ? 'data:image/*;base64,' . base64_encode($data['image']) : $img_placeholder;

    ob_start(); ?>
    <html lang="en">
    <body>
    <div class="card mb-4" style="border: none; width: 100%">
        <div class="card-horizontal">
            <img alt="" class="card-image card-image-small" src="<?php echo $img_src ?>"
                 onclick="location='<?php echo $product_dir . $data["product_id"] ?>'"/>
            <div class="card-body py-0">
                <p class="card-title title-tag"><?php echo $data["product_name"] ?></p>
                <p class="size-tag mb-1">SIZE: <?php echo strtoupper($data['size_code']) ?></p>
                <p style="font-size: 12px" class="mb-1">Order Number: <?php echo strtoupper($data['order_id']) ?></p>
                <?php if ($data["status"] == 'Pending') : ?>
                    <a class="remove-link" href="<?php echo $my_orders_dir . $data['order_id'] ?>"
                       onclick="confirm('Are you sure you want to cancel this order?')">Cancel order</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    </html>
    <?php
    return ob_get_clean();
}


