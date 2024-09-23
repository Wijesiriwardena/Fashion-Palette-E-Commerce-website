<?php
global $pro_dir, $result, $user_id, $product_sizes, $promos;

if (!is_logged_in()) {
    header('Location: ' . $pro_dir . 'login');
    exit();
}

fetch_cart_items();
$item_count = $result->num_rows;

$product_sizes = get_product_sizes();

// delete cart item
if (isset($_GET['cart_item_id'])) {
    delete_cart_item();
}

// update cart items and checkout
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    checkout();
}

function fetch_cart_items()
{
    global $user_id, $result;

    $conn = connect_db();
    $sql = "SELECT * FROM cart_item as ct 
            LEFT JOIN product as p on ct.product_id = p.product_id 
            LEFT JOIN item_spec as isp on ct.item_spec_id = isp.item_spec_id 
            LEFT JOIN color as c on c.color_id = isp.color_id 
            LEFT JOIN size as s on s.size_id = isp.size_id 
            LEFT JOIN neckline as n on n.neckline_id = isp.neckline_id 
            LEFT JOIN sleeve as sl on sl.sleeve_id = isp.sleeve_id 
            LEFT JOIN fabric as f on f.fabric_id = isp.fabric_id 
            WHERE ct.user_id = ? 
            ORDER BY ct.created_at DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);

    // Execute the statement
    if (!$stmt->execute()) {
        $stmt->close();
        $conn->close();
        $_SESSION['form_data'] = ['error' => 'Error while retrieving cart items: ' . $stmt->error];
        header("Location: " . $_SERVER["REQUEST_URI"]);
        exit();
    }

    $result = $stmt->get_result();

    $stmt->close();
    $conn->close();
}

function delete_cart_item()
{
    global $pro_dir;
    $cart_item_id = intval(htmlspecialchars(trim($_GET['cart_item_id'])));

    $conn = connect_db();

    // Prepare the SQL statement to delete item from cart
    $sql = "DELETE FROM cart_item WHERE cart_item_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $cart_item_id);

    if ($stmt->execute()) {
        $_SESSION["form_data"] = ['message' => 'Product deleted from cart successfully'];
    } else {
        $_SESSION["form_data"] = ['error' => 'Failed to deleted product from cart: ' . $stmt->error];
    }

    $stmt->close();
    $conn->close();

    // Redirect back
    header("Location: " . $pro_dir . '/cart');
    exit();
}

function checkout()
{
    global $user_id;
    $data = json_decode(file_get_contents('php://input'), true);

    if ($data && is_array($data)) {
        $conn = connect_db();
        $conn->begin_transaction();

        try {
            foreach ($data as $item) {
                $sql = "SELECT * FROM cart_item WHERE cart_item_id = ?";
                $stmt_get_cart_item = $conn->prepare($sql);
                $stmt_get_cart_item->bind_param('i', $item['cart_item_id']);
                if (!$stmt_get_cart_item->execute()) {
                    throw new Exception("Cart item get failed: " . $stmt_get_cart_item->error);
                }

                $cart_item = $stmt_get_cart_item->get_result()->fetch_assoc();
                $stmt_get_cart_item->close();

                $sql = "UPDATE item_spec SET size_id = ?, color_id = ? WHERE item_spec_id = ?";
                $stmt_update_item_spec = $conn->prepare($sql);
                $stmt_update_item_spec->bind_param('iii',  $item['size_id'], $item['color_id'], $cart_item ['item_spec_id']);
                if (!$stmt_update_item_spec->execute()) {
                    throw new Exception("Cart item spec update failed: " . $stmt_update_item_spec->error);
                }
                $stmt_update_item_spec->close();

                // Update each cart item in the database
                $sql = "UPDATE cart_item SET quantity = ? WHERE cart_item_id = ?";
                $stmt_update_cart_item = $conn->prepare($sql);
                $stmt_update_cart_item->bind_param('ii', $item['quantity'], $item['cart_item_id']);
                if (!$stmt_update_cart_item->execute()) {
                    throw new Exception("Cart item update failed: " . $stmt_update_cart_item->error);
                }
                $stmt_update_cart_item->close();
            }
            $conn->commit();
            $conn->close();

            // Send a JSON response with a redirect URL
            echo json_encode(['status' => 'success']);
        } catch (Exception $e) {
            $conn->rollback();
            $conn->close();
            $_SESSION['form_data'] = ['error' => $e->getMessage()];
            echo json_encode(['status' => 'error', 'message' => 'Error while updating cart items:' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid data']);
    }
}

?>

<div class="row">
    <div class="col-xl-9 col-lg-8 col-md-6 pr-0">
        <?php if (isset($promos) && $promos->num_rows > 0) : $promo_count = $promos->num_rows ?>
            <div id="promos" class="carousel slide" data-ride="carousel">
                <?php
                $promos = $promos->fetch_all(MYSQLI_ASSOC);
                $index = 0;
                foreach ($promos as $promo): ?>
                    <div class="carousel-item text-center <?php if ($index === 0) {
                        echo 'active';
                    } ?>">
                        <div class="promo-info">
                            <p class="promo-text"><?php echo $promo['promotion_name'] ?></p>
                            <?php if (isset($promo['min_subtotal'])): ?>
                                <p class="promo-text">
                                    SPEND OVER $<?php echo $promo['min_subtotal'] ?></p>
                            <?php endif; ?>
                            <?php if ($promo['type'] === 'Amount'): ?>
                                <p class="promo-text">$<?php echo $promo['value'] ?> OFF</p>
                            <?php endif; ?>
                            <?php if ($promo['type'] !== 'Amount'): ?>
                                <p class="promo-text"><?php echo $promo['value'] ?>% OFF</p>
                            <?php endif; ?>
                            <p class="promo-text">
                                APPLY PROMO CODE: <b><?php echo $promo['promo_code'] ?></b></p>
                        </div>
                    </div>
                    <?php
                    $index++;
                endforeach;
                if ($promo_count > 1): ?>
                    <a class="carousel-control-prev" href="#promos" role="button" data-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="sr-only">Previous</span>
                    </a>
                    <a class="carousel-control-next" href="#promos" role="button" data-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="sr-only">Next</span>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <div class="cart-container">
            <div class="cart-heading">
                <div class="form-title">SHOPPING CART</div>
                <div class=""><?php echo $item_count ?> ITEMS</div>
            </div>
            <hr>
            <?php
            // Check if there are any cart items
            if ($item_count > 0) {
                include_once __DIR__ . '/../includes/product.php';
                // Loop through the products and render product cart views
                ?>
                <div class="container px-0 my-3">
                    <table class="table table-borderless cart-table">
                        <thead class="thead-white">
                        <tr>
                            <th style="width: auto" class="table-header-min">PRODUCT</th>
                            <th style="width: 100px" class="table-header-min">COLOR</th>
                            <th style="width: 100px" class="table-header-min">SIZE</th>
                            <th style="width: 100px" class="table-header-min">QUANTITY</th>
                            <th style="width: 100px" class="table-header-min text-right">UNIT PRICE</th>
                            <th style="width: 100px" class="table-header-min text-right">TOTAL</th>
                        </tr>
                        </thead>
                        <tbody id="cart-items">
                        <?php
                        $cart_items = $result->fetch_all(MYSQLI_ASSOC);
                        foreach ($cart_items as $cart_item): ?>
                            <tr id="<?php echo $cart_item['cart_item_id']; ?>">
                                <td style="width=auto">
                                    <?php echo product_cart_view($cart_item); ?>
                                </td>
                                <td style="width=100px">
                                    <select class="color-dropdown" style="height: 30px">
                                        <?php foreach (get_color_options() as $color): ?>
                                            <option value="<?php echo $color['color_id'] ?>"
                                                <?php if ($cart_item['color_id'] == $color['color_id']) {
                                                    echo 'selected';
                                                } ?>>
                                                <?php echo $color['color_name'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td style="width=100px">
                                    <select class="size-dropdown" style="height: 30px">
                                        <?php foreach ($product_sizes as $size): ?>
                                            <option value="<?php echo $size['size_id'] ?>"
                                                <?php if ($cart_item['size_id'] == $size['size_id']) {
                                                    echo 'selected';
                                                } ?>>
                                                <?php echo $size['size_code'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td style="width=100px">
                                    <input type="number" class="quantity"
                                           value="<?php echo $cart_item['quantity']; ?>" min="1" max="99">
                                </td>
                                <td style="width=100px" class="price text-right">
                                    $<?php echo $cart_item['price']; ?>
                                </td>
                                <td style="width=100px" class="total text-right">
                                    $<?php echo $cart_item['price'] * $cart_item['quantity']; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <hr>
                </div>
            <?php } else { ?>
                <div>
                    <div class="text-content">Your cart is empty</div>
                </div>
            <?php } ?>
        </div>
    </div>
    <div class="col-xl-3 col-lg-4 col-md-6 pl-0">
        <div class="checkout-panel">
            <div class="flex-box mb-5">
                <div class="form-title text-content text-center">ORDER SUMMARY</div>
            </div>
            <div class="sub-total-info flex-box mb-2">
                <div class="text-content">Subtotal:</div>
                <div class="text-content">$<span id="subtotal">0.00</span></div>
            </div>
            <div class="sub-total-info flex-box mb-2">
                <div class="text-content">Estimated Shipping Cost:</div>
                <div class="text-content">$<span id="shipping">0.00</span></div>
            </div>
            <div class="sub-total-discount flex-box mb-2">
                <div class="text-content">Shipping Discount:</div>
                <div class="text-content">-$<span id="shipping-discount">0.00</span></div>
            </div>
            <div class="sub-total-info flex-box mb-2">
                <div class="text-content">Estimated Sales Tax:</div>
                <div class="text-content">$<span id="sales-tax">0.00</span></div>
            </div>
            <hr class="my-4">
            <div class="total-info flex-box mb-2">
                <div class="text-content">Estimated Total:</div>
                <div class="text-content">$<span id="final-total">0.00</span></div>
            </div>
            <hr class="my-4">
            <div class="total-info flex-box mb-2">
                <div class="text-content">Promo code:</div>
            </div>
            <div class="form-group">
                <input id="promo-code" name="promo-code" class="form-control">
            </div>
            <button id="checkout-btn" type="button" class="btn btn-custom-secondary" style="width: 100%"
                <?php if ($item_count <= 0) {
                    echo 'disabled';
                } ?>>
                <i class="fa fa-lock" style="font-size: 13px"></i> Secure Checkout
            </button>
        </div>
    </div>
</div>

<script>
    const shippingRand = Math.random();
    const shippingDisRand = Math.random();
    const taxRand = Math.random();

    document.addEventListener('DOMContentLoaded', function () {
        // Function to update the total for each product and the subtotal
        function format_price(strPrice) {
            return parseFloat(strPrice.replace('$', '').replace(',', ''));
        }

        function updateTotal() {
            let subtotal = 0;
            let finalTotal = 0;
            let shippingCost = 0;
            let shippingDiscount = 0;
            let salesTax = 0;

            // Loop through each cart item row
            if (document && document.querySelectorAll('#cart-items tr')) {
                document.querySelectorAll('#cart-items tr').forEach(function (row) {
                    const price = format_price(row.querySelector('.price').textContent);
                    const quantity = parseInt(row.querySelector('.quantity').value);

                    const total = price * quantity;

                    // Update the total column for this row
                    row.querySelector('.total').textContent = '$' + total.toFixed(2);

                    // Add to the subtotal
                    subtotal += total;
                    shippingCost = (shippingRand * subtotal) / 10;
                    shippingDiscount = shippingCost > 10 ? Math.min(shippingRand, shippingDisRand) * shippingCost : 0;

                    finalTotal = subtotal + shippingCost - shippingDiscount;
                    salesTax = taxRand > 0.3 ? (taxRand * finalTotal) / 10 : 0;
                    finalTotal += salesTax;
                });
            }

            // Update the subtotal in the order summary
            document.getElementById('subtotal').textContent = subtotal.toFixed(2);
            document.getElementById('shipping').textContent = shippingCost.toFixed(2);
            document.getElementById('final-total').textContent = finalTotal.toFixed(2);
            document.getElementById('shipping-discount').textContent = shippingDiscount.toFixed(2);
            document.getElementById('sales-tax').textContent = salesTax.toFixed(2);
        }

        // Attach event listeners to all quantity input fields
        if (document && document.querySelectorAll('.quantity')) {
            document.querySelectorAll('.quantity').forEach(function (input) {
                input.addEventListener('input', function () {
                    updateTotal();
                });
            });
        }

        document.getElementById('checkout-btn').addEventListener('click', function () {
            const cartItems = [];

            if (document && document.querySelectorAll('#cart-items tr')) {
                document.querySelectorAll('#cart-items tr').forEach(function (row) {
                    const cart_item_id = row.getAttribute('id');
                    const quantity = row.querySelector('.quantity').value;
                    const size_id = row.querySelector('.size-dropdown option:checked')?.value;
                    const color_id = row.querySelector('.color-dropdown option:checked')?.value;

                    cartItems.push({
                        cart_item_id: cart_item_id,
                        quantity: quantity,
                        size_id: size_id,
                        color_id: color_id
                    });
                });
            }

            // Send the updated quantities to the server via AJAX
            const xhr = new XMLHttpRequest();
            xhr.open('POST', window.location.href, true);
            xhr.setRequestHeader('Content-Type', 'application/json;charset=UTF-8');
            xhr.onreadystatechange = function () {
                let url = window.location.href.replace('cart', 'checkout')
                    + '?subtotal=' + format_price(document.getElementById('subtotal').textContent)
                    + '&total=' + format_price(document.getElementById('final-total').textContent)
                    + '&shipping=' + format_price(document.getElementById('shipping').textContent)
                    + '&shipping_discount=' + format_price(document.getElementById('shipping-discount').textContent)
                    + '&sales_tax=' + format_price(document.getElementById('sales-tax').textContent);

                const promoCode = document.getElementById('promo-code').value;
                if (promoCode) {
                    url = url + '&promo_code=' + promoCode;
                }

                window.location.href = url;
            };
            xhr.send(JSON.stringify(cartItems));
        });

        // Initialize the subtotal on page load
        updateTotal();
    });
</script>


