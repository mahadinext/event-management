<?php ob_start(); ?>

<?php $isLoggedIn = isset($_SESSION['user_id']); ?>

<style>
    .card {
        transition: all 0.3s ease;
    }

    .card .position-absolute {
        opacity: 1; /* Changed from 0 to always show the overlay */
        transition: opacity 0.3s ease;
    }

    /* Styling for expired/fully booked events */
    .card.expired img {
        filter: grayscale(100%);
    }

    .card.expired .card-body {
        opacity: 0.7;
    }

    /* Different colors for Expired vs Fully Booked */
    .card.expired .badge.bg-danger {
        background-color: #dc3545 !important; /* Red for Expired */
    }

    .card:not(.expired) .badge.bg-danger {
        background-color: #6c757d !important; /* Gray for Fully Booked */
    }

    /* Spots remaining indicator */
    .text-danger {
        font-weight: bold;
    }
</style>

<div class="container-fluid">
    <div class="row">
        <!-- Filters Sidebar -->
        <div class="col-md-3 col-lg-2 p-3 bg-light">
            <h5>Filters</h5>
            <form method="GET" class="mb-4">
                <div class="mb-3">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" 
                           value="<?php echo htmlspecialchars($filters['search'] ?? ''); ?>"
                           placeholder="Search events...">
                </div>

                <div class="mb-3">
                    <label class="form-label">Event Type</label>
                    <select name="event_type" class="form-select">
                        <option value="">All Types</option>
                        <?php foreach (App\Constants\EventConstants::EVENT_TYPE_LABELS as $key => $label): ?>
                            <option value="<?php echo $key; ?>" 
                                <?php echo isset($filters['event_type']) && $filters['event_type'] == $key ? 'selected' : ''; ?>>
                                <?php echo $label; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Registration Type</label>
                    <select name="registration_type" class="form-select">
                        <option value="">All Types</option>
                        <?php foreach (App\Constants\EventConstants::REGISTRATION_TYPE_LABELS as $key => $label): ?>
                            <option value="<?php echo $key; ?>" <?php echo isset($filters['registration_type']) && $filters['registration_type'] == $key ? 'selected' : ''; ?>>
                                <?php echo $label; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Date From</label>
                    <input type="date" name="date_from" class="form-control" 
                           value="<?php echo $filters['date_from'] ?? ''; ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Date To</label>
                    <input type="date" name="date_to" class="form-control" 
                           value="<?php echo $filters['date_to'] ?? ''; ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Sort By</label>
                    <select name="sort" class="form-select">
                        <option value="event_date" <?php echo $sort === 'event_date' ? 'selected' : ''; ?>>Event Date</option>
                        <option value="created_at" <?php echo $sort === 'created_at' ? 'selected' : ''; ?>>Recently Added</option>
                        <option value="name" <?php echo $sort === 'name' ? 'selected' : ''; ?>>Name</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Order</label>
                    <select name="order" class="form-select">
                        <option value="ASC" <?php echo $order === 'ASC' ? 'selected' : ''; ?>>Ascending</option>
                        <option value="DESC" <?php echo $order === 'DESC' ? 'selected' : ''; ?>>Descending</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary w-100 mb-2">Apply Filters</button>
                <a href="/" class="btn btn-secondary w-100">Reset Filters</a>
            </form>
        </div>

        <!-- Events Grid -->
        <div class="col-md-9 col-lg-10 p-3">
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                <?php foreach ($events as $event): ?>
                    <div class="col">
                        <?php 
                            // Check if event has expired or is fully booked
                            $isExpired = !empty($event['registration_deadline']) && strtotime($event['registration_deadline']) < time();
                            $availableSpots = $event['max_attendees'] - ($event['registrations'] ?? 0);
                            $isFullyBooked = $availableSpots <= 0;
                            
                            // Add expired class if either condition is met
                            $cardClass = ($isExpired || $isFullyBooked) ? 'card h-100 position-relative expired' : 'card h-100 position-relative';
                        ?>
                        <div class="<?php echo $cardClass; ?>">
                            <?php if ($isExpired || $isFullyBooked): ?>
                                <div class="position-absolute w-100 h-100 d-flex align-items-center justify-content-center" 
                                    style="background: rgba(0,0,0,0.7); z-index: 1;">
                                    <span class="badge bg-danger px-4 py-2" style="font-size: 1.2rem;">
                                        <?php echo $isExpired ? 'EXPIRED' : 'FULLY BOOKED'; ?>
                                    </span>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($event['image_url'])): ?>
                                <img src="<?php echo htmlspecialchars($event['image_url']); ?>" 
                                    class="card-img-top" alt="Event Image"
                                    style="height: 200px; object-fit: cover;">
                            <?php endif; ?>
                            
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($event['name']); ?></h5>
                                <p class="card-text">
                                    <?php echo nl2br(htmlspecialchars(substr($event['description'], 0, 100))); ?>...
                                </p>
                                <div class="mb-2">
                                    <i class="fas fa-calendar"></i> 
                                    <?php echo date('F j, Y', strtotime($event['event_date'])); ?>
                                </div>
                                <div class="mb-2">
                                    <i class="fas fa-map-marker-alt"></i> 
                                    <?php echo htmlspecialchars($event['event_location']); ?>
                                </div>
                                <div class="mb-2">
                                    <i class="fas fa-ticket-alt"></i> 
                                    <?php echo $event['event_type'] == App\Constants\EventConstants::EVENT_TYPE_FREE ? 'Free' : 
                                        'Paid - $' . number_format($event['ticket_price'], 2); ?>
                                </div>
                                <div class="mb-2">
                                    <i class="fas fa-users"></i> 
                                    <?php if ($isFullyBooked): ?>
                                        <span class="text-danger">No spots available</span>
                                    <?php else: ?>
                                        <?php echo $availableSpots; ?> spots left
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="card-footer">
                                <?php if ($isExpired): ?>
                                    <button type="button" class="btn btn-secondary w-100" disabled>
                                        Registration Closed
                                    </button>
                                <?php elseif ($isFullyBooked): ?>
                                    <button type="button" class="btn btn-secondary w-100" disabled>
                                        Spots Filled Up
                                    </button>
                                <?php elseif ($event['registration_type'] == App\Constants\EventConstants::REGISTRATION_USER_ONLY && !$isLoggedIn): ?>
                                    <button type="button" class="btn btn-primary w-100" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#loginModal"
                                            data-event-id="<?php echo $event['id']; ?>">
                                        Register
                                    </button>
                                <?php else: ?>
                                    <button type="button" class="btn btn-primary w-100" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#registrationModal"
                                        data-event-id="<?php echo $event['id']; ?>"
                                        data-event-name="<?php echo htmlspecialchars($event['name']); ?>"
                                        data-event-description="<?php echo htmlspecialchars($event['description']); ?>"
                                        data-event-date="<?php echo $event['event_date']; ?>"
                                        data-event-location="<?php echo htmlspecialchars($event['event_location']); ?>"
                                        data-event-type="<?php echo $event['event_type']; ?>"
                                        data-registration-deadline="<?php echo $event['registration_deadline']; ?>"
                                        data-max-attendees="<?php echo $event['max_attendees']; ?>"
                                        data-registration-type="<?php echo $event['registration_type']; ?>"
                                        data-ticket-price="<?php echo $event['ticket_price']; ?>">
                                        View Details & Register
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <nav aria-label="Page navigation" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>&<?php echo http_build_query(array_merge($filters, ['sort' => $sort, 'order' => $order])); ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Login Modal -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="loginModalLabel">Login Required</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>This event requires you to be logged in to register.</p>
                <div class="d-grid gap-2">
                    <a href="/attendee/login" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt"></i> Login as Attendee
                    </a>
                    <a href="/attendee/register" class="btn btn-outline-primary">
                        <i class="fas fa-user-plus"></i> Register as Attendee
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Event Registration Modal -->
<div class="modal fade" id="registrationModal" tabindex="-1" aria-labelledby="registrationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="registrationModalLabel">Event Registration</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="registrationForm" action="/events/register" method="POST" onsubmit="return false;">
                    <input type="hidden" name="event_id" id="event_id">
                    
                    <!-- Event Details Section -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="border-bottom pb-2">Event Details</h6>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Event Name</label>
                                <input type="text" class="form-control" id="event_name" disabled>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Event Date</label>
                                <input type="text" class="form-control" id="event_date" disabled>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Location</label>
                                <input type="text" class="form-control" id="event_location" disabled>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Event Type</label>
                                <input type="text" class="form-control" id="event_type" disabled>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Registration Deadline</label>
                                <input type="text" class="form-control" id="registration_deadline" disabled>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Available Spots</label>
                                <input type="text" class="form-control" id="max_attendees" disabled>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" id="event_description" rows="3" disabled></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Ticket Price</label>
                                <input type="text" class="form-control" id="ticket_price" disabled>
                            </div>
                        </div>
                    </div>

                    <!-- Attendee Information Section -->
                    <div class="row">
                        <div class="col-12">
                            <h6 class="border-bottom pb-2">Attendee Information</h6>
                        </div>
                        <?php if (!$isLoggedIn): ?>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Name *</label>
                                    <input type="text" class="form-control" name="name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Email *</label>
                                    <input type="email" class="form-control" name="email" required>
                                </div>
                            </div>
                        <?php endif; ?>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Phone *</label>
                                <input type="tel" class="form-control" name="phone" required>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" form="registrationForm" id="registrationSubmit" class="btn btn-primary">Register</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const loginModal = document.getElementById('loginModal');
    if (loginModal) {
        loginModal.addEventListener('show.bs.modal', function(event) {
            // Get the button that triggered the modal
            const button = event.relatedTarget;
            // Get the event ID from data attribute
            const eventId = button.getAttribute('data-event-id');
            
            // Store the event ID in localStorage so we can redirect after login
            localStorage.setItem('intended_event_id', eventId);
        });
    }

    const registrationModal = document.getElementById('registrationModal');
    if (registrationModal) {
        registrationModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            
            // Set event details in the form
            document.getElementById('event_id').value = button.getAttribute('data-event-id');
            document.getElementById('event_name').value = button.getAttribute('data-event-name');
            document.getElementById('event_description').value = button.getAttribute('data-event-description');
            
            // Format date for display
            const eventDate = new Date(button.getAttribute('data-event-date'));
            document.getElementById('event_date').value = eventDate.toLocaleDateString('en-US', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            
            document.getElementById('event_location').value = button.getAttribute('data-event-location');
            
            // Set event type label
            const eventType = button.getAttribute('data-event-type');
            document.getElementById('event_type').value = eventType == <?php echo App\Constants\EventConstants::EVENT_TYPE_FREE; ?> ? 'Free' : 'Paid';
            
            // Format registration deadline
            const deadline = new Date(button.getAttribute('data-registration-deadline'));
            document.getElementById('registration_deadline').value = deadline.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            
            document.getElementById('max_attendees').value = button.getAttribute('data-max-attendees');
            
            // Set ticket price if it's a paid event
            const ticketPriceInput = document.getElementById('ticket_price');
            if (ticketPriceInput) {
                ticketPriceInput.value = '$' + parseFloat(button.getAttribute('data-ticket-price')).toFixed(2);
            }
        });
    }

    // Form validation and submission
    const registrationForm = document.getElementById('registrationForm');
    if (registrationForm) {
        registrationForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get form data
            const formData = new FormData(this);
            const isLoggedIn = <?php echo $isLoggedIn ? 'true' : 'false' ?>;
            
            // Validation
            let isValid = true;
            let errorMessage = '';
            
            if (!isLoggedIn) {
                // Validate required fields for guest registration
                const name = formData.get('name');
                const email = formData.get('email');
                const phone = formData.get('phone');
                
                if (!name || !email || !phone) {
                    toastr.error('Name, Email and Phone are required for guest registration.');
                    isValid = false;
                }
                
                // Basic email validation
                if (email && !email.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
                    toastr.error('Please enter a valid email address.');
                    isValid = false;
                }
                
                // Basic phone validation
                if (phone && !phone.match(/^[0-9]{11}$/)) {
                    toastr.error('Please enter a valid 11-digit phone number.');
                    isValid = false;
                }
            }
            
            if (!isValid) {
                return;
            }
            
            // Show loading state
            const submitButton = document.getElementById('registrationSubmit');
            const originalText = submitButton.innerHTML;
            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Registering...';
            
            // Submit form via AJAX
            fetch('/events/register', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    toastr.success(data.message);
                    
                    // Close modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('registrationModal'));
                    modal.hide();
                    
                    // Refresh the page after a short delay
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    // Show error message
                    toastr.error(data.message || 'Registration failed. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                toastr.error('An error occurred. Please try again.');
            })
            .finally(() => {
                // Reset button state
                submitButton.disabled = false;
                submitButton.innerHTML = originalText;
            });
        });
    }
});
</script>

<?php
$content = ob_get_clean();
require_once(__DIR__ . '/../layouts/public.php');
?>