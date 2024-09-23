<?php
global $pro_dir, $back_route;


// If not an admin, redirect to default route
if (!is_admin()) {
    header("Location: " . $back_route);
    exit();
}

$promo_list_route = $pro_dir . 'admin/promotion';
$promo_add_route = $promo_list_route . '/create';
$promo_del_route = $promo_list_route . '?promotion_id=';
$promo_disable_route = $promo_list_route . '?action=disable&promotion_id=';

$result = fetch_promotions();
$item_count = $result->num_rows;

if (isset($_GET['promotion_id']) && isset($_GET['action'])) {
    $promotion_id = intval($_GET['promotion_id']);
    $action = $_GET['action'];
    $conn = connect_db();

    // Delete promotion
    if ($action === "delete") {
        // Prepare the SQL statement to delete the promotion
        $stmt = $conn->prepare("DELETE FROM promotion WHERE promotion_id = ?");
        $stmt->bind_param('i', $promotion_id);

        if ($stmt->execute()) {
            $_SESSION["form_data"] = ['message' => 'Promotion deleted successfully'];
        } else {
            $_SESSION["form_data"] = ['error' => 'Failed to delete promotion: ' . $stmt->error];
        }
    }

    if ($action === "disable" && isset($_GET['status'])) {
        $new_status = intval($_GET['status']);
        $stmt = $conn->prepare("UPDATE promotion SET disabled = ? WHERE promotion_id = ?");
        $stmt->bind_param('ii', $new_status, $promotion_id);

        if ($stmt->execute()) {
            $status_message = $new_status ? 'disabled' : 'enabled';
            $_SESSION["form_data"] = ['message' => 'Promotion ' . $status_message . ' successfully'];
        } else {
            $_SESSION["form_data"] = ['error' => 'Failed to update user status: ' . $stmt->error];
        }
    }

    $stmt->close();
    $conn->close();

    // Redirect back to user list page
    header("Location: " . $promo_list_route);
}

function fetch_promotions()
{
    $conn = connect_db();
    $sql = "SELECT * FROM promotion as p ORDER BY p.created_at DESC";

    $stmt = $conn->prepare($sql);
    if (!$stmt->execute()) {
        $_SESSION['form_data'] = ['error' => 'Error while retrieving promotions: ' . $stmt->error];
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

<div class="container my-5" style="min-height: 700px">
    <div class="flex-box mb-4">
        <div class="form-title">PROMOTION LIST</div>
        <button class="btn btn-sm btn-custom-primary" onclick="location='<?php echo $promo_add_route ?>'">
            Create Promotion
        </button>
    </div>
    <hr>
    <?php
    // Check if there are any users
    if ($item_count > 0) { ?>
        <table class="table table-borderless">
            <thead class="thead-white">
            <tr>
                <th>Promotion Name</th>
                <th>Promo Code</th>
                <th>Promo Type</th>
                <th>Promo Value</th>
                <th width="100px">Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php
            $promotions = $result->fetch_all(MYSQLI_ASSOC);
            // Loop through the promotions and create table rows
            foreach ($promotions as $promotion) : ?>
                <tr>
                    <td><?php echo htmlspecialchars($promotion['promotion_name']) ?></td>
                    <td><?php echo htmlspecialchars($promotion['promo_code']) ?></td>
                    <td><?php echo htmlspecialchars($promotion['type']) ?></td>
                    <td><?php echo htmlspecialchars($promotion['value']) ?></td>
                    <td>
                        <?php if ($promotion['disabled']) { ?>
                            <a class="mr-2" aria-hidden="true"
                               href="<?php echo $promo_disable_route . $promotion['promotion_id'] . '&status=0' ?>"
                               onclick="<?php echo 'return confirm(\'Are you sure you want to enable this promotion?\')' ?>">
                                <i class="btn-toggle fa-solid fa-toggle-off"></i></a>
                        <?php } else { ?>
                            <a class="mr-2" aria-hidden="true"
                               href="<?php echo $promo_disable_route . $promotion['promotion_id'] . '&status=1' ?>"
                               onclick="<?php echo 'return confirm(\'Are you sure you want to disable this promotion?\')' ?>">
                                <i class="btn-toggle fa-solid fa-toggle-on"></i></a>
                        <?php } ?>
                        <a class="mr-2" aria-hidden="true"
                           href="<?php echo $promo_del_route . $promotion['promotion_id'] ?>"
                           onclick="<?php echo 'return confirm(\'Are you sure you want to delete this promotion?\')' ?>">
                            <i class="btn-delete fa fa-trash"></i></a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php } else { ?>
        <p>No promotions found.</p>
    <?php } ?>
</div>

