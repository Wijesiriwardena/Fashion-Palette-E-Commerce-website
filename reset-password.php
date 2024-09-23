<?php
$form_data = get_form_data();

// set email from query params if available
$queries = array();
parse_str($_SERVER['QUERY_STRING'], $queries);

if (isset($queries['email'])) {
    $form_data["email"] = $queries['email'];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Check if passwords match
    if ($_POST['email'] !== $_POST['confirm_password']) {
        $error = "Passwords do not match.";
    } else {
        $conn = connect_db();
        // Sanitize inputs
        $email = htmlspecialchars(trim($_POST['email']));
        $password = htmlspecialchars(trim($_POST['password']));
        $verificationCode = htmlspecialchars(trim($_POST['verification_code']));

        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("UPDATE user SET password = ? WHERE email = ?");
        $stmt->bind_param("ss", $hashed_password, $email);

        if ($stmt->execute()) {
            // Clear the form data from session when navigate away
            clear_form_data();
            // Redirect to login page
            header("Location: login");
        } else {
            $error = "Error while register: " . $stmt->error;
        }

        if (isset($error)) {
            // Save form data to session
            $_SESSION["form_data"] = [
                "error" => $error,
                "email" => $_POST["email"],
                "verification_code" => $_POST["verification_code"],
            ];
            // Redirect back
            header("Location: " . $_SERVER["REQUEST_URI"]);
        }

        $stmt->close();
        $conn->close();
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
            <div class="form-title text-content">Reset Password</div>
            <div class="form-link text-content my-2">
                <a class="form-link" onclick="location='register'">Resend verification code</a>
            </div>
            <?php if (isset($form_data['error'])): ?>
                <div class="alert alert-danger alert-dismissible">
                    <?php echo $form_data['error']; ?>
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
            <div class="form-group py-1">
                <div class="input-wrapper">
                    <input id="verification_code" name="verification_code" type="text" class="form-control with-icon"
                           placeholder="Verification Code" required
                           value="<?php echo get_form_val('verification_code'); ?>">
                    <i class="input-icon fa fa-code" aria-hidden="true"></i>
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
            <button type="submit" class="btn btn-custom-primary mt-2">Reset Password</button>
            <div class="form-link text-content my-3">
                <a class="form-link" onclick="location='login'">Back to Sign in</a>
            </div>
        </form>
    </div>
</div>


