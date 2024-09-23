<?php
global $routes, $admin_routes, $protected_routes, $open_routes, $pro_dir;
session_start();

include_once __DIR__ . "/includes/routes.php";
include_once __DIR__ . "/includes/config.php";

$default_route = $pro_dir . 'women';
$form_data = get_form_data();

// logout user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    // Redirect back to the form page
    header("Location: " . $default_route);
    exit();
}

// Get the route from the URL
$route = isset($_GET['route']) ? $_GET['route'] : $default_route;

// valid route. render relevant page
if (array_key_exists($route, $routes)) {

    if (!is_admin() && in_array($route, $admin_routes)) {
        // Non admin user trying to access admin route. redirect to home
        header('Location: ' . $default_route);
        exit();
    }

    if (is_logged_in() && in_array($route, $open_routes)) {
        // user already logged in. redirect to home
        header('Location: ' . $default_route);
        exit();
    }

    $view = $routes[$route];
    $title = $view['title'];
    $css_files = $view['css'] ?? [];

    // Render header
    include_once __DIR__ . "/includes/header.php";

    // Conditionally render navbar
    if (!in_array($route, $open_routes)) {
        include_once __DIR__ . "/includes/navbar.php";

        // Render notification alerts
        include_once __DIR__ . "/includes/alert.php";
    }

    // Render main page content
    include_once $view['page'];

    // Conditionally render footer
    if (!in_array($route, $open_routes)) {
        include_once __DIR__ . "/includes/footer.php";
    }
} else {
    // Redirect to default route
    header('Location: ' . $default_route);
}

