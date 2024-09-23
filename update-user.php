<?php
global $pro_dir, $back_route, $data;

// If not an admin, redirect back
if (!is_admin()) {
    header("Location: " . $back_route);
    exit();
}

// get user_id from query params
$queries = array();
parse_str($_SERVER['QUERY_STRING'], $queries);

// If user_id not available, redirect back
if (!isset($queries['user_id'])) {
    header("Location: " . $back_route);
    exit();
}

$user_list_route = $pro_dir . "admin/user";
$user_id = intval($queries['user_id']);

// Fetch user data from database
fetch_user();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    update_user();
}

function fetch_user()
{
    global $data, $user_id;

    $conn = connect_db();
    $stmt = $conn->prepare("SELECT * FROM user WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        $data = $stmt->get_result()->fetch_assoc();
    } else {
        $_SESSION['form_data'] = ['error' => 'Error while retrieving user: ' . $stmt->error];
    }

    $stmt->close();
    $conn->close();
}

function update_user()
{
    global $user_id, $promotion_list_route;
    $conn = connect_db();

    $first_name = htmlspecialchars($_POST['first_name']);
    $last_name = htmlspecialchars($_POST['last_name']);

    // Update user
    $stmt = $conn->prepare("UPDATE user SET first_name = ?, last_name = ? WHERE user_id = ?");
    $stmt->bind_param("ssi", $first_name, $last_name, $user_id);

    if ($stmt->execute()) {
        // Clear the form data from session when navigate away
        clear_form_data();

        $stmt->close();
        $conn->close();

        // Redirect to users page
        $_SESSION["form_data"] = ['message' => 'User updated successfully'];
        header('Location: ' . $promotion_list_route);
        exit();
    } else {
        $error = "Error while updating user: " . $stmt->error;
    }

    $_SESSION["form_data"] = [
        "error" => $error,
        "first_name" => $_POST["first_name"],
        "last_name" => $_POST["last_name"],
        "password" => $_POST["password"],
    ];

    $stmt->close();
    $conn->close();

    // Redirect to same page
    header("Location: " . $_SERVER["REQUEST_URI"]);
    exit();
}

if ($data) { ?>
    <div class="container custom-container p-5">
        <form class="custom-form" action="" method="POST">
            <div class="form-title text-content mb-3">UPDATE USER</div>
            <div class="form-group required py-1">
                <div class="input-wrapper">
                    <label for="email" class="form-label control-label">Email</label>
                    <input id="email" name="email" type="email" class="form-control"
                           placeholder="Email" autocomplete="username" readonly
                           value="<?php echo get_form_val('email'); ?>">
                </div>
            </div>
            <div class="row">
                <div class="col form-group required pr-1">
                    <div class="input-wrapper">
                        <label for="first_name" class="form-label control-label">First Name</label>
                        <input id="first_name" name="first_name" type="text"
                               class="form-control" placeholder="First Name" required
                               value="<?php echo get_form_val('first_name'); ?>">
                    </div>
                </div>
                <div class="col form-group pl-1">
                    <div class="input-wrapper">
                        <label for="last_name" class="form-label control-label">Last Name</label>
                        <input id="last_name" name="last_name" type="text"
                               class="form-control" placeholder="Last Name" required
                               value="<?php echo get_form_val('last_name'); ?>">
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-custom-primary mt-2">Update User</button>
            <div class="form-link text-content my-3">
                <a class="form-link" onclick="location='<?php echo $back_route ?>'">Back to users</a>
            </div>
        </form>
    </div>
<?php } else if (!isset($_SESSION['form_data']['error'])) :
    include_once __DIR__ . '/../../includes/not-found.php';
endif; ?>
