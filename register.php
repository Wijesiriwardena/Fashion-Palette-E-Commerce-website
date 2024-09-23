<?php

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Check if passwords match
    if ($_POST['password'] === $_POST['confirm_password']) {
        $conn = connect_db();

        // Sanitize inputs
        $email = htmlspecialchars(trim($_POST['email']));
        $first_name = htmlspecialchars(trim($_POST['first_name']));
        $last_name = htmlspecialchars(trim($_POST['last_name']));

        // Hash password
        $hashed_password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);

        // Insert user into the database
        $sql = "INSERT INTO user (email, password, first_name, last_name) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $email, $hashed_password, $first_name, $last_name);

        if ($stmt->execute()) {
            // Clear the form data from session when navigate away
            clear_form_data();

            // Redirect to login page
            header('Location: login?email=' . $email);
        } else {
            $error = "Unexpected processing error";
        }

        $stmt->close();
        $conn->close();
    } else {
        $error = "Passwords do not match";
    }

    if (isset($error)) {
        $_SESSION["form_data"] = [
            "error" => $error,
            "email" => $_POST["email"],
            "role" => $_POST["role"],
            "first_name" => $_POST["first_name"],
            "last_name" => $_POST["last_name"],
            "password" => $_POST["password"],
            "confirm_password" => $_POST["confirm_password"],
        ];

        // Redirect back to the form page
        header("Location: " . $_SERVER["REQUEST_URI"]);
    }
}
?>

<div class="auth-container">
    <div class="auth-inner-container">
        <div class="brand-name">
            <span>FASHION PALETTE</span>
        </div>
        <div class="brand-icon">
            <i class="fa fa-palette"></i>
        </div>
        <form class="auth-form" action="" method="POST">
            <div class="form-title text-content mb-3">Create Account</div>
            <?php if (isset($form_data["error"])): ?>
                <div id="alert" class="alert alert-danger alert-dismissible">
                    <?php echo $form_data["error"]; ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>
            <div class="form-group py-1">
                <div class="input-wrapper">
                    <input id="email" name="email" type="email" class="form-control with-icon"
                           placeholder="Email" autocomplete="username" required
                           value="<?php echo get_form_val('email'); ?>">
                    <i class="input-icon fa fa-user" aria-hidden="true"></i>
                </div>
            </div>
            <div class="row">
                <div class="col form-group pr-1">
                    <div class="input-wrapper">
                        <input id="first_name" name="first_name" type="text"
                               class="form-control with-icon" placeholder="First Name" required
                               value="<?php echo get_form_val('firat_name'); ?>">
                        <i class="input-icon fa fa-address-book" aria-hidden="true"></i>
                    </div>
                </div>
                <div class="col form-group pl-1">
                    <div class="input-wrapper">
                        <input id="last_name" name="last_name" type="text"
                               class="form-control with-icon" placeholder="Last Name" required
                               value="<?php echo get_form_val('last_name'); ?>">
                        <i class="input-icon fa fa-address-book" aria-hidden="true"></i>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col form-group pr-1">
                    <div class="input-wrapper">
                        <input id="password" name="password" type="password" class="form-control with-icon"
                               placeholder="Password" autocomplete="new-password" required
                               value="<?php echo get_form_val('password'); ?>">
                        <i class="input-icon fa fa-lock" aria-hidden="true"></i>
                    </div>
                </div>
                <div class="col form-group pl-1">
                    <div class="input-wrapper">
                        <input id="confirm_password" name="confirm_password" type="password"
                               class="form-control with-icon" placeholder="Confirm Password"
                               autocomplete="new-password" required
                               value="<?php echo get_form_val('confirm_password'); ?>">
                        <i class="input-icon fa fa-lock" aria-hidden="true"></i>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-custom-primary mt-2">Sign Up</button>
            <div class="form-link text-content my-3">
                <a class="form-link" onclick="location='login'">Already have an account? Sign in</a>
            </div>
        </form>
    </div>
</div>

