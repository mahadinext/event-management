<?php ob_start(); ?>

<div class="container-fluid p-4">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Edit Event</h5>
                    <a href="/events" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Back to Events
                    </a>
                </div>
                <div class="card-body">
                    <form action="/admin/events/update/<?php echo $event['id']; ?>" method="POST" id="editEventForm">
                        <input type="hidden" name="_method" value="PUT">
                        
                        <!-- Event Name -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Event Name *</label>
                                <input type="text" class="form-control" id="name" name="name" required
                                    value="<?php echo htmlspecialchars($event['name']); ?>">
                                <?php if (isset($errors['name'])): ?>
                                    <div class="text-danger"><?php echo $errors['name']; ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label for="event_location" class="form-label">Location *</label>
                                <input type="text" class="form-control" id="event_location" name="event_location" required
                                    value="<?php echo htmlspecialchars($event['event_location']); ?>">
                                <?php if (isset($errors['event_location'])): ?>
                                    <div class="text-danger"><?php echo $errors['event_location']; ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Event Description -->
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="4"><?php echo htmlspecialchars($event['description']); ?></textarea>
                            <?php if (isset($errors['description'])): ?>
                                <div class="text-danger"><?php echo $errors['description']; ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Event Date and Registration Deadline -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="event_date" class="form-label">Event Date *</label>
                                <input type="datetime-local" class="form-control" id="event_date" name="event_date" required
                                    value="<?php echo date('Y-m-d\TH:i', strtotime($event['event_date'])); ?>">
                                <?php if (isset($errors['event_date'])): ?>
                                    <div class="text-danger"><?php echo $errors['event_date']; ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label for="registration_deadline" class="form-label">Registration Deadline *</label>
                                <input type="datetime-local" class="form-control" id="registration_deadline" name="registration_deadline" required
                                    value="<?php echo date('Y-m-d\TH:i', strtotime($event['registration_deadline'])); ?>">
                                <?php if (isset($errors['registration_deadline'])): ?>
                                    <div class="text-danger"><?php echo $errors['registration_deadline']; ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Event Type and Registration Type -->
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="event_type" class="form-label">Event Type *</label>
                                <select class="form-select" id="event_type" name="event_type" required>
                                    <option value="1" <?php echo $event['event_type'] == 1 ? 'selected' : ''; ?>>Free</option>
                                    <option value="2" <?php echo $event['event_type'] == 2 ? 'selected' : ''; ?>>Paid</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="registration_type" class="form-label">Registration Type *</label>
                                <select class="form-select" id="registration_type" name="registration_type" required>
                                    <option value="1" <?php echo $event['registration_type'] == 1 ? 'selected' : ''; ?>>User Only</option>
                                    <option value="2" <?php echo $event['registration_type'] == 2 ? 'selected' : ''; ?>>All Allowed</option>
                                    <option value="3" <?php echo $event['registration_type'] == 3 ? 'selected' : ''; ?>>Guest Only</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="max_attendees" class="form-label">Maximum Attendees *</label>
                                <input type="number" class="form-control" id="max_attendees" name="max_attendees" required min="1"
                                    value="<?php echo htmlspecialchars($event['max_attendees']); ?>">
                            </div>
                        </div>

                        <!-- Ticket Price -->
                        <div class="mb-3" id="ticketPriceContainer">
                            <label for="ticket_price" class="form-label">Ticket Price ($) *</label>
                            <input type="number" class="form-control" id="ticket_price" name="ticket_price" step="0.01" min="0"
                                value="<?php echo htmlspecialchars($event['ticket_price']); ?>">
                        </div>

                        <!-- Status -->
                        <div class="mb-3">
                            <label for="status" class="form-label">Status *</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="1" <?php echo $event['status'] == 1 ? 'selected' : ''; ?>>Active</option>
                                <option value="2" <?php echo $event['status'] == 2 ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">Update Event</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const eventTypeSelect = document.getElementById('event_type');
    const ticketPriceContainer = document.getElementById('ticketPriceContainer');
    const ticketPriceInput = document.getElementById('ticket_price');

    function toggleTicketPrice() {
        if (eventTypeSelect.value === '2') { // Paid event
            ticketPriceContainer.style.display = 'block';
            ticketPriceInput.required = true;
        } else {
            ticketPriceContainer.style.display = 'none';
            ticketPriceInput.required = false;
            ticketPriceInput.value = '0.00';
        }
    }

    eventTypeSelect.addEventListener('change', toggleTicketPrice);
    toggleTicketPrice(); // Initial state

    // Form validation
    document.getElementById('editEventForm').addEventListener('submit', function(e) {
        const eventDate = new Date(document.getElementById('event_date').value);
        const registrationDeadline = new Date(document.getElementById('registration_deadline').value);

        if (registrationDeadline > eventDate) {
            e.preventDefault();
            toastr.error('Registration deadline must be before event date');
        }
    });
});
</script>

<?php
$content = ob_get_clean();
require_once(__DIR__ . '/../layouts/master.php');
?>