<?php
global $pro_dir, $back_route, $data, $error, $img_src, $img_placeholder, $genders, $categories, $brands;

// If not an admin, redirect to default route
if (!is_admin()) {
    header("Location: " . $back_route);
    exit();
}

// Get product_id from query params
$queries = array();
parse_str($_SERVER['QUERY_STRING'], $queries);

if (!isset($queries['product_id'])) {
    header("Location: " . $back_route);
    exit();
}

$product_list_route = $pro_dir . "admin/product";
$product_id = intval($queries['product_id']);

$genders = get_gender_options();
$categories = get_category_options();
$brands = get_brand_options();

fetch_product();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    update_product();
}

function fetch_product()
{
    global $product_id, $data, $img_src, $img_placeholder;
    $conn = connect_db();

    $sql = "SELECT * FROM product WHERE product_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);

    if ($stmt->execute()) {
        $data = $stmt->get_result()->fetch_assoc();
        $img_src = isset($data['image']) ? 'data:image/*;base64,' . base64_encode($data['image']) : $img_placeholder;
    } else {
        $_SESSION['form_data'] = ['error' => 'Error while retrieving product: ' . $stmt->error];
        header("Location: " . $_SERVER["REQUEST_URI"]);
        exit();
    }

    $stmt->close();
    $conn->close();
}

function update_product()
{
    global $product_id, $error, $data, $product_list_route;
    if (doubleval($_POST['price']) == 0) {
        $error = "Invalid product price. Should be a double";
        save_state();
        header("Location: " . $_SERVER["REQUEST_URI"]);
        exit();
    }

    if (doubleval($_POST['discount']) < 0) {
        $error = "Invalid product discount. Should be a double";
        save_state();
        header("Location: " . $_SERVER["REQUEST_URI"]);
        exit();
    }

    if (intval($_POST['stock']) < 0) {
        $error = "Invalid stock value. Should be a non negative integer value";
        save_state();
        header("Location: " . $_SERVER["REQUEST_URI"]);
        exit();
    }

    $conn = connect_db();

    // Sanitize inputs
    $product_name = htmlspecialchars(trim($_POST['product_name']));
    $description = htmlspecialchars(trim($_POST['description']));
    $skin_tone = htmlspecialchars(trim($_POST['skin_tone']));
    $price = htmlspecialchars(trim($_POST['price']));
    $discount = htmlspecialchars(trim($_POST['discount']));
    $stock = htmlspecialchars(trim($_POST['stock']));
    $gender_id = htmlspecialchars(trim($_POST['gender_id']));
    $category_id = htmlspecialchars(trim($_POST['category_id']));
    $brand_id = htmlspecialchars(trim($_POST['brand_id']));

    if (isset($_FILES["product-image"]) && $_FILES["product-image"]["error"] == 0) {
        $image = file_get_contents($_FILES['product-image']['tmp_name']);
    } else {
        $image = $data['image'];
    }

    $sql = "UPDATE product SET 
                   product_name = ?, 
                   description = ?, 
                   skin_tone = ?,
                   price = ?, 
                   discount = ?, 
                   stock = ?, 
                   gender_id = ?, 
                   category_id = ?, 
                   brand_id = ?, 
                   image = ? 
               WHERE product_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssddiiiisi", $product_name, $description, $skin_tone, $price, $discount,
        $stock, $gender_id, $category_id, $brand_id, $image, $product_id);

    if ($stmt->execute()) {
        // Clear the form data from session when navigate away
        clear_form_data();

        $stmt->close();
        $conn->close();

        // Redirect to product page
        $_SESSION["form_data"] = ['message' => 'Product updated successfully'];
        header('Location: ' . $product_list_route);
        exit();
    } else {
        $error = "Error while updating product: " . $stmt->error;
    }

    save_state();

    $stmt->close();
    $conn->close();

    // Redirect back to same page
    header("Location: " . $_SERVER["REQUEST_URI"]);
}

function save_state()
{
    global $error;
    $_SESSION["form_data"] = [
        "error" => $error,
        "name" => $_POST["name"],
        "color_id" => $_POST["color_id"],
        "description" => $_POST["description"],
        "discount" => $_POST["discount"],
        "price" => $_POST["price"],
        "stock" => $_POST["stock"],
        "gender_id" => $_POST["gender_id"],
        "category_id" => $_POST["category_id"],
        "brand_id" => $_POST["brand_id"]
    ];
}

?>

<div class="container custom-container custom-container-xl p-5">
    <form class="custom-form" action="" method="POST" enctype="multipart/form-data">
        <div class="row">
            <div class="col-lg-5 col-md-12 img-container">
                <img alt="" src="<?php echo $img_src ?>">
            </div>
            <div class="col-lg-7 col-md-12 pl-4">
                <div class="form-title text-content mb-3">UPDATE PRODUCT</div>
                <div class="form-group required pr-1">
                    <div class="input-wrapper">
                        <label for="product_name" class="form-label control-label">Product Name</label>
                        <input id="product_name" name="product_name" type="text" class="form-control"
                               placeholder="Product Name" required
                               value="<?php echo get_form_val('product_name') ?>">
                    </div>
                </div>
                <div class="row">
                    <div class="col form-group required pr-1">
                        <div class="input-wrapper">
                            <label for="gender_id" class="form-label control-label">Product For</label>
                            <select class="custom-select" id="gender_id" name="gender_id">
                                <?php foreach ($genders->fetch_all(MYSQLI_ASSOC) as $gender) : ?>
                                    <option value="<?php echo $gender['gender_id'] ?>"
                                        <?php if (isset($data['gender_id']) &&
                                            $gender['gender_id'] == $data['gender_id']) {
                                            echo 'selected';
                                        } ?>>
                                        <?php echo $gender['gender_name'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col form-group required px-1">
                        <div class="input-wrapper">
                            <label for="category_id" class="form-label control-label">Product Category</label>
                            <select class="custom-select" id="category_id" name="category_id">
                                <?php foreach ($categories->fetch_all(MYSQLI_ASSOC) as $category) : ?>
                                    <option value="<?php echo $category['category_id'] ?>"
                                        <?php if (isset($data['category_id']) &&
                                            $category['category_id'] == $data['category_id']) {
                                            echo 'selected';
                                        } ?>>
                                        <?php echo $category['category_name'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col form-group required pl-1">
                        <div class="input-wrapper">
                            <label for="brand_id" class="form-label control-label">Product Brand</label>
                            <select class="custom-select" id="brand_id" name="brand_id">
                                <?php foreach ($brands->fetch_all(MYSQLI_ASSOC) as $brand) : ?>
                                    <option value="<?php echo $brand['brand_id'] ?>"
                                        <?php if (isset($data['brand_id']) &&
                                            $brand['brand_id'] == $data['brand_id']) {
                                            echo 'selected';
                                        } ?>>
                                        <?php echo $brand['brand_name'] ?>
                                    </option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-group py-1">
                    <div class="input-wrapper">
                        <label for="description" class="form-label">Product Description</label>
                        <textarea id="description" name="description" class="form-control" style="height: 100px"
                                  placeholder="Product Description"><?php echo get_form_val('description') ?></textarea>
                    </div>
                </div>
                <div class="row">
                    <div class="col form-group required pr-1">
                        <div class="input-wrapper">
                            <label for="price" class="form-label control-label">Product Price</label>
                            <div class="input-group mb-2">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">$</div>
                                </div>
                                <input id="price" name="price" type="text" class="form-control"
                                       placeholder="Product Price" required
                                       value="<?php echo get_form_val('price') ?>">
                            </div>
                        </div>
                    </div>
                    <div class="col form-group px-1">
                        <div class="input-wrapper">
                            <label for="discount" class="form-label control-label">Product Discount</label>
                            <div class="input-group mb-2">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">%</div>
                                </div>
                                <input id="discount" name="discount" type="text" class="form-control"
                                       placeholder="Product Discount" required
                                       value="<?php echo get_form_val('discount') ?>">
                            </div>
                        </div>
                    </div>
                    <div class="col form-group required pl-1">
                        <div class="input-wrapper">
                            <label for="stock" class="form-label control-label">Product Stock</label>
                            <input id="stock" name="stock" type="number" min="0" class="form-control"
                                   placeholder="Product Stock" required
                                   value="<?php echo get_form_val('stock') ?>">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-8 form-group required pr-1">
                        <div class="input-wrapper">
                            <label for="product-image" class="form-label control-label">Product Image</label>
                            <input id="product-image" name="product-image" type="file" class="form-control"
                                   accept="image/*">
                        </div>
                    </div>
                    <div class="col-4 form-group required pl-1">
                        <div class="input-wrapper">
                            <label for="skin_tone" class="form-label control-label">Skin Tone</label>
                            <select class="custom-select" id="skin_tone" name="skin_tone">
                                <?php foreach (get_skintone_options() as $skin_tone_option) : ?>
                                    <option value="<?php echo $skin_tone_option['value'] ?>"
                                        <?php $val = get_form_val('skin_tone');
                                        if (isset($val) && $skin_tone_option['value'] == $val) {
                                            echo 'selected';
                                        } ?>>
                                        <?php echo $skin_tone_option['name'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <button style="width: 100%" type="submit" class="btn btn-custom-primary mt-2">
                    UPDATE
                </button>
                <div class="form-link text-content my-3">
                    <a class="form-link" onclick="location='<?php echo $product_list_route ?>'">Back to products</a>
                </div>
            </div>
        </div>
    </form>
</div>


