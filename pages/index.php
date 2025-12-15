<?php include('../includes/header.php'); ?>

<section>
    <div id="hero-banner" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner">
            <div class="carousel-item active"
                style="background-image: url('../assets/images/slider2.jpg'); height: 50vh;">
                <div class="carousel-caption">
                    <h5>Save Food, Money, Planet</h5>
                    <p>Discover mystery food boxes from hotels, restaurants, cafes and bakeries at up to 70% off. <br>
                        Fight food waste while enjoying delicious meals.</p>
                    <a href="search.php" class="btn btn-primary">Browse Boxes</a>
                </div>
            </div>
            <div class="carousel-item" style="background-image: url('../assets/images/slider4.jpg'); height: 50vh;">
                <div class="carousel-caption">
                    <h5>Reliable Vendors</h5>
                    <p>Quality and fresh surplus foods for sales.</p>
                    <a href="login.php" class="btn btn-primary">Become a Vendor</a>
                </div>
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#hero-banner" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#hero-banner" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
        </button>
    </div>
</section>

<!-- Featured Categories Section -->
<section class="featured-categories py-5">
    <div class="container text-center">
        <h2 class="mb-4">Featured Categories</h2>
        <div class="row">
            <div class="col-md-4">
                <div class="category-card">
                    <img src="../assets/images/hotel.jpg" alt="Hotels">
                    <h5>Hotels</h5>
                    <a href="search.php?category=hotels" class="btn btn-outline-warning">Save Food</a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="category-card">
                    <img src="../assets/images/cafes.jpg" alt="Restaurants & Cafes">
                    <h5>Restaurants & Cafes</h5>
                    <a href="search.php?category=restaurants & cafes" class="btn btn-outline-warning">Save Money</a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="category-card">
                    <img src="../assets/images/bakery.jpg" alt="Bakeries">
                    <h5>Bakeries</h5>
                    <a href="search.php?category=bakeries" class="btn btn-outline-warning">Save Planet</a>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="impact-counter py-5 text-white text-center" style="background-color: #00a650;">
    <div class="container">
        <h2 class="mb-5">Our Impact So Far</h2>
        <div class="row">

            <div class="col-md-3 col-6 mb-4">
                <div class="stat-box"> <i class="fas fa-utensils fa-3x mb-3"></i>
                    <h3 class="display-5 fw-bold" data-target="5000" id="meals_saved_counter">0</h3>
                    <p class="lead">Meals Saved</p>
                </div>
            </div>

            <div class="col-md-3 col-6 mb-4">
                <div class="stat-box"> <i class="fas fa-leaf fa-3x mb-3"></i>
                    <h3 class="display-5 fw-bold" data-target="12500" id="co2_reduced_counter">0</h3>
                    <p class="lead">CO₂ Reduced (kg)</p>
                </div>
            </div>

            <div class="col-md-3 col-6 mb-4">
                <div class="stat-box"> <i class="fas fa-users fa-3x mb-3"></i>
                    <h3 class="display-5 fw-bold" data-target="1500" id="active_users_counter">0</h3>
                    <p class="lead">Active Users</p>
                </div>
            </div>

            <div class="col-md-3 col-6 mb-4">
                <div class="stat-box"> <i class="fas fa-store-alt fa-3x mb-3"></i>
                    <h3 class="display-5 fw-bold" data-target="250" id="partner_vendors_counter">0</h3>
                    <p class="lead">Partner Vendors</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
require_once '../config/db.php';

$limit = 8;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
if ($page < 1)
    $page = 1;
$offset = ($page - 1) * $limit;

// Get total products for pagination
$count_query = "SELECT COUNT(*) as total FROM products WHERE product_status = 'Available'";
$count_result = $conn->query($count_query);
$total_products = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_products / $limit);

$query = "SELECT * FROM products WHERE product_status = 'Available' LIMIT $limit OFFSET $offset";
$result = $conn->query($query);
?>

<!-- Featured Boxes Section -->
<section id="featured-products" class="featured-products py-5 bg-light">
    <div class="container text-center">
        <h2 class="mb-4">Featured Mystery Boxes</h2>
        <?php if ($result->num_rows > 0): ?>
            <div class="row">
                <?php
                $counter = 0;
                while ($product = $result->fetch_assoc()):

                    $original_price = (float) ($product['original_price'] ?? $product['price']);
                    $selling_price = (float) $product['price'];
                    $is_discounted = ($original_price > $selling_price && $original_price > 0);
                    ?>
                    <div class="col-md-3 mb-4">
                        <div class="card product-card">
                            <img src="../assets/images/<?= !empty($product['image']) ? htmlspecialchars($product['image']) : 'default.jpg'; ?>"
                                class="card-img-top" alt="<?= htmlspecialchars($product['name']); ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($product['name']); ?></h5>

                                <p class="card-text">
                                    <?php if ($is_discounted): ?>
                                        <span style="text-decoration: line-through; color: #888; margin-right: 5px;">
                                            RM<?= number_format($original_price, 2); ?>
                                        </span>
                                        <span style="color: red; font-weight: bold;">
                                            RM<?= number_format($selling_price, 2); ?> (Sale!)
                                        </span>
                                    <?php else: ?>
                                        RM<?= number_format($selling_price, 2); ?>
                                    <?php endif; ?>
                                </p>

                                <a href="product-detail.php?product_id=<?= $product['product_id']; ?>"
                                    class="btn btn-outline-secondary" style="border-radius: 20px;">View Details</a>

                                <form action="add-to-cart.php" method="POST" class="mt-2">
                                    <input type="hidden" name="product_image" value="<?= $product['image']; ?>">
                                    <input type="hidden" name="product_id" value="<?= $product['product_id']; ?>">
                                    <input type="hidden" name="product_name" value="<?= htmlspecialchars($product['name']); ?>">
                                    <input type="hidden" name="product_price" value="<?= $product['price']; ?>">
                                    <button type="submit" class="btn btn-success">Add to Cart</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <?php
                    $counter++;
                    if ($counter % 4 == 0): ?>
                    </div>
                    <div class="row">
                    <?php endif; ?>
                <?php endwhile; ?>
            </div>

            <!-- Pagination Controls -->
            <?php if ($total_pages > 1): ?>
                <nav aria-label="Page navigation" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <!-- Previous Button -->
                        <li class="page-item <?= ($page <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link"
                                href="<?= ($page > 1) ? '?page=' . ($page - 1) . '#featured-products' : '#'; ?>"
                                aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>

                        <!-- Page Numbers -->
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?= ($page == $i) ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?= $i; ?>#featured-products"><?= $i; ?></a>
                            </li>
                        <?php endfor; ?>

                        <!-- Next Button -->
                        <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : ''; ?>">
                            <a class="page-link"
                                href="<?= ($page < $total_pages) ? '?page=' . ($page + 1) . '#featured-products' : '#'; ?>"
                                aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php else: ?>
            <p class="text-muted">No featured products available at the moment.</p>
        <?php endif; ?>
    </div>
</section>

<section class="mission-statement py-5 text-center" style="background-color: #e3f1e0;">
    <div class="container">
        <h2 class="mb-3 fw-bold" style="font-size: larger;">
            </style>Our Mission</h2>
        <p class="lead" style="max-width: 800px; margin: 0 auto; color: #737373;">
            In Malaysia, thousands of kilograms of perfectly good food go to waste every day.
            <b>MakanMystery</b> connects you with local food businesses to rescue surplus meals at amazing prices.
            Together, we're building a more sustainable food system, one mystery box at a time.
        </p>
    </div>
</section>

<section class="how-it-works py-5 text-center">
    <div class="container">
        <h2 class="mb-5 fw-bold">How It Works</h2>
        <div class="row justify-content-center">

            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm" style="border-radius: 15px;">
                    <img src="../assets/images/customer.jpg" class="card-img-top" alt="For Customers"
                        style="max-height: 200px; width: 100%; object-fit: cover; border-top-left-radius: 15px; border-top-right-radius: 15px;">

                    <div class="card-body text-start">
                        <h5 class="card-title fw-bold mt-2" style="font-size: 1.15rem;">For Customers</h5>
                        <p class="card-text text-muted mb-0">Save up to 70% on quality meals while fighting food waste.
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm" style="border-radius: 15px;">
                    <img src="../assets/images/vendor.jpg" class="card-img-top" alt="For Vendors"
                        style="max-height: 200px; width: 100%; object-fit: cover; border-top-left-radius: 15px; border-top-right-radius: 15px;">

                    <div class="card-body text-start">
                        <h5 class="card-title fw-bold mt-2" style="font-size: 1.15rem;">For Vendors</h5>
                        <p class="card-text text-muted mb-0">Turn surplus food into revenue and reduce waste.</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm" style="border-radius: 15px;">
                    <img src="../assets/images/landfill.jpg" class="card-img-top" alt="For Planet"
                        style="max-height: 200px; width: 100%; object-fit: cover; border-top-left-radius: 15px; border-top-right-radius: 15px;">

                    <div class="card-body text-start">
                        <h5 class="card-title fw-bold mt-2" style="font-size: 1.15rem;">For Planet</h5>
                        <p class="card-text text-muted mb-0">Every box saves meals from landfill and reduces carbon
                            footprint.</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<?php include('../includes/footer.php'); ?>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const counters = document.querySelectorAll('.stat-box h3');
        const duration = 2000;

        const impactSection = document.querySelector('.impact-counter');

        if (!impactSection) return;

        const animateCount = (counter) => {
            const target = +counter.getAttribute('data-target');
            const start = 0;
            let current = start;
            const step = Math.ceil(target / (duration / 16));

            const updateCount = () => {
                current += step;

                if (current < target) {
                    counter.innerText = Math.floor(current).toLocaleString();
                    requestAnimationFrame(updateCount);
                } else {
                    let final_text = target.toLocaleString();
                    if (target >= 1000) final_text += '+';
                    counter.innerText = final_text;
                }
            };

            requestAnimationFrame(updateCount);
        };

        const options = {
            root: null,
            rootMargin: '0px',
            threshold: 0.5
        };

        const observer = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    counters.forEach(animateCount);

                    observer.unobserve(entry.target);
                }
            });
        }, options);

        observer.observe(impactSection);
    });
</script>
<?php include('../includes/end.php'); ?>