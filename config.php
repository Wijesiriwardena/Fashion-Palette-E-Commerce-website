<?php
$appName = "Fashion Palette";
$pro_dir = "/fashionpalette/";
$page_dir = $pro_dir . "pages/";
$includes_dir = $pro_dir . "includes/";
$css_dir = $pro_dir . "assets/css/";
$img_placeholder = 'https://placehold.co/400x600?text=FASHION\nPALETTE&font=roboto';
$back_route = "javascript:history.go(-1)";
$user_id = is_logged_in() ? intval($_SESSION['user']['user_id']) : null;

$gender = [
    'men' => 1,
    'women' => 2,
    'kids' => 3
];

$category = [
    'shoes' => 1,
    'bags' => 2,
    'tops' => 3,
    'pants' => 4,
    'dressing' => 5,
    'outerwear' => 6,
    'accessories' => 7,
    'sportswear' => 8
];

$order_status = [
    'Pending', 'Shipped', 'Delivered', 'Completed', 'Cancelled', 'Declined', 'Refunded'
];

function connect_db()
{
    $DB_HOSTNAME = 'localhost';
    $DB_PORT = 3306;
    $DB_USERNAME = 'root';
    $DB_PASSWORD = '';
    $DB_NAME = 'clothing_store';

    static $conn;
    if (!is_resource($conn)) {
        $conn = mysqli_connect($DB_HOSTNAME, $DB_USERNAME, $DB_PASSWORD, $DB_NAME, $DB_PORT)
        or die('connection failed');
    }
    return $conn;
}

function is_logged_in()
{
    return isset($_SESSION['user']['user_id']);
}

function is_admin()
{
    return isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin';
}

function is_supplier()
{
    return isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'supplier';
}

function clear_form_data()
{
    unset($_SESSION["form_data"]);
}

function get_gender_options()
{
    $conn = connect_db();
    $sql = "SELECT * FROM gender";
    $stmt = $conn->prepare($sql);
    if ($stmt->execute()) {
        $genders = $stmt->get_result();
    } else {
        $_SESSION['form_data'] = ['error' => 'Error while retrieving genders: ' . $stmt->error];
        header("Location: " . $_SERVER["REQUEST_URI"]);
        exit();
    }
    $stmt->close();
    $conn->close();
    return $genders;
}

function get_category_options()
{
    $conn = connect_db();
    $sql = "SELECT category_id, category_name FROM category";
    $stmt = $conn->prepare($sql);
    if ($stmt->execute()) {
        $categories = $stmt->get_result();
    } else {
        $_SESSION['form_data'] = ['error' => 'Error while retrieving categories: ' . $stmt->error];
        header("Location: " . $_SERVER["REQUEST_URI"]);
        exit();
    }
    $stmt->close();
    $conn->close();
    return $categories;
}

function get_brand_options()
{
    $conn = connect_db();
    $sql = "SELECT brand_id, brand_name FROM brand";
    $stmt = $conn->prepare($sql);

    if ($stmt->execute()) {
        $brands = $stmt->get_result();
    } else {
        $_SESSION['form_data'] = ['error' => 'Error while retrieving brands: ' . $stmt->error];
        header("Location: " . $_SERVER["REQUEST_URI"]);
        exit();
    }
    $stmt->close();
    $conn->close();
    return $brands;
}

function get_product_sizes()
{
    $conn = connect_db();
    $sql = "SELECT * FROM size";
    $stmt = $conn->prepare($sql);
    if ($stmt->execute()) {
        $size_options = $stmt->get_result();
    } else {
        $_SESSION['form_data'] = ['error' => 'Error while retrieving product sizes: ' . $stmt->error];
        header("Location: " . $_SERVER["REQUEST_URI"]);
        exit();
    }
    $stmt->close();
    $conn->close();
    return $size_options;
}

function get_neckline_options()
{
    $conn = connect_db();
    $sql = "SELECT * FROM neckline";
    $stmt = $conn->prepare($sql);
    if ($stmt->execute()) {
        $necklines = $stmt->get_result();
    } else {
        $_SESSION['form_data'] = ['error' => 'Error while retrieving necklines: ' . $stmt->error];
        header("Location: " . $_SERVER["REQUEST_URI"]);
        exit();
    }
    $stmt->close();
    $conn->close();
    return $necklines;
}

function get_fabric_options()
{
    $conn = connect_db();
    $sql = "SELECT * FROM fabric";
    $stmt = $conn->prepare($sql);
    if ($stmt->execute()) {
        $fabrics = $stmt->get_result();
    } else {
        $_SESSION['form_data'] = ['error' => 'Error while retrieving fabrics: ' . $stmt->error];
        header("Location: " . $_SERVER["REQUEST_URI"]);
        exit();
    }
    $stmt->close();
    $conn->close();
    return $fabrics;
}

function get_sleeve_options()
{
    $conn = connect_db();
    $sql = "SELECT * FROM sleeve";
    $stmt = $conn->prepare($sql);
    if ($stmt->execute()) {
        $sleeves = $stmt->get_result();
    } else {
        $_SESSION['form_data'] = ['error' => 'Error while retrieving sleeves: ' . $stmt->error];
        header("Location: " . $_SERVER["REQUEST_URI"]);
        exit();
    }
    $stmt->close();
    $conn->close();
    return $sleeves;
}

function get_sort_options()
{
    static $sort_options = [
        'all' => ['value' => 'all', 'name' => 'Latest'],
        'asc' => ['value' => '1', 'name' => 'Price low to high'],
        'desc' => ['value' => '2', 'name' => 'Price high to low']
    ];
    return $sort_options;
}

function get_color_options()
{
    $conn = connect_db();
    $sql = "SELECT * FROM color";
    $stmt = $conn->prepare($sql);
    if ($stmt->execute()) {
        $colors = $stmt->get_result();
    } else {
        $_SESSION['form_data'] = ['error' => 'Error while retrieving colors: ' . $stmt->error];
        header("Location: " . $_SERVER["REQUEST_URI"]);
        exit();
    }
    $stmt->close();
    $conn->close();
    return $colors;
}

function get_skintone_options()
{
    static $color_options = [
        'light' => ['value' => 'light', 'name' => 'Light'],
        'fair' => ['value' => 'fair', 'name' => 'Fair'],
        'medium' => ['value' => 'medium', 'name' => 'Medium'],
        'dark' => ['value' => 'dark', 'name' => 'Dark']
    ];
    return $color_options;
}

function get_price_range_options()
{
    static $price_range_options = [
        'all' => ['value' => 'all', 'name' => 'All Prices'],
        'upto10' => ['value' => '1', 'name' => 'Up to $10'],
        '10to50' => ['value' => '2', 'name' => '$10 - $50'],
        '50to250' => ['value' => '3', 'name' => '$50 - $250'],
        '250to1000' => ['value' => '4', 'name' => '$250 - $1000'],
        'above1000' => ['value' => '5', 'name' => 'Above $1000']
    ];
    return $price_range_options;
}

function get_discount_options()
{
    static $discount_options = [
        'all' => ['value' => 'all', 'name' => 'All Products'],
        'upto10' => ['value' => '1', 'name' => 'Up to 20%'],
        '10to50' => ['value' => '2', 'name' => '20% - 30%'],
        '50to250' => ['value' => '3', 'name' => '30% - 40%'],
        '250to1000' => ['value' => '4', 'name' => '40% - 50%'],
        'above1000' => ['value' => '5', 'name' => 'Above 50%']
    ];
    return $discount_options;
}

function get_form_data()
{
    static $form_data;
    if (isset($_SESSION["form_data"])) {
        $form_data = $_SESSION["form_data"];
        // Clear form data from session
        clear_form_data();
    }
    return $form_data;
}

function get_form_val($field)
{
    global $data, $form_data;
    if (isset($data[$field])) {
        return htmlspecialchars($data[$field]);
    } else if (isset($form_data[$field])) {
        return htmlspecialchars($form_data[$field]);
    } else {
        return null;
    }
}

