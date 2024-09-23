<?php
global $pro_dir, $back_route;

// If not an admin, redirect to default route
if (!is_admin()) {
    header("Location: " . $back_route);
    exit();
}

$user_list_route = $pro_dir . 'admin/user';
$user_create_route = $user_list_route . '/create';
$user_edit_route = $user_list_route . '/update?user_id=';
$user_disable_route = $user_list_route . '?action=disable&user_id=';
$user_delete_route = $user_list_route . '?action=delete&user_id=';

if (isset($_GET['user_id']) && isset($_GET['action'])) {
    $user_id = intval($_GET['user_id']);
    $action = $_GET['action'];
    $conn = connect_db();

    // Delete user
    if ($action === "delete") {
        // Prepare the SQL statement to delete the user
        $stmt = $conn->prepare("DELETE FROM user WHERE user_id = ?");
        $stmt->bind_param('i', $user_id);

        if ($stmt->execute()) {
            $_SESSION["form_data"] = ['message' => 'User deleted successfully'];
        } else {
            $_SESSION["form_data"] = ['error' => 'Failed to delete user: ' . $stmt->error];
        }
    }

    if ($action === "disable" && isset($_GET['status'])) {
        $new_status = intval($_GET['status']);
        $stmt = $conn->prepare("UPDATE user SET disabled = ? WHERE user_id = ?");
        $stmt->bind_param('ii', $new_status, $user_id);

        if ($stmt->execute()) {
            $status_message = $new_status ? 'disabled' : 'enabled';
            $_SESSION["form_data"] = ['message' => 'User ' . $status_message . ' successfully'];
        } else {
            $_SESSION["form_data"] = ['error' => 'Failed to update user status: ' . $stmt->error];
        }
    }

    $stmt->close();
    $conn->close();

    // Redirect back to user list page
    header("Location: " . $user_list_route);
}

$result = fetch_users();
$item_count = $result->num_rows;

function fetch_users()
{
    $conn = connect_db();
    $sql = "SELECT * FROM user ORDER BY created_at DESC";

    $stmt = $conn->prepare($sql);
    if (!$stmt->execute()) {
        $_SESSION['form_data'] = ['error' => 'Error while retrieving users: ' . $stmt->error];
        $stmt->close();
        $conn->close();
        header("Location: " . $_SERVER["REQUEST_URI"]);
        exit();
    }

    $result = $stmt->get_result();
    $stmt->close();
    $conn->close();
    return $result;
}

?>

<div class="container my-5" style="min-height: 75vh">
    <div class="flex-box mb-4">
        <div class="form-title">USER LIST</div>
        <button class="btn btn-sm btn-custom-primary" onclick="location='<?php echo $user_create_route ?>'">
            Create User
        </button>
    </div>
    <hr>
    <?php
    // Check if there are any users
    if ($item_count > 0) { ?>
        <table class="table table-borderless">
            <thead class="thead-white">
            <tr>
                <th class="table-header">Email</th>
                <th class="table-header">User Role</th>
                <th class="table-header">First Name</th>
                <th class="table-header">Last Name</th>
                <th width="120px" class="table-header">Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php
            $users = $result->fetch_all(MYSQLI_ASSOC);
            // Loop through the users and create table rows
            foreach ($users as $user): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['email']) ?></td>
                    <td><?php echo htmlspecialchars($user['role']) ?></td>
                    <td><?php echo htmlspecialchars($user['first_name']) ?></td>
                    <td><?php echo htmlspecialchars($user['last_name']) ?></td>
                    <td>
                        <a class="mr-2" aria-hidden="true" href="<?php echo $user_edit_route . $user['user_id'] ?>">
                            <i class="btn-edit fa fa-pen-to-square"></i></a>
                        <?php if ($user['disabled']) { ?>
                            <a class="mr-2" aria-hidden="true"
                               href="<?php echo $user_disable_route . $user['user_id'] . '&status=0' ?>"
                               onclick="<?php echo 'return confirm(\'Are you sure you want to enable this user?\')' ?>">
                                <i class="btn-toggle fa-solid fa-toggle-off"></i></a>
                        <?php } else { ?>
                            <a class="mr-2" aria-hidden="true"
                               href="<?php echo $user_disable_route . $user['user_id'] . '&status=1' ?>"
                               onclick="<?php echo 'return confirm(\'Are you sure you want to disable this user?\')' ?>">
                                <i class="btn-toggle fa-solid fa-toggle-on"></i></a>
                        <?php } ?>
                        <a class="mr-2" aria-hidden="true"
                           href="<?php echo $user_delete_route . $user['user_id'] ?>"
                           onclick="<?php echo 'return confirm(\'Are you sure you want to delete this user?\')' ?>">
                            <i class="btn-delete fa fa-trash"></i></a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php } else { ?>
        <p>No users found.</p>
    <?php } ?>
</div>




