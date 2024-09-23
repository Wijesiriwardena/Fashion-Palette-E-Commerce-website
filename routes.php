<?php
// Routes without navbar and footer
$open_routes = [
    'login', 'register', 'reset-password', 'forgot-password'
];
// Routes required authentication
$protected_routes = [
    'cart',
];
// Routes required admin permission
$admin_routes = [
    'admin/user',
    'admin/user/create',
    'admin/user/update',
    'admin/product',
    'admin/product/update',
    'admin/product/update',
];
// Define routes and their corresponding files, titles and css
$routes = [
    'login' => [
        'page' => 'pages/auth/login.php',
        'title' => 'Sign In',
        'css' => ['auth.css']
    ],
    'register' => [
        'page' => 'pages/auth/register.php',
        'title' => 'Create Account',
        'css' => ['auth.css']
    ],
    'reset-password' => [
        'page' => 'pages/auth/reset-password.php',
        'title' => 'Reset Password',
        'css' => ['auth.css']
    ],
    'forgot-password' => [
        'page' => 'pages/auth/forgot-password.php',
        'title' => 'Forgot Password',
        'css' => ['auth.css']
    ],
    'admin/user' => [
        'page' => 'pages/admin/users.php',
        'title' => 'Users',
    ],
    'admin/user/create' => [
        'page' => 'pages/admin/add-user.php',
        'title' => 'Add User',
    ],
    'admin/user/update' => [
        'page' => 'pages/admin/update-user.php',
        'title' => 'Update User',
    ],
    'admin/product' => [
        'page' => 'pages/admin/products.php',
        'title' => 'Products',
    ],
    'admin/product/create' => [
        'page' => 'pages/admin/add-product.php',
        'title' => 'Add Product',
    ],
    'admin/product/update' => [
        'page' => 'pages/admin/update-product.php',
        'title' => 'Add Product',
    ],
    'admin/order' => [
        'page' => 'pages/admin/orders.php',
        'title' => 'Orders',
    ],
    'admin/order/items' => [
        'page' => 'pages/admin/order-items.php',
        'title' => 'Order Items',
    ],
    'admin/promotion' => [
        'page' => 'pages/admin/promotions.php',
        'title' => 'Promotions',
    ],
    'admin/promotion/create' => [
        'page' => 'pages/admin/add-promotion.php',
        'title' => 'Add Promotion',
    ],
    '' => [
        'page' => 'pages/home.php',
        'title' => 'Home',
        'css' => ['home.css']
    ],
    'women' => [
        'page' => 'pages/home.php',
        'title' => 'Home',
        'css' => ['home.css']
    ],
    'men' => [
        'page' => 'pages/home.php',
        'title' => 'Home',
        'css' => ['home.css']
    ],
    'kids' => [
        'page' => 'pages/home.php',
        'title' => 'Home',
        'css' => ['home.css']
    ],
    'product' => [
        'page' => 'pages/product.php',
        'title' => 'Product',
        'css' => ['product.css']
    ],
    'product/customize' => [
        'page' => 'pages/customize.php',
        'title' => 'Product Customize',
        'css' => ['product.css']
    ],
    'men/product' => [
        'page' => 'pages/products.php',
        'title' => 'Product',
        'css' => ['product.css']
    ],
    'women/product' => [
        'page' => 'pages/products.php',
        'title' => 'Product',
        'css' => ['product.css']
    ],
    'kids/product' => [
        'page' => 'pages/products.php',
        'title' => 'Product',
        'css' => ['product.css']
    ],
    'cart' => [
        'page' => 'pages/cart.php',
        'title' => 'Cart',
        'css' => ['cart.css', 'product.css']
    ],
    'checkout' => [
        'page' => 'pages/checkout.php',
        'title' => 'Checkout',
        'css' => ['product.css', 'checkout.css']
    ],
    'my/orders' => [
        'page' => 'pages/my-orders.php',
        'title' => 'My Orders',
        'css' => ['cart.css', 'product.css']
    ],
    'my/profile' => [
        'page' => 'pages/my-profile.php',
        'title' => 'My Profile',
        'css' => ['profile.css']
    ],
    'favourite' => [
        'page' => 'pages/favourite.php',
        'title' => 'My Favourites',
        'css' => ['cart.css', 'product.css']
    ]
];
