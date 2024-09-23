<?php
global $pro_dir, $back_route;


// If not an admin, redirect to default route
if (!is_admin()) {
    header("Location: " . $back_route);
    exit();
}

$user_list_route = $pro_dir . 'admin/user';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    add_user();
}

function add_user()
{
    global $promotion_list_route;
    $conn = connect_db();
    $email = htmlspecialchars(trim($_POST['email']));

    // Validate email
    $stmt = $conn->prepare("SELECT user_id FROM user WHERE email = ?");
    $stmt->bind_param("s", $email);

    if ($stmt->execute()) {
        $user = $stmt->get_result()->fetch_assoc();
        if ($user) {
            $error = "Email taken. Please try different email.";
        } else {
            // Sanitize inputs
            $role = htmlspecialchars($_POST['role']);
            $first_name = htmlspecialchars($_POST['first_name']);
            $last_name = htmlspecialchars($_POST['last_name']);

            // Hash password
            $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);

            // Insert user into the database
            $sql = "INSERT INTO user (email, role, password, first_name, last_name) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssss", $email, $role, $hashed_password, $first_name, $last_name);

            if ($stmt->execute()) {
                // Clear the form data from session when navigate away
                clear_form_data();
                $stmt->close();
                $conn->close();

                $_SESSION["form_data"] = ['message' => 'User created successfully'];
                // Redirect to users page
                header('Location:' . $promotion_list_route);
                exit();
            } else {
                $error = "Error while creating user: " . $stmt->error;
            }
        }
    } else {
        $error = "Error while creating user: " . $stmt->error;
    }

    $_SESSION["form_data"] = [
        "error" => $error,
        "email" => $_POST["email"],
        "role" => $_POST["role"],
        "first_name" => $_POST["first_name"],
        "last_name" => $_POST["last_name"],
        "password" => $_POST["password"],
    ];

    $stmt->close();
    $conn->close();

    // Redirect back to the form page
    header("Location: " . $_SERVER["REQUEST_URI"]);
}

?>

<div class="container custom-container p-5">
    <form class="custom-form" action="" method="POST">
        <div class="form-title text-content mb-3">ADD USER</div>
        <div class="form-group required py-1">
            <div class="input-wrapper">
                <label for="email" class="form-label control-label">User Email</label>
                <input id="email" name="email" type="email" class="form-control"
                       placeholder="Email" autocomplete="username" required
                       value="<?php echo get_form_val('email') ?>">
            </div>
        </div>
        <div class="row">
            <div class="col form-group required pr-1">
                <div class="input-wrapper">
                    <label for="first_name" class="form-label control-label">First Name</label>
                    <input id="first_name" name="first_name" type="text"
                           class="form-control" placeholder="First Name" required
                           value="<?php echo htmlspecialchars(isset($form_data) ?
                               $form_data["first_name"] : ""); ?>">
                </div>
            </div>
            <div class="col form-group required pl-1">
                <div class="input-wrapper">
                    <label for="last_name" class="form-label control-label">Last Name</label>
                    <input id="last_name" name="last_name" type="text"
                           class="form-control" placeholder="Last Name" required
                           value="<?php echo htmlspecialchars(isset($form_data) ?
                               $form_data["last_name"] : ""); ?>">
                </div>
            </div>
        </div>
        <div class="form-group required py-1">
            <label for="role" class="form-label control-label">User Role</label>
            <select class="custom-select" id="role" name="role">
                <option selected value="supplier">Supplier</option>
                <option value="admin">Admin</option>
                <option value="customer">Customer</option>
            </select>
        </div>
        <div class="form-group required">
            <div class="input-wrapper">
                <label for="password" class="form-label control-label">Password</label>
                <input id="password" name="password" type="password" class="form-control"
                       placeholder="Password" autocomplete="new-password" required
                       value="<?php echo htmlspecialchars(isset($form_data) ?
                           $form_data["password"] : ""); ?>">
            </div>
        </div>
        <button type="submit" class="btn btn-custom-primary mt-2">Add User</button>
        <div class="form-link text-content my-3">
            <a class="form-link" onclick="location='<?php echo $user_list_route ?>'">Back to users</a>
        </div>
    </form>
</div>

