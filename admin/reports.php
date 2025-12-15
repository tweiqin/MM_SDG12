<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports Dashboard</title>
    <link rel="icon" type="image-icon" href="../assets/images/icon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .container {
            max-width: 1400px;
        }

        .chart-container {
            margin-bottom: 30px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            padding: 25px;
            transition: transform 0.3s ease;
            min-height: 400px;
        }

        .chart-container:hover {
            transform: translateY(-5px);
        }

        .section-title {
            margin-bottom: 25px;
            color: #2c3e50;
            font-weight: 600;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }

        .loading {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 300px;
            font-size: 1.2rem;
            color: #666;
        }

        .back-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            transition: transform 0.2s;
        }

        .back-btn:hover {
            color: white;
            transform: translateY(-2px);
            text-decoration: none;
        }
    </style>
</head>

<body>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0">📊 Analytics Reports</h1>
            <a href="dashboard.php" class="back-btn">
                <i class="fas fa-arrow-left me-2"></i> Back to Dashboard
            </a>
        </div>

        <!-- First Row: 2 charts -->
        <div class="row">
            <!-- Chart 1: Weekly Sales Revenue -->
            <div class="col-md-6">
                <div class="chart-container">
                    <h3 class="section-title"><i class="fas fa-chart-bar me-2"></i>Weekly Sales Revenue</h3>
                    <div class="loading" id="salesLoading">Loading sales data...</div>
                    <canvas id="salesChart" height="250" style="display: none;"></canvas>
                </div>
            </div>

            <!-- Chart 2: User Registration Trend -->
            <div class="col-md-6">
                <div class="chart-container">
                    <h3 class="section-title"><i class="fas fa-users me-2"></i>User Registration Trend (Last 4 Months)
                    </h3>
                    <div class="loading" id="usersLoading">Loading user data...</div>
                    <canvas id="userActivityChart" height="250" style="display: none;"></canvas>
                </div>
            </div>
        </div>

        <!-- Second Row: 2 charts -->
        <div class="row">
            <!-- Chart 3: Top Performing Sellers -->
            <div class="col-md-6">
                <div class="chart-container">
                    <h3 class="section-title"><i class="fas fa-store me-2"></i>Top Performing Sellers</h3>
                    <div class="loading" id="sellersLoading">Loading seller data...</div>
                    <canvas id="sellerChart" height="280" style="display: none;"></canvas>
                </div>
            </div>

            <!-- Chart 4: Product Ratings Overview -->
            <div class="col-md-6">
                <div class="chart-container">
                    <h3 class="section-title"><i class="fas fa-star me-2"></i>Product Ratings Distribution</h3>
                    <div class="loading" id="ratingsLoading">Loading rating data...</div>
                    <canvas id="ratingChart" height="280" style="display: none;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Initialize charts
        // In your reports.php, update the sales chart initialization:
        const salesCtx = document.getElementById('salesChart').getContext('2d');
        salesChart = new Chart(salesCtx, {
            type: 'bar',
            data: {
                labels: [], // Will be months
                datasets: [{
                    label: 'Sales Revenue (RM)',
                    data: [],
                    backgroundColor: 'rgba(52, 152, 219, 0.7)',
                    borderColor: 'rgba(52, 152, 219, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                return `Sales: RM ${context.parsed.y.toFixed(2)}`;
                            },
                            afterLabel: function (context) {
                                // Show order count if available
                                const orderCount = salesChart.data.order_counts?.[context.dataIndex];
                                if (orderCount !== undefined) {
                                    return `Orders: ${orderCount}`;
                                }
                                return '';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function (value) {
                                return 'RM ' + value;
                            }
                        }
                    }
                }
            }
        });

        // 2. USER REGISTRATION TREND CHART
        // USER REGISTRATION TREND CHART - Shows ALL user registrations
        const userActivityCtx = document.getElementById('userActivityChart').getContext('2d');
        userActivityChart = new Chart(userActivityCtx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [
                    {
                        label: 'Buyers Registered',
                        data: [],
                        borderColor: '#FF6384', // Red
                        backgroundColor: 'rgba(255, 99, 132, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 5,
                        pointHoverRadius: 8,
                        pointBackgroundColor: '#FF6384',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2
                    },
                    {
                        label: 'Sellers Registered',
                        data: [],
                        borderColor: '#36A2EB', // Blue
                        backgroundColor: 'rgba(54, 162, 235, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 5,
                        pointHoverRadius: 8,
                        pointBackgroundColor: '#36A2EB',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2
                    },
                    {
                        label: 'Total Users',
                        data: [], // Buyers + Sellers
                        borderColor: '#4BC0C0', // Teal
                        backgroundColor: 'rgba(75, 192, 192, 0.1)',
                        borderWidth: 3,
                        fill: false,
                        tension: 0.4,
                        pointRadius: 5,
                        pointHoverRadius: 8,
                        pointBackgroundColor: '#4BC0C0',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        borderDash: [5, 5] // Dashed line for total
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: { size: 14 },
                            usePointStyle: true
                        }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            label: function (context) {
                                return `${context.dataset.label}: ${context.parsed.y}`;
                            },
                            afterBody: function (context) {
                                if (context.length >= 3) {
                                    const buyers = context[0].parsed.y;
                                    const sellers = context[1].parsed.y;
                                    const total = buyers + sellers;
                                    return [`Buyers + Sellers = ${total}`];
                                }
                                return [];
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Month',
                            font: { size: 14, weight: 'bold' }
                        },
                        grid: { color: 'rgba(0,0,0,0.05)' }
                    },
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Number of Users',
                            font: { size: 14, weight: 'bold' }
                        },
                        ticks: {
                            stepSize: 2, // Shows 0 and other even numbers
                            callback: function (value) {
                                return value;
                            }
                        },
                        grid: { color: 'rgba(0,0,0,0.05)' }
                    }
                },
                interaction: { intersect: false, mode: 'nearest' }
            }
        });

        // 3. SELLER CHART
        const sellerCtx = document.getElementById('sellerChart').getContext('2d');
        sellerChart = new Chart(sellerCtx, {
            type: 'bar',
            data: {
                labels: [],
                datasets: [{
                    label: 'Total Sales (RM)',
                    data: [],
                    backgroundColor: 'rgba(155, 89, 182, 0.7)',
                    borderColor: 'rgba(155, 89, 182, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                indexAxis: 'y',
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: { callback: value => 'RM ' + value }
                    }
                }
            }
        });

        // 4. RATING CHART
        const ratingCtx = document.getElementById('ratingChart').getContext('2d');
        ratingChart = new Chart(ratingCtx, {
            type: 'pie',
            data: {
                labels: ['⭐ 1 Star', '⭐⭐ 2 Stars', '⭐⭐⭐ 3 Stars', '⭐⭐⭐⭐ 4 Stars', '⭐⭐⭐⭐⭐ 5 Stars'],
                datasets: [{
                    data: [0, 0, 0, 0, 0],
                    backgroundColor: [
                        '#ff4757', '#ffa502', '#eccc68', '#2ed573', '#3742fa'
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'right' } }
            }
        });

        // Function to load all data
        async function loadDashboardData() {
            try {
                await Promise.all([
                    loadSalesData(),
                    loadUserData(),
                    loadSellerData(),
                    loadRatingData()
                ]);
            } catch (error) {
                console.error('Error loading dashboard data:', error);
            }
        }

        // Update the loadSalesData function:
        async function loadSalesData() {
            try {
                const response = await fetch('get_sales_data.php');
                const data = await response.json();

                if (data.error) {
                    throw new Error(data.error);
                }

                // Show chart and hide loading
                document.getElementById('salesLoading').style.display = 'none';
                document.getElementById('salesChart').style.display = 'block';

                // Update chart data - handle both monthly and weekly data
                if (data.months) {
                    salesChart.data.labels = data.months;
                    salesChart.options.plugins.title = {
                        display: true,
                        text: 'Monthly Sales Revenue'
                    };
                } else if (data.weeks) {
                    salesChart.data.labels = data.weeks;
                    if (data.date_ranges) {
                        // Add date ranges to tooltip
                        salesChart.options.plugins.tooltip.callbacks.title = function (tooltipItems) {
                            const index = tooltipItems[0].dataIndex;
                            return data.date_ranges[index] || data.weeks[index];
                        };
                    }
                    salesChart.options.plugins.title = {
                        display: true,
                        text: 'Weekly Sales Revenue'
                    };
                }

                salesChart.data.datasets[0].data = data.sales;

                // Store additional data for tooltips
                if (data.order_counts) {
                    salesChart.data.order_counts = data.order_counts;
                }

                salesChart.update();

            } catch (error) {
                console.error('Error loading sales data:', error);
                document.getElementById('salesLoading').textContent = 'Error loading sales data';
            }
        }

        // Function to load user data
        async function loadUserData() {
            try {
                const response = await fetch('get_user_data.php');
                const data = await response.json();

                console.log('User registration data:', data);

                if (data.error) throw new Error(data.error);

                // Show chart and hide loading
                document.getElementById('usersLoading').style.display = 'none';
                document.getElementById('userActivityChart').style.display = 'block';

                // Update chart data
                userActivityChart.data.labels = data.months;
                userActivityChart.data.datasets[0].data = data.buyers;
                userActivityChart.data.datasets[1].data = data.sellers;

                // Calculate total (buyers + sellers) if not provided
                if (data.total) {
                    userActivityChart.data.datasets[2].data = data.total;
                } else {
                    const totals = [];
                    for (let i = 0; i < data.buyers.length; i++) {
                        totals.push(data.buyers[i] + data.sellers[i]);
                    }
                    userActivityChart.data.datasets[2].data = totals;
                }

                userActivityChart.update();

            } catch (error) {
                console.error('Error loading user data:', error);
                document.getElementById('usersLoading').textContent = 'Error loading user data: ' + error.message;
            }
        }

        // Function to load seller data
        async function loadSellerData() {
            try {
                const response = await fetch('get_seller_data.php');
                const data = await response.json();

                if (data.error) throw new Error(data.error);

                document.getElementById('sellersLoading').style.display = 'none';
                document.getElementById('sellerChart').style.display = 'block';

                sellerChart.data.labels = data.sellers;
                sellerChart.data.datasets[0].data = data.sales;
                sellerChart.update();

            } catch (error) {
                console.error('Error loading seller data:', error);
                document.getElementById('sellersLoading').textContent = 'Error loading seller data';
            }
        }

        // Function to load rating data
        async function loadRatingData() {
            try {
                const response = await fetch('get_rating_data.php');
                const data = await response.json();

                if (data.error) throw new Error(data.error);

                document.getElementById('ratingsLoading').style.display = 'none';
                document.getElementById('ratingChart').style.display = 'block';

                ratingChart.data.datasets[0].data = data.ratings;
                ratingChart.update();

            } catch (error) {
                console.error('Error loading rating data:', error);
                document.getElementById('ratingsLoading').textContent = 'Error loading rating data';
            }
        }

        // Load data when page loads
        document.addEventListener('DOMContentLoaded', loadDashboardData);
    </script>

</body>

</html>