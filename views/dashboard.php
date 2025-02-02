<?php ob_start(); ?>

<?php
    $eventTypes = array_map(function($type) {
        return App\Constants\EventConstants::EVENT_TYPE_LABELS[$type['event_type']] ?? 'Unknown Type';
    }, $stats['eventsByType']);

    $registrationTypes = array_map(function($type) {
        return App\Constants\EventConstants::REGISTRATION_TYPE_LABELS[$type['registration_type']] ?? 'Unknown Type';
    }, $stats['eventsByRegistrationType']);
?>

<style>
    .card {
        transition: transform 0.2s ease-in-out;
    }

    .card:hover {
        transform: translateY(-5px);
    }

    /* Add some depth to cards */
    .card {
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    /* Improve card title readability */
    .card-title {
        font-size: 1rem;
        font-weight: 600;
        margin-bottom: 0.75rem;
    }

    /* Style card numbers */
    .card h2 {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 0;
    }

    /* Style the small text */
    .card small {
        opacity: 0.9;
        font-size: 0.8rem;
        line-height: 1.2;
    }

    /* Add gradient backgrounds to cards */
    .card.bg-primary {
        background: linear-gradient(45deg, #007bff, #0056b3) !important;
    }

    .card.bg-success {
        background: linear-gradient(45deg, #28a745, #1e7e34) !important;
    }

    .card.bg-info {
        background: linear-gradient(45deg, #17a2b8, #117a8b) !important;
    }

    .card.bg-warning {
        background: linear-gradient(45deg, #ffc107, #d39e00) !important;
    }

    .card.bg-danger {
        background: linear-gradient(45deg, #dc3545, #bd2130) !important;
    }

    .card.bg-secondary {
        background: linear-gradient(45deg, #6c757d, #545b62) !important;
    }

    .chart-container {
        min-height: 300px;
        width: 100%;
    }

    .card.h-100 {
        height: 100% !important;
    }

    /* Style the chart type selector */
    #chartTypeSelector {
        padding: 4px 8px;
        border-radius: 4px;
        border: 1px solid #ced4da;
        background-color: #f8f9fa;
        cursor: pointer;
    }

    #chartTypeSelector:focus {
        border-color: #80bdff;
        outline: 0;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
    }
</style>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="container-fluid p-4">
    <h2>Dashboard</h2>
    <p>Welcome back, <?php echo htmlspecialchars($userName); ?>!</p>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    
    <!-- Statistics Cards -->
    <div class="row mt-4">
        <div class="col-md-3 mb-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Events</h5>
                    <h2 class="card-text"><?php echo $stats['totalEvents'] ?? 0; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Active Events</h5>
                    <h2 class="card-text"><?php echo $stats['activeEvents'] ?? 0; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5 class="card-title">Inactive Events</h5>
                    <h2 class="card-text"><?php echo $stats['inactiveEvents'] ?? 0; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Registrations</h5>
                    <h2 class="card-text"><?php echo $stats['totalRegistrations'] ?? 0; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h5 class="card-title">Expired Events</h5>
                    <div class="d-flex align-items-center">
                        <h2 class="card-text mb-0"><?php echo $stats['expiredEvents'] ?? 0; ?></h2>
                        <div class="ms-3">
                            <!-- <small>Past registration deadline<br>or event date</small> -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card bg-secondary text-white">
                <div class="card-body">
                    <h5 class="card-title">Fully Booked Events</h5>
                    <div class="d-flex align-items-center">
                        <h2 class="card-text mb-0"><?php echo $stats['fullEvents'] ?? 0; ?></h2>
                        <div class="ms-3">
                            <!-- <small>No spots<br>remaining</small> -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mt-4">
        <!-- Events by Type Pie Chart -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Events by Type</h5>
                    <div class="chart-container" style="position: relative; height: 300px;">
                        <canvas id="eventsByTypeChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    
        <!-- Events by Registration Type Pie Chart -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Events by Registration Type</h5>
                    <div class="chart-container" style="position: relative; height: 300px;">
                        <canvas id="eventsByRegistrationTypeChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    
        <!-- Registration Trend Chart -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title mb-0">Registration Trend (Last 30 Days)</h5>
                        <select class="form-select" style="width: auto;" id="chartTypeSelector">
                            <option value="line">Line Chart</option>
                            <option value="bar">Bar Chart</option>
                        </select>
                    </div>
                    <div class="chart-container" style="position: relative; height: 300px;">
                        <canvas id="registrationTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tables Row -->
    <div class="row mt-4">
        <!-- Upcoming Events Table -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Upcoming Events</h5>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Event</th>
                                    <th>Date</th>
                                    <th>Registrations</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($stats['upcomingEvents'])): ?>
                                    <?php foreach ($stats['upcomingEvents'] as $event): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($event['name']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($event['event_date'])); ?></td>
                                        <td><?php echo $event['registrations']; ?>/<?php echo $event['max_attendees']; ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="text-center">No upcoming events</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Popular Events Table -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Popular Events</h5>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Event</th>
                                    <th>Registrations</th>
                                    <th>Capacity</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($stats['popularEvents'])): ?>
                                    <?php foreach ($stats['popularEvents'] as $event): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($event['name']); ?></td>
                                        <td><?php echo $event['registration_count']; ?></td>
                                        <td><?php echo $event['max_attendees']; ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="text-center">No events found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Initialization -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Events by Type Pie Chart
        new Chart(document.getElementById('eventsByTypeChart'), {
            type: 'pie',
            data: {
                labels: <?php echo json_encode($eventTypes); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($stats['eventsByType'], 'count')); ?>,
                    backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Events by Registration Type Pie Chart
        new Chart(document.getElementById('eventsByRegistrationTypeChart'), {
            type: 'pie',
            data: {
                labels: <?php echo json_encode($registrationTypes); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($stats['eventsByRegistrationType'], 'count')); ?>,
                    backgroundColor: ['#4BC0C0', '#FFCE56', '#FF6384']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            font: { size: 12 }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                let value = context.raw || 0;
                                return `${label}: ${value} events`;
                            }
                        }
                    }
                }
            }
        });

        // Registration Trend Chart
        let registrationTrendChart = null;
        const registrationTrendData = {
            labels: <?php echo json_encode(array_column($stats['registrationTrend'], 'date')); ?>,
            datasets: [{
                label: 'Registrations',
                data: <?php echo json_encode(array_column($stats['registrationTrend'], 'count')); ?>,
                borderColor: '#36A2EB',
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderWidth: 2,
                tension: 0.1
            }]
        };

        function createRegistrationChart(type = 'line') {
            const ctx = document.getElementById('registrationTrendChart');
            
            // Destroy existing chart if it exists
            if (registrationTrendChart) {
                registrationTrendChart.destroy();
            }

            registrationTrendChart = new Chart(ctx, {
                type: type,
                data: registrationTrendData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        }

        // Initial chart creation
        createRegistrationChart('line');

        // Chart type selector event listener
        document.getElementById('chartTypeSelector').addEventListener('change', function(e) {
            createRegistrationChart(e.target.value);
        });
    });
</script>

<?php
    $content = ob_get_clean();
    require_once(__DIR__ . '/layouts/master.php');
?>