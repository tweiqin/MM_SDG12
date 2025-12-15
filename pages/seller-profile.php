<?php
session_start();
require_once '../config/db.php';
include('../includes/header.php');

// Check for seller ID in the URL
if (!isset($_GET['seller_id']) || !is_numeric($_GET['seller_id'])) {
    echo "<div class='container my-5 alert alert-danger'>Invalid Seller ID.</div>";
    include('../includes/footer.php');
    exit;
}

$seller_id = intval($_GET['seller_id']);

// Fetch location coordinates (latitude and longitude)
$query = "SELECT name, email, phone, address, is_active, latitude, longitude FROM users WHERE user_id = ? AND role = 'seller'";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $seller_id);
$stmt->execute();
$result = $stmt->get_result();
$seller = $result->fetch_assoc();
$stmt->close();

if (!$seller) {
    echo "<div class='container my-5 alert alert-warning'>Seller profile not found.</div>";
    include('../includes/footer.php');
    exit;
}

// Determine status text and color
$is_active = $seller['is_active'];
$status_text = ($is_active == 1) ? 'Active' : 'Inactive';
$status_color = ($is_active == 1) ? 'green' : 'red';

// NEW LOGIC: Sanitize phone number for WhatsApp API (remove non-digits)
$clean_phone = preg_replace('/[^0-9]/', '', $seller['phone']);
$whatsapp_url = "https://api.whatsapp.com/send?phone={$clean_phone}";

// Construct the required navigation links using LAT/LNG
$lat = htmlspecialchars($seller['latitude']);
$lng = htmlspecialchars($seller['longitude']);

// Google Maps (using coordinates 'q=lat,lng')
$google_maps_url = "https://www.google.com/maps/dir/?api=1&destination={$lat},{$lng}";

// Waze (using coordinates 'll=lat,lng')
$waze_url = "https://waze.com/ul?ll={$lat},{$lng}&navigate=yes";
?>

<div class="container my-5">
    <h2 class="text-center mb-4">Seller Profile: <?= htmlspecialchars($seller['name']); ?></h2>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    Contact and Business Information
                </div>
                <div class="card-body">
                    <p>
                        <strong>Account Status:</strong>
                        <span style="color: <?= $status_color; ?>; font-weight: bold;">
                            <?= $status_text; ?>
                        </span>
                    </p>

                    <p><strong>Seller Name:</strong> <?= htmlspecialchars($seller['name']); ?></p>
                    
                    <p>
                        <strong>Email:</strong> 
                        <span style="color: inherit; text-decoration: none;">
                            <?= htmlspecialchars($seller['email']); ?>
                        </span>
                    </p>
                    
                    <p>
                        <strong>Contact:</strong> 
                        <a href="<?= $whatsapp_url; ?>" target="_blank" title="Chat on WhatsApp" style="color: #25D366; font-weight: bold; text-decoration: none;">
                            <i class="fab fa-whatsapp"></i> <?= htmlspecialchars($seller['phone']); ?>
                        </a>
                    </p>
                    
                    <p><strong>Address:</strong> <?= nl2br(htmlspecialchars($seller['address'])); ?></p>

                    <?php if ($lat && $lng): ?>
                        <h6 class="mt-4 mb-3"><b>Get Directions:</b></h6>
                        <div class="d-flex align-items-center gap-3">

                            <a href="<?= $google_maps_url; ?>" target="_blank" title="Navigate using Google Maps"
                                style="display: block; width: 40px; height: 40px; border-radius: 50%;">
                                <img src="../assets/images/logos/google.jpg" alt="Google Maps"
                                    style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover; box-shadow: 0 2px 5px rgba(0,0,0,0.2);">
                            </a>

                            <a href="<?= $waze_url; ?>" target="_blank" title="Navigate using Waze"
                                style="display: block; width: 40px; height: 40px; border-radius: 50%;">
                                <img src="../assets/images/logos/waze.jpg" alt="Waze"
                                    style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover; box-shadow: 0 2px 5px rgba(0,0,0,0.2);">
                            </a>

                        </div>
                        <small class="form-text text-muted d-block mt-2">Opens in a new window.</small>
                    <?php else: ?>
                        <p class="text-warning mt-3">Seller has not yet set exact GPS coordinates for easy navigation.</p>
                    <?php endif; ?>

                    <hr class="mt-4">

                    <a href="javascript:history.back()" class="btn btn-secondary">Back</a>
                    <a href="store-products.php?seller_id=<?= $seller_id; ?>" class="btn text-dark" style="background-image: linear-gradient(90deg, #ffde59, #ff914d); 
          border: none; 
          font-weight: bold;">
                        View Store Products
                    </a>

                </div>
            </div>
        </div>
    </div>
</div>

<?php include('../includes/footer.php'); ?>