<?php
global $appName, $title, $css_dir, $route, $open_routes;
ob_start();
?>
<!-- header.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $appName . ' | ' . $title ?></title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <!-- Global CSS -->
    <link rel="stylesheet" href="<?php echo $css_dir ?>style.css">
    <?php if (!in_array($route, $open_routes)) { ?>
        <!-- Navbar/Footer CSS -->
        <link rel="stylesheet" href="<?php echo $css_dir ?>navbar.css">
        <link rel="stylesheet" href="<?php echo $css_dir ?>footer.css">
    <?php }
    if (isset($css_files)) :
        foreach ($css_files as $css_file): ?>
            <!-- Page-specific CSS -->
            <link rel="stylesheet" href="<?php echo $css_dir . $css_file ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
