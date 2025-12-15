<?php
include('../includes/header.php');
require_once '../config/db.php';

// Helper function for dynamic bind_param
$ref_values = function ($arr) {
    $refs = [];
    foreach ($arr as $key => $value)
        $refs[$key] = &$arr[$key];
    return $refs;
};

function renderProductCard($product)
{
    // Retrieve stock level
    $quantity_left = (int) ($product['quantity'] ?? 0);
    $is_out_of_stock = ($quantity_left <= 0);

    // Prepare prices for discount display
    $original_price = (float) ($product['original_price'] ?? $product['price']);
    $selling_price = (float) $product['price'];
    $is_discounted = ($original_price > $selling_price && $original_price > 0);

    ?>
    <div class="col-md-3 mb-4">
        <div class="card featured-products text-center" style="heigt: 300px; width:100%">
            <img src="../assets/images/<?= !empty($product['image']) ? htmlspecialchars($product['image']) : 'default.jpg'; ?>"
                class="card-img-top" alt="<?= htmlspecialchars($product['name']); ?>">
            <div class="card-body">

                <h5 style="font-weight: bold; display: inline-block;" class="card-title">
                    <?= htmlspecialchars($product['name']); ?>
                </h5>
                <?php if ($quantity_left > 0): ?>
                    <span style="color: red; font-size: 0.9em; margin-left: 5px;">
                        <?= $quantity_left; ?> left
                    </span>
                <?php else: ?>
                    <span style="color: red; font-weight: bold; font-size: 0.9em; margin-left: 5px;">
                        SOLD OUT
                    </span>
                <?php endif; ?>

                <p>
                    <?php if ($is_discounted): ?>
                        <span style="text-decoration: line-through; color: #888; margin-right: 10px;">
                            RM<?= number_format($original_price, 2); ?>
                        </span>
                        <span style="color: red; font-weight: bold;">
                            RM<?= number_format($selling_price, 2); ?> (Sale!)
                        </span>
                    <?php else: ?>
                        <span style="font-weight: bold;">
                            RM<?= number_format($selling_price, 2); ?>
                        </span>
                    <?php endif; ?>
                </p>
                <a href="product-detail.php?product_id=<?= $product['product_id']; ?>" class="btn btn-outline-secondary"
                    style="border-radius: 20px;">View Details</a>

                <form action="add-to-cart.php" method="POST" class="mt-2">
                    <input type="hidden" name="product_image" value="<?= $product['image']; ?>">
                    <input type="hidden" name="product_id" value="<?= $product['product_id']; ?>">
                    <input type="hidden" name="product_name" value="<?= htmlspecialchars($product['name']); ?>">
                    <input type="hidden" name="product_price" value="<?= $product['price']; ?>">
                    <button type="submit" class="btn btn-success" <?= $is_out_of_stock ? 'disabled' : ''; ?>>
                        <?= $is_out_of_stock ? 'Out of Stock' : 'Add to Cart'; ?>
                    </button>
                </form>
            </div>
        </div>
    </div>
    <?php
}

// Search query handling
$searchResults = null;
$searchQuery = $_GET['query'] ?? null;
$user_lat = $_GET['user_lat'] ?? null;
$user_lng = $_GET['user_lng'] ?? null;
$limit = 100;
$sellers_for_map = [];
$products_list = [];

// ======================
// PHP LOGIC: GEO-SEARCH
// ======================
if ($user_lat && $user_lng) {
    // GEO-SEARCH LOGIC
    $distance_sql = "( 6371 * acos( cos( radians(?) ) * cos( radians( u.latitude ) ) * cos( radians( u.longitude ) - radians(?) ) + sin( radians(?) ) * sin( radians( u.latitude ) ) ) )";

    // Select all product details needed for rendering, plus seller geo-data
    $query = "
        SELECT 
            p.product_id, p.name, p.price, p.original_price, p.image, p.quantity,
            u.user_id AS seller_id, u.name AS seller_name, u.latitude, u.longitude, u.logo,
            ({$distance_sql}) AS distance
        FROM products p
        JOIN users u ON p.seller_id = u.user_id
        WHERE p.product_status = 'Available' AND u.latitude IS NOT NULL AND u.longitude IS NOT NULL
        HAVING distance < 20.00 
        ORDER BY distance ASC
        LIMIT ?";

    $stmt = $conn->prepare($query);

    $bind_params = ['dddi', $user_lat, $user_lng, $user_lat, $limit];

    $bind_refs = [];
    foreach ($bind_params as $key => $value) {
        if ($key === 0) {
            $bind_refs[] = $value;
        } else {
            $bind_refs[] = &$bind_params[$key];
        }
    }

    if (call_user_func_array([$stmt, 'bind_param'], $bind_refs)) {
        if ($stmt->execute()) {
            $results = $stmt->get_result();

            $unique_seller_ids = [];

            // Aggregate unique sellers for map plotting and products for display
            while ($product = $results->fetch_assoc()) {
                $products_list[] = $product;

                if (!in_array($product['seller_id'], $unique_seller_ids)) {
                    $unique_seller_ids[] = $product['seller_id'];

                    $sellers_for_map[] = [
                        'name' => $product['seller_name'],
                        'lat' => (float) $product['latitude'],
                        'lng' => (float) $product['longitude'],
                        'logo' => $product['logo'],
                        'distance' => number_format($product['distance'], 2),
                        'seller_id' => $product['seller_id'],
                        'product_id' => $product['product_id']
                    ];
                }
            }
        }
    }
    $stmt->close();

    // Assign geo-search results to the custom object structure
    $searchResults = (object) ['num_rows' => count($products_list), 'products' => $products_list];

}
// ==================================================
// PHP LOGIC: FALLBACK (Text Search or All Products)
// ==================================================
else {
    $search_condition = "";
    $bind_types = "";
    $bind_params = [];

    // Check if standard text search query is present
    if ($searchQuery) {
        $searchTerm = '%' . $searchQuery . '%';
        $search_condition = " AND (name LIKE ? OR description LIKE ?)";
        $bind_types = "ss";
        $bind_params = [$searchTerm, $searchTerm];
    }

    // FALLBACK Query: Show All Available (filtered by text if present)
    $allProductsQuery = "SELECT product_id, name, price, original_price, image, quantity FROM products WHERE product_status = 'Available' {$search_condition} ORDER BY created_at DESC";
    $stmt_all = $conn->prepare($allProductsQuery);

    if ($searchQuery) {
        $bind_refs = array_merge([$bind_types], $bind_params);
        call_user_func_array([$stmt_all, 'bind_param'], $ref_values($bind_refs));
    }

    $stmt_all->execute();
    $raw_results = $stmt_all->get_result();
    $stmt_all->close();

    $products_list = [];
    while ($product = $raw_results->fetch_assoc()) {
        $products_list[] = $product;
    }

    $searchResults = (object) ['num_rows' => count($products_list), 'products' => $products_list];
}

?>

<div class="container my-5">
    <h1 class="text-center mb-4">Find Surplus Food for Sale in Mystery Box</h1>

    <div id="map" style="height: 400px; width: 100%; margin-bottom: 25px; border-radius: 10px;"></div>

    <form id="location-form" class="form-inline justify-content-center mb-4 mx-auto my-5" method="GET"
        action="search.php">
        <div class="input-group w-100 mx-auto">
            <button type="button" id="use-current-location" class="btn btn-primary"
                style="border-radius: 5px; width: 100%; padding: 10px;">
                <i class="fas fa-crosshairs"></i> Use Current Location to Find Nearby Boxes
            </button>
        </div>
        <input type="hidden" name="user_lat" id="user_lat" value="<?= htmlspecialchars($user_lat ?? ''); ?>">
        <input type="hidden" name="user_lng" id="user_lng" value="<?= htmlspecialchars($user_lng ?? ''); ?>">
    </form>

    <form class="form-inline justify-content-center mb-4 mx-auto my-5" method="GET" action="search.php">
        <div class="input-group w-50 mx-auto">
            <input type="text" class="form-control" name="query" placeholder="Search for products..."
                value="<?= htmlspecialchars($searchQuery); ?>" required>
            <div class="input-group-append">
                <button type="submit" class="btn btn-warning btn-lg ms-2">Search</button>
            </div>
        </div>
    </form>
</div>

<section class="results-section container">
    <h2 class="mb-4">
        <?php if ($user_lat): ?>
            Nearest Boxes (<?= count($sellers_for_map); ?> Stores Found)
        <?php elseif ($searchQuery): ?>
            Search Results for "<?= htmlspecialchars($searchQuery); ?>"
        <?php else: ?>
            All Available Mystery Boxes
        <?php endif; ?>
    </h2>

    <?php if ($searchResults->num_rows > 0): ?>
        <div class="row justify-content-center">
            <?php
            // Loop directly over the 'products' array property
            foreach ($searchResults->products as $product):
                renderProductCard($product);
            endforeach;
            ?>
        </div>
    <?php else: ?>
        <p class="text-center col-12">
            <?php if ($user_lat): ?>
                No available boxes found within 20km of your location. Try setting your location manually on your profile.
            <?php else: ?>
                No featured mystery boxes available at the moment.
            <?php endif; ?>
        </p>
    <?php endif; ?>
</section>


<?php include('../includes/footer.php'); ?>
<?php include('../includes/end.php'); ?>

<script>
    let map;
    let userMarker;
    const vendorLocations = <?= json_encode($sellers_for_map); ?>;
    const defaultCoords = [3.140853, 101.693207]; // [lat, lng] for Leaflet

    function initMap() {
        const mapElement = document.getElementById('map');
        if (!mapElement) return;

        // Use submitted location or default
        const urlLat = document.getElementById('user_lat').value;
        const urlLng = document.getElementById('user_lng').value;
        let initialCoords = defaultCoords;
        let initialZoom = 12;

        if (urlLat && urlLng) {
            initialCoords = [parseFloat(urlLat), parseFloat(urlLng)];
            initialZoom = 14;
        }

        // 1. Initialize Leaflet Map
        map = L.map('map').setView(initialCoords, initialZoom);

        // 2. Add OpenStreetMap Tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        // 3. Add user location marker if available
        if (urlLat) {
            userMarker = L.marker(initialCoords, {
                icon: L.divIcon({ className: 'user-location-marker', html: '<i class="fas fa-crosshairs" style="color: blue; font-size: 20px;"></i>' })
            }).addTo(map);
        }

        plotVendorMarkers();
    }

    // Plots vendor pins on the map
    function plotVendorMarkers() {
        // Fix: Using L.popup() for consistency
        const infoWindow = L.popup();

        vendorLocations.forEach(vendor => {
            // 1. Create Icon using dynamic path for the logo
            const logoPath = vendor.logo ? `../assets/images/logos/${vendor.logo}` : null;

            const customIcon = logoPath
                ? L.icon({
                    iconUrl: logoPath,
                    iconSize: [32, 32],
                    iconAnchor: [16, 32],
                    popupAnchor: [0, -32]
                })
                : null;

            // 2. Create Marker
            const marker = L.marker([vendor.lat, vendor.lng], {
                icon: customIcon
            }).addTo(map);

            // 3. Info window content with links
            const content = `
            <div class="info-window-content">
                <h6>${vendor.name}</h6>
                <p>Distance: ${vendor.distance} km</p>
                <a href="store-products.php?seller_id=${vendor.seller_id}" class="btn btn-sm btn-primary text-white">View Store Boxes</a>
                <br><br><a href="seller-profile.php?seller_id=${vendor.seller_id}">View Profile</a>
            </div>
        `;

            // 4. Bind popup and set click listener
            marker.bindPopup(content);

            marker.on('click', function (e) {
                this.openPopup();
            });
        });
    }

    // Function to handle location found by browser
    function handleLocationFound(position) {
        const pos = {
            lat: position.coords.latitude,
            lng: position.coords.longitude
        };

        // Update hidden form fields
        document.getElementById('user_lat').value = pos.lat;
        document.getElementById('user_lng').value = pos.lng;

        // Submit the form to run the Haversine query on the server
        document.getElementById('location-form').submit();
    }

    // Attach geolocation function to the button
    document.addEventListener('DOMContentLoaded', () => {
        initMap();

        const locationButton = document.getElementById('use-current-location');
        if (locationButton) {
            locationButton.addEventListener('click', () => {
                if (navigator.geolocation) {
                    locationButton.disabled = true;
                    locationButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Fetching location...';

                    navigator.geolocation.getCurrentPosition(handleLocationFound, (error) => {
                        alert("Geolocation failed or was denied: " + error.message);
                        locationButton.disabled = false;
                        locationButton.innerHTML = '<i class="fas fa-crosshairs"></i> Use Current Location to Find Nearby Boxes';
                    }, { enableHighAccuracy: true, timeout: 5000, maximumAge: 0 });
                } else {
                    alert("Geolocation not supported by this browser.");
                }
            });
        }
    });

    window.initMap = initMap;
</script>