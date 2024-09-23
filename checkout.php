<?php
global $pro_dir, $back_route, $user_id, $form_data, $error;

$cart_route = $pro_dir . 'cart';
$order_route = $pro_dir . 'my/orders';

if (!is_logged_in()) {
    header('Location: ' . $pro_dir . 'login');
}

$queries = array();
parse_str($_SERVER['QUERY_STRING'], $queries);

if (!isset($queries['total'])) {
    header('Location: ' . $back_route);
}

$total = doubleval($queries['total']);
$subtotal = doubleval($queries['subtotal']);
$sales_tax = doubleval($queries['sales_tax']);
$shipping_cost = doubleval($queries['shipping']);
$shipping_discount = doubleval($queries['shipping_discount']);
$promo_code = isset($queries['promo_code']) ? $queries['promo_code'] : null;
$promo_value = null;
$promo_type = null;

if (isset($promo_code)) {
    $conn = connect_db();
    $stmt = $conn->prepare("SELECT * FROM `promotion` WHERE promo_code = ? && disabled = ?");
    $disabled = 0;
    $stmt->bind_param("si", $promo_code, $disabled);

    if ($stmt->execute()) {
        $data = $stmt->get_result()->fetch_assoc();
    } else {
        $error = 'Error while applying promo code: ' . $stmt->error;
    }

    if (isset($data['min_subtotal']) && $total >= $data['min_subtotal']) {
        $promo_value = $data['value'];
        $promo_type = $data['type'];

        if ($promo_type != "Amount") {
            $promo_value = $total * ($promo_value / 100.00);
        }
        $total = $total - $promo_value;
    }

    $stmt->close();
    $conn->close();
}

$form_data = [
    'error' => $error,
    'total' => $total,
    'subtotal' => $subtotal,
    'sales_tax' => $sales_tax,
    'shipping_cost' => $shipping_cost,
    'shipping_discount' => $shipping_discount,
    'promo_code' => $promo_code,
    'promo_value' => $promo_value,
    'promo_type' => $promo_type
];

fetch_contact();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    place_order();
}

function place_order()
{
    global $order_route, $user_id, $total, $subtotal, $sales_tax, $shipping_cost, $shipping_discount;

    $conn = connect_db();

    $contact_name = htmlspecialchars(trim($_POST['contact_name']));
    $contact_no = htmlspecialchars(trim($_POST['contact_no']));
    $address_line_one = htmlspecialchars(trim($_POST['address_line_one']));
    $address_line_two = htmlspecialchars(trim($_POST['address_line_two']));
    $city = htmlspecialchars(trim($_POST['city']));
    $state = htmlspecialchars(trim($_POST['state']));
    $postal_code = htmlspecialchars(trim($_POST['postal_code']));

    $card_number = htmlspecialchars(trim($_POST['card_number']));
    $card_expiry = htmlspecialchars(trim($_POST['card_expiry']));
    $card_cvc = htmlspecialchars(trim($_POST['card_cvc']));

    // Start transaction
    $conn->begin_transaction();
    try {
        // Insert contact info into the contact table (for shipping and billing)
        $contact_query = "INSERT INTO `contact` (
                user_id, contact_name, contact_no, address_line_one, address_line_two, city, `state`, postal_code)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE user_id = VALUES(user_id)";

        $stmt_contact = $conn->prepare($contact_query);
        $stmt_contact->bind_param(
            "isssssss",
            $user_id,
            $contact_name,
            $contact_no,
            $address_line_one,
            $address_line_two,
            $city,
            $state,
            $postal_code
        );

        if (!$stmt_contact->execute()) {
            throw new Exception("Contact insert failed: " . $stmt_contact->error);
        }

        // Get the contact ID of the inserted contact (for both shipping and billing, assuming they are the same)
        $contact_id = $stmt_contact->insert_id;
        $stmt_contact->close();

        $query_order = "INSERT INTO `order` (
                     user_id, total, shipping_cost, shipping_discount, sales_tax, shipping_id, billing_id
                     ) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt_order = $conn->prepare($query_order);
        $stmt_order->bind_param(
            "idddiii",
            $user_id,
            $total,
            $shipping_cost,
            $shipping_discount,
            $sales_tax,
            $contact_id,
            $contact_id
        );

        if (!$stmt_order->execute()) {
            throw new Exception("Order insert failed: " . $stmt_order->error);
        }

        // Get the order ID of the inserted order
        $order_id = $stmt_order->insert_id;
        $stmt_order->close();

        // Fetch cart items for the user
        $cart_query = "SELECT * FROM cart_item as ct
                        LEFT JOIN product as p on ct.product_id = p.product_id
                        WHERE user_id = ?";

        $stmt_cart = $conn->prepare($cart_query);
        $stmt_cart->bind_param("i", $user_id);
        $stmt_cart->execute();
        $cart_items = $stmt_cart->get_result();
        $stmt_cart->close();

        // Insert each cart item as an order item
        $order_item_query = "INSERT INTO order_item (
                        order_id, product_id, quantity, item_spec_id, price) VALUES (?, ?, ?, ?, ?)";
        $stmt_order_item = $conn->prepare($order_item_query);

        while ($item = $cart_items->fetch_assoc()) {
            $price = round($item['quantity'] * $item['price'], 2);
            $stmt_order_item->bind_param(
                "iiiid",
                $order_id,
                $item['product_id'],
                $item['quantity'],
                $item['item_spec_id'],
                $price
            );

            if (!$stmt_order_item->execute()) {
                throw new Exception("Order item insert failed: " . $stmt_order_item->error);
            }

            $stmt_product_query = "UPDATE `product` SET stock = stock - 1 WHERE product_id = ?";
            $stmt_product_item = $conn->prepare($stmt_product_query);
            $stmt_product_item->bind_param("i", $item['product_id']);

            if (!$stmt_product_item->execute()) {
                throw new Exception("Product stock update failed: " . $stmt_product_item->error);
            }
            $stmt_product_item->close();
        }
        $stmt_order_item->close();

        // Delete the corresponding cart items after order is placed
        $delete_cart_query = "DELETE FROM cart_item WHERE user_id = ?";
        $stmt_delete_cart = $conn->prepare($delete_cart_query);
        $stmt_delete_cart->bind_param("i", $user_id);

        if (!$stmt_delete_cart->execute()) {
            throw new Exception("Failed to delete cart items: " . $stmt_delete_cart->error);
        }
        $stmt_delete_cart->close();

        // Commit the transaction
        $conn->commit();

        // Clear the form data from session when navigate away
        clear_form_data();

        // Close the database connection
        $conn->close();

        $_SESSION["form_data"] = ['message' => 'Order placed successfully'];
        // Redirect to the order confirmation page
        header("Location: " . $order_route);
        exit();

    } catch (Exception $e) {
        // Rollback the transaction if any query failed
        $conn->rollback();
        $_SESSION["form_data"] = [
            "error" => $e->getMessage(),
            'total' => $total,
            'subtotal' => $subtotal,
            'sales_tax' => $sales_tax,
            'shipping_cost' => $shipping_cost,
            'shipping_discount' => $shipping_discount,
            "contact_name" => $_POST["contact_name"],
            "contact_no" => $_POST["contact_no"],
            "address_line_one" => $_POST["address_line_one"],
            "address_line_two" => $_POST["address_line_two"],
            "city" => $_POST["city"],
            "state" => $_POST["state"],
            "postal_code" => $_POST["postal_code"],
        ];
        // Redirect back to the form page
        header("Location: " . $_SERVER["REQUEST_URI"]);
        // Close the database connection
        $conn->close();
        exit();
    }
}

function fetch_contact()
{
    global $data, $user_id, $sales_tax, $total, $shipping_cost, $shipping_discount;

    $conn = connect_db();
    // Write the SQL statement to fetch my orders
    $sql = "SELECT * FROM `contact` WHERE user_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);

    // Execute the statement
    if (!$stmt->execute()) {
        $_SESSION['form_data'] = ['error' => 'Error while retrieving shipping: ' . $stmt->error];
        $stmt->close();
        $conn->close();
        header("Location: " . $_SERVER["REQUEST_URI"]);
        exit();
    }

    $data = $stmt->get_result()->fetch_assoc();

    $stmt->close();
    $conn->close();
}

?>

<div class="container custom-container custom-container-lg p-5">
    <div class="row">
        <div class="col-lg-6 col-sm-12 pr-5">
            <div class="mb-5">
                <div class="title-total text-content">
                    $<?php echo get_form_val('subtotal') ?>
                </div>
                <div class="text-content" style="font-size: 13px">Pay Secure</div>
            </div>
            <div class="sub-total-info flex-box mb-2">
                <div class="text-content">Subtotal:</div>
                <div class="text-content">
                    $<span id="subtotal"><?php echo get_form_val('subtotal') ?></span>
                </div>
            </div>
            <div class="sub-total-info flex-box mb-2">
                <div class="text-content">Shipping Cost:</div>
                <div class="text-content">
                    $<span id="shipping"><?php echo get_form_val('shipping_cost') ?></span>
                </div>
            </div>
            <?php if (isset($shipping_discount) && $shipping_discount > 0) { ?>
                <div class="sub-total-discount flex-box mb-2">
                    <div class="text-content">Shipping Discount:</div>
                    <div class="text-content">
                        -$<span id="shipping-discount"><?php echo get_form_val('shipping_discount') ?></span>
                    </div>
                </div>
            <?php }
            if (isset($sales_tax) && $sales_tax > 0) { ?>
                <div class="sub-total-info flex-box mb-2">
                    <div class="text-content">Sales Tax:</div>
                    <div class="text-content">
                        $<span id="sales-tax"><?php echo get_form_val('sales_tax') ?></span>
                    </div>
                </div>
            <?php }
            if (isset($promo_value)) { ?>
                <hr>
                <div class="sub-total-info flex-box mb-2">
                    <div class="text-content">Promotion Code:</div>
                    <div class="text-content">
                        <span id="promo_value"><?php echo get_form_val('promo_code') ?></span>
                    </div>
                </div>
                <div class="sub-total-info flex-box mb-2">
                    <div class="text-content">Discount:</div>
                    <div class="text-content">
                        -$<span id="promo_value"><?php echo get_form_val('promo_value') ?></span>
                    </div>
                </div>
            <?php } ?>
            <hr class="my-4">
            <div class="total-info flex-box mb-2">
                <div class="text-content"><b>Total Due:</b></div>
                <div class="text-content">
                    <b>$<span id="final-total"><?php echo get_form_val('total') ?></span></b>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-sm-12 pl-5">
            <form class="custom-form" action="" method="POST">
                <div class="form-title text-content mb-3">CHECKOUT</div>
                <section id="card-info" class="mb-3">
                    <div class="form-sub-title text-content">PAY WITH CARD</div>
                    <div class="form-group required pr-1">
                        <div class="input-wrapper">
                            <label for="card_number" class="form-label control-label">Card Number</label>
                            <input id="card_number" name="card_number" type="text" class="form-control"
                                   placeholder="1234-1234-1234-1234" minlength="16" maxlength="16" pattern="[0-9]+"
                                   required value="<?php echo get_form_val('card_number') ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-8 form-group required pr-1">
                            <div class="input-wrapper">
                                <label for="card_expiry" class="form-label control-label">Card Expiry</label>
                                <input id="card_expiry" name="card_expiry" type="text" class="form-control"
                                       placeholder="MM/YY" maxlength="5" pattern="[0-9/]+" required
                                       value="<?php echo get_form_val('card_expiry') ?>">
                            </div>
                        </div>
                        <div class="col-4 form-group required pl-1">
                            <div class="input-wrapper">
                                <label for="card_cvc" class="form-label control-label">CVC</label>
                                <input id="card_cvc" name="card_cvc" type="text" class="form-control"
                                       placeholder="CVC" maxlength="4" required
                                       value="<?php echo get_form_val('card_cvc') ?>">
                            </div>
                        </div>
                    </div>
                </section>
                <section id="shipping-to" class="mb-2">
                    <div class="form-sub-title text-content">SHIPPING TO</div>
                    <div class="row">
                        <div class="col-7 form-group required pr-1">
                            <div class="input-wrapper">
                                <label for="contact_name" class="form-label control-label">Contact Name</label>
                                <input id="contact_name" name="contact_name" type="text" class="form-control"
                                       placeholder="Contact Name" required
                                       value="<?php echo get_form_val('contact_name') ?>">
                            </div>
                        </div>
                        <div class="col-5 form-group required pl-1">
                            <div class="input-wrapper">
                                <label for="contact_no" class="form-label control-label">Contact No</label>
                                <input id="contact_no" name="contact_no" type="text" class="form-control"
                                       placeholder="Contact No" required
                                       value="<?php echo get_form_val('contact_no') ?>">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6 form-group required pr-1">
                            <div class="input-wrapper">
                                <label for="address_line_one" class="form-label control-label">Address Line 1</label>
                                <input id="address_line_one" name="address_line_one" type="text" class="form-control"
                                       placeholder="Address Line 1" required
                                       value="<?php echo get_form_val('address_line_one') ?>">
                            </div>
                        </div>
                        <div class="col-6 form-group pl-1">
                            <div class="input-wrapper">
                                <label for="address_line_two" class="form-label control-label">Address Line 2</label>
                                <input id="address_line_two" name="address_line_two" type="text" class="form-control"
                                       placeholder="Address Line 2"
                                       value="<?php echo get_form_val('address_line_two') ?>">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col form-group required pr-1">
                            <div class="input-wrapper">
                                <label for="city" class="form-label control-label">City</label>
                                <input id="city" name="city" type="text" class="form-control"
                                       placeholder="City" required
                                       value="<?php echo get_form_val('city') ?>">
                            </div>
                        </div>
                        <div class="col form-group required px-1">
                            <div class="input-wrapper">
                                <label for="state" class="form-label control-label">State</label>
                                <input id="state" name="state" type="text" class="form-control"
                                       placeholder="State" required
                                       value="<?php echo get_form_val('state') ?>">
                            </div>
                        </div>
                        <div class="col form-group required pl-1">
                            <div class="input-wrapper">
                                <label for="postal_code" class="form-label control-label">Postal Code</label>
                                <input id="postal_code" name="postal_code" type="text" class="form-control"
                                       placeholder="Postal Code" required
                                       value="<?php echo get_form_val('postal_code') ?>">
                            </div>
                        </div>
                    </div>
                </section>
                <button type="submit" class="btn btn-custom-primary mt-2">Place Order</button>
                <div class="form-link text-content my-3">
                    <a class="form-link" onclick="location='<?php echo $cart_route ?>'">Back to cart</a>
                </div>
            </form>
        </div>
    </div>
</div>
