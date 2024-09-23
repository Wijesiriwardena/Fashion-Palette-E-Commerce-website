<?php

// set email from query params if available
$queries = array();
parse_str($_SERVER['QUERY_STRING'], $queries);

if (isset($queries['email'])) {
    $form_data["email"] = $queries['email'];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $conn = connect_db();
    $stmt = $conn->prepare("SELECT * FROM user WHERE email = ?");
    $stmt->bind_param("s", $_POST["email"]);

    if ($stmt->execute()) {
        $data = $stmt->get_result()->fetch_assoc();

        if ($data && password_verify($_POST["password"], $data["password"])) {
            if ($data['disabled']) {
                $error = "User disabled";
            } else {
                // Store user information in the session
                $_SESSION["user"] = [
                    "email" => $data["email"],
                    "user_id" => $data["user_id"],
                    "first_name" => $data["first_name"],
                    "last_name" => $data["last_name"],
                    "role" => $data["role"],
                ];

                if ($_POST["remember"]) {
                    setcookie("email", $_POST["email"], time() + (86400 * 30), "/");
                    setcookie("password", $_POST["password"], time() + (86400 * 30), "/");
                } else {
                    if (isset($_COOKIE["email"])) {
                        setcookie("email", "", time() - 3600, "/");
                    }
                    if (isset($_COOKIE["password"])) {
                        setcookie("password", "", time() - 3600, "/");
                    }
                }

                // Clear the form data from session when navigate away
                clear_form_data();

                // Redirect to home page
                header("Location: home");
            }
        } else {
            $error = "Invalid email or password";
        }
    } else {
        $error = "Error while user login: " . $stmt->error;
    }

    if (isset($error)) {
        $_SESSION["form_data"] = [
            "error" => $error,
            "email" => $_POST["email"],
            "password" => $_POST["password"],
        ];
        // Redirect back
        header("Location: " . $_SERVER["REQUEST_URI"]);
    }

    $stmt->close();
    $conn->close();
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
            <div class="form-title text-content">Sign In to Continue</div>
            <div class="form-link text-content my-2">
                <a class="form-link" onclick="location='register'">Create a new account</a>
            </div>
            <?php if (isset($form_data["error"])): ?>
                <div class="alert alert-danger alert-dismissible">
                    <?php echo $form_data["error"]; ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>
            <div class="form-group py-1">
                <div class="input-wrapper">
                    <input id="email" name="email" type="email" class="form-control with-icon"
                           placeholder="Email" autocomplete="username"
                           value="<?php
                           if (isset($form_data['email'])) {
                               echo htmlspecialchars($form_data['email']);
                           } else if (isset($_COOKIE['email'])) {
                               echo htmlspecialchars($_COOKIE['email']);
                           } else {
                               echo "";
                           } ?>">
                    <i class="input-icon fa fa-user" aria-hidden="true"></i>
                </div>
            </div>
            <div class="form-group py-1">
                <div class="input-wrapper">
                    <input id="password" name="password" type="password" class="form-control with-icon"
                           placeholder="Password" autocomplete="password"
                           value="<?php
                           if (isset($form_data['email'])) {
                               echo htmlspecialchars($form_data['email']);
                           } else if (isset($_COOKIE['password'])) {
                               echo htmlspecialchars($_COOKIE['password']);
                           } else {
                               echo "";
                           } ?>">
                    <i class="input-icon fa fa-lock" aria-hidden="true"></i>
                </div>
            </div>
            <button type="submit" class="btn btn-custom-primary">Log in</button>
            <div class="form-link mt-3 my-1">
                <label>
                    <input type="checkbox" name="remember" class="mr-1"
                        <?php if (isset($_COOKIE['email'])) {
                            echo 'checked';
                        } ?>>Remember me
                </label>
            </div>
            <div class="form-link text-content mb-3">
                <a class="form-link" onclick="location='forgot-password'">Forgot your password?</a>
            </div>
        </form>
    </div>
</div>
