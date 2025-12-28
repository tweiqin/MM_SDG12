<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/db.php';


if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    echo '<div class="container my-5 text-center">';
    echo '<div class="alert alert-warning">Your cart is empty!</div>';
    echo '<a href="cart.php" class="btn btn-primary">Go Back to Cart</a>';
    echo '</div>';
    exit;
}

$grand_total = 0;
foreach ($_SESSION['cart'] as $item) {
    if (!isset($item['price'])) {
        $item['price'] = 0.00;
    }

    if (isset($item['product_price']) && isset($item['quantity'])) {
        $grand_total += $item['product_price'] * $item['quantity'];
    }
}

// Fetch user details for autofill
$user_name = '';
$user_email = '';
$user_phone = '';

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $user_query = "SELECT name, email, phone FROM users WHERE user_id = ?";
    if ($stmt = $conn->prepare($user_query)) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($user_name, $user_email, $user_phone);
        $stmt->fetch();
        $stmt->close();
    }
}
?>
<?php include('../includes/buyerheader.php'); ?>

<div class="container my-5">
    <h1 class="text-center mb-4">Checkout</h1>
    <form action="process-checkout.php" method="POST">

        <div class="row">
            <div class="col-md-6">
                <h4>Contact Information</h4>
                <div class="mb-3">
                    <label for="full_name" class="form-label">Full Name</label>
                    <input type="text" class="form-control" id="full_name" name="full_name"
                        value="<?= htmlspecialchars($user_name); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email"
                        value="<?= htmlspecialchars($user_email); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="phone" class="form-label">Phone Number</label>
                    <input type="tel" class="form-control" id="phone" name="phone"
                        value="<?= htmlspecialchars($user_phone); ?>" required>
                </div>

            </div>

            <div class="col-md-6">
                <h4>Cart Review</h4>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($_SESSION['cart'] as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['product_name']); ?></td>
                                <td><?= $item['quantity']; ?></td>
                                <td>RM<?= number_format($item['product_price'] * $item['quantity'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="2" class="text-end"><strong>Grand Total:</strong></td>
                            <td><strong>RM<?= number_format($grand_total, 2); ?></strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-12">
                <h4>Payment Details</h4>
                <div class="mb-3">
                    <label for="payment_method" class="form-label">Payment Method</label>
                    <select class="form-select" id="payment_method" name="payment_method" required>
                        <option value="">-- Select Payment Method --</option>
                        <option value="credit-card">Credit Card (Stripe)</option>
                        <option value="grab_pay">Grab Pay</option>
                        <option value="fpx_online_banking">FPX Online Banking</option>
                    </select>
                </div>

                <!-- FPX Bank Selection -->
                <div id="fpx-bank-container" class="mb-3" style="display: none;">
                    <label for="fpx_bank" class="form-label">Select Bank</label>
                    <select class="form-select" id="fpx_bank" name="fpx_bank">
                        <option value="">-- Choose Your Bank --</option>
                        <option value="Maybank2u">Maybank2u</option>
                        <option value="CIMB Clicks">CIMB Clicks</option>
                        <option value="Public Bank">Public Bank</option>
                        <option value="RHB Now">RHB Now</option>
                        <option value="Hong Leong Connect">Hong Leong Connect</option>
                        <option value="AmOnline">AmOnline</option>
                    </select>
                </div>

                <!-- Stripe Elements Placeholder -->
                <div id="stripe-card-container" class="mb-3 p-3 border rounded bg-light" style="display: none;">
                    <label class="form-label">Card Details</label>
                    <div id="card-element"></div>
                    <div id="card-errors" role="alert" class="text-danger mt-2 small"></div>
                </div>
            </div>
        </div>

        <div class="text-end mt-4">
            <a href="cart.php" class="btn btn-secondary">Back to Cart</a>
            <button type="submit" id="submit-button" class="btn btn-success">Place Order</button>
        </div>
    </form>
</div>

<!-- Stripe JS SDK -->
<script src="https://js.stripe.com/v3/"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Create an instance of Stripe
        // REPLACE with your actual Publishable Key from Stripe Dashboard
        const stripe = Stripe('pk_test_TYooMQauvdEDq54NiTphI7jx');
        const elements = stripe.elements();

        // Create the card Element
        const card = elements.create('card', {
            style: {
                base: {
                    color: '#32325d',
                    fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
                    fontSmoothing: 'antialiased',
                    fontSize: '16px',
                    '::placeholder': {
                        color: '#aab7c4'
                    }
                },
                invalid: {
                    color: '#fa755a',
                    iconColor: '#fa755a'
                }
            },
            hidePostalCode: true // Hide postal code purely for simpler UI in this mock
        });

        // Add instance to the DOM
        card.mount('#card-element');

        // Handle Payment Method Change
        const paymentSelect = document.getElementById('payment_method');
        const stripeContainer = document.getElementById('stripe-card-container');
        const form = document.querySelector('form');
        const submitButton = document.getElementById('submit-button');

        const fpxContainer = document.getElementById('fpx-bank-container');

        paymentSelect.addEventListener('change', function () {
            // Reset logic
            stripeContainer.style.display = 'none';
            fpxContainer.style.display = 'none';

            if (this.value === 'credit-card') {
                stripeContainer.style.display = 'block';
            } else if (this.value === 'fpx_online_banking') {
                fpxContainer.style.display = 'block';
            }
        });

        // Handle Form Submission
        form.addEventListener('submit', function (event) {

            if (paymentSelect.value === 'credit-card') {
                event.preventDefault(); // Stop default submission to validate Stripe first

                submitButton.disabled = true;
                submitButton.innerText = 'Processing...';

                stripe.createToken(card).then(function (result) {
                    if (result.error) {
                        // Show error
                        const errorElement = document.getElementById('card-errors');
                        errorElement.textContent = result.error.message;
                        submitButton.disabled = false;
                        submitButton.innerText = 'Place Order';
                    } else {
                        // Success!
                        form.submit();
                    }
                });
            } else if (paymentSelect.value === 'grab_pay') {
                event.preventDefault();
                if (confirm("Redirecting to GrabPay Secure Gateway...\n\nClick OK to Authorize Payment.\nClick Cancel to simulate failed authorization.")) {
                     // Simulate processing delay
                     submitButton.disabled = true;
                     submitButton.innerText = 'Authorizing...';
                     setTimeout(() => { form.submit(); }, 1000);
                } else {
                     alert("Payment Authorization Failed. Please try again.");
                }
            } else if (paymentSelect.value === 'fpx_online_banking') {
                event.preventDefault();
                const bank = document.getElementById('fpx_bank').value;
                if (!bank) {
                    alert("Please select your bank.");
                    return;
                }
                if (confirm("Redirecting to " + bank + "...\n\nClick OK to Login and Authorize.\nClick Cancel to simulate failed authorization.")) {
                    submitButton.disabled = true;
                    submitButton.innerText = 'Connecting to Bank...';
                    setTimeout(() => { form.submit(); }, 1000);
                } else {
                    alert("Bank Authorization Failed. Transaction Cancelled.");
                }
            } else {
                return true;
            }
        });
    });
</script>

<?php include('../includes/footer.php'); ?>
<?php include('../includes/end.php'); ?>