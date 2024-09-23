<?php
global $pro_dir, $back_route;

// If not an admin, redirect to default route
if (!is_admin()) {
    header("Location: " . $back_route);
    exit();
}

$promotion_list_route = $pro_dir . 'admin/promotion';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    add_promotion();
}

function add_promotion()
{
    global $promotion_list_route;
    $conn = connect_db();
    $promo_code = htmlspecialchars(trim($_POST['promo_code']));

    // Validate email
    $stmt = $conn->prepare("SELECT promo_code FROM promotion WHERE promo_code = ?");
    $stmt->bind_param("s", $promo_code);

    if ($stmt->execute()) {
        $user = $stmt->get_result()->fetch_assoc();
        if ($user) {
            $error = "Promo code already exists. Please try different Promo code.";
        } else {
            // Sanitize inputs
            $promotion_name = htmlspecialchars($_POST['promotion_name']);
            $promo_value = htmlspecialchars($_POST['promo_value']);
            $promo_type = htmlspecialchars($_POST['promo_type']);
            $description = htmlspecialchars($_POST['description']);
            $min_subtotal = htmlspecialchars($_POST['min_subtotal']);

            // Insert promotion into the database
            $sql = "INSERT INTO promotion (promotion_name, promo_code, description, type, value, min_subtotal) 
                VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssdd", $promotion_name, $promo_code, $description, $promo_type,
                $promo_value, $min_subtotal);

            if ($stmt->execute()) {
                // Clear the form data from session when navigate away
                clear_form_data();
                $stmt->close();
                $conn->close();

                $_SESSION["form_data"] = ['message' => 'Promotion created successfully'];
                // Redirect to promotions page
                header('Location:' . $promotion_list_route);
                exit();
            } else {
                $error = "Error while creating promotion: " . $stmt->error;
            }
        }
    } else {
        $error = "Error while creating promotion: " . $stmt->error;
    }

    $_SESSION["form_data"] = [
        "error" => $error,
        "promotion_name" => $_POST["promotion_name"],
        "promo_code" => $_POST["promo_code"],
        "promo_type" => $_POST["promo_type"],
        "promo_value" => $_POST["promo_value"],
        "description" => $_POST["description"],
    ];

    $stmt->close();
    $conn->close();

    // Redirect back to the form page
    header("Location: " . $_SERVER["REQUEST_URI"]);
}

?>

<div class="container custom-container p-5">
    <form class="custom-form" action="" method="POST">
        <div class="form-title text-content mb-3">ADD PROMOTION</div>
        <div class="row">
            <div class="col form-group required pr-1">
                <div class="input-wrapper">
                    <label for="promotion_name" class="form-label control-label">Promo Name</label>
                    <input id="promotion_name" name="promotion_name" type="text" class="form-control"
                           placeholder="Promo Name" required
                           value="<?php echo get_form_val("promotion_name") ?>">
                </div>
            </div>
            <div class="col form-group required pl-1">
                <div class="input-wrapper">
                    <label for="min_subtotal" class="form-label control-label">
                        Minimum Subtotal For Promo</label>
                    <input id="min_subtotal" name="min_subtotal" type="text"
                           class="form-control" placeholder="Min Subtotal" required
                           value="<?php echo get_form_val("min_subtotal") ?>">
                </div>
            </div>
        </div>
        <div class="form-group required">
            <div class="input-wrapper">
                <label for="promo_type" class="form-label control-label">Promo Type</label>
                <select class="custom-select" id="promo_type" name="promo_type">
                    <option selected value="Percentage">Percentage</option>
                    <option value="Amount">Amount</option>
                </select>
            </div>
        </div>
        <div class="row">
            <div class="col form-group required pr-1">
                <div class="input-wrapper">
                    <label for="promo_code" class="form-label control-label">Promo Code</label>
                    <input id="promo_code" name="promo_code" type="text"
                           class="form-control" placeholder="Promo Code" required
                           value="<?php echo get_form_val("promo_code") ?>">
                </div>
            </div>
            <div class="col form-group required pl-1">
                <div class="input-wrapper">
                    <label for="promo_value" class="form-label control-label">Promo Value</label>
                    <input id="promo_value" name="promo_value" type="text"
                           class="form-control" placeholder="Promo Value" required
                           value="<?php echo get_form_val("promo_value") ?>">
                </div>
            </div>
        </div>
        <div class="form-group py-1">
            <div class="input-wrapper">
                <label for="description" class="form-label">Promo Description</label>
                <textarea id="description" name="description" class="form-control" style="height: 100px"
                          placeholder="Promo Description"><?php echo get_form_val('description') ?></textarea>
            </div>
        </div>
        <button type="submit" class="btn btn-custom-primary mt-2">Add Promotion</button>
        <div class="form-link text-content my-3">
            <a class="form-link" onclick="location='<?php echo $promotion_list_route ?>'">Back to promotions</a>
        </div>
    </form>
</div>

