<?php ob_start(); ?>

<div class="container-fluid p-4">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Create Event</h5>
                    <a href="/admin/events" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Back to Events
                    </a>
                </div>
                <div class="card-body">
                    <form action="/admin/events/store" method="POST" id="createEventForm" class="px-2">
                    <div class="px-3">
                        <!-- Event Name -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Event Name *</label>
                                <input type="text" class="form-control" id="name" name="name" required
                                    value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : (isset($old['name']) ? htmlspecialchars($old['name']) : ''); ?>">
                                <?php if (isset($errors['name'])): ?>
                                    <div class="text-danger"><?php echo $errors['name']; ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label for="event_location" class="form-label">Location *</label>
                                <input type="text" class="form-control" id="event_location" name="event_location" required
                                    value="<?php echo isset($_POST['event_location']) ? htmlspecialchars($_POST['event_location']) : (isset($old['event_location']) ? htmlspecialchars($old['event_location']) : ''); ?>">
                                <?php if (isset($errors['event_location'])): ?>
                                    <div class="text-danger"><?php echo $errors['event_location']; ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Event Description -->
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="4"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : (isset($old['description']) ? htmlspecialchars($old['description']) : ''); ?></textarea>
                            <?php if (isset($errors['description'])): ?>
                                <div class="text-danger"><?php echo $errors['description']; ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Event Date and Registration Deadline -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="event_date" class="form-label">Event Date *</label>
                                <input type="datetime-local" class="form-control" id="event_date" name="event_date" required
                                    value="<?php echo isset($_POST['event_date']) ? htmlspecialchars($_POST['event_date']) : (isset($old['event_date']) ? htmlspecialchars($old['event_date']) : ''); ?>">
                                <?php if (isset($errors['event_date'])): ?>
                                    <div class="text-danger"><?php echo $errors['event_date']; ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label for="registration_deadline" class="form-label">Registration Deadline *</label>
                                <input type="datetime-local" class="form-control" id="registration_deadline" name="registration_deadline" required
                                    value="<?php echo isset($_POST['registration_deadline']) ? htmlspecialchars($_POST['registration_deadline']) : (isset($old['registration_deadline']) ? htmlspecialchars($old['registration_deadline']) : ''); ?>">
                                <?php if (isset($errors['registration_deadline'])): ?>
                                    <div class="text-danger"><?php echo $errors['registration_deadline']; ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Event Type and Registration Type -->
                        <div class="row mb-3">
                            <div class="col-md-4 mb-3">
                                <label for="event_type" class="form-label">Event Type *</label>
                                <select class="form-select" id="event_type" name="event_type" required>
                                    <option value="">Select</option>
                                    <?php foreach(App\Constants\EventConstants::EVENT_TYPE_LABELS as $key => $label) { ?>
                                        <option value="<?php echo $key ?>" <?php echo (isset($_POST['event_type']) && $_POST['event_type'] == $key) || (isset($old['event_type']) && $old['event_type'] == $key) ? 'selected' : ''; ?>><?php echo $label ?></option>
                                    <?php } ?>
                                </select>
                                <?php if (isset($errors['event_type'])): ?>
                                    <div class="text-danger"><?php echo $errors['event_type']; ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="registration_type" class="form-label">Registration Type *</label>
                                <select class="form-select" id="registration_type" name="registration_type" required>
                                    <option value="">Select</option>
                                    <?php foreach(App\Constants\EventConstants::REGISTRATION_TYPE_LABELS as $key => $label) { ?>
                                        <option value="<?php echo $key ?>" <?php echo (isset($_POST['registration_type']) && $_POST['registration_type'] == $key) || (isset($old['registration_type']) && $old['registration_type'] == $key) ? 'selected' : ''; ?>><?php echo $label ?></option>
                                    <?php } ?>
                                </select>
                                <?php if (isset($errors['registration_type'])): ?>
                                    <div class="text-danger"><?php echo $errors['registration_type']; ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="max_attendees" class="form-label">Maximum Attendees *</label>
                                <input type="number" class="form-control" id="max_attendees" name="max_attendees" required min="1"
                                    value="<?php echo isset($_POST['max_attendees']) ? htmlspecialchars($_POST['max_attendees']) : (isset($old['max_attendees']) ? htmlspecialchars($old['max_attendees']) : '100'); ?>">
                                <?php if (isset($errors['max_attendees'])): ?>
                                    <div class="text-danger"><?php echo $errors['max_attendees']; ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-4 mb-3" id="ticketPriceContainer" style="display: none;">
                                <label for="ticket_price" class="form-label">Ticket Price ($) *</label>
                                <input type="number" class="form-control" id="ticket_price" name="ticket_price" step="0.01" min="0"
                                    value="<?php echo isset($_POST['ticket_price']) ? htmlspecialchars($_POST['ticket_price']) : (isset($old['ticket_price']) ? htmlspecialchars($old['ticket_price']) : '0.00'); ?>">
                                <?php if (isset($errors['ticket_price'])): ?>
                                    <div class="text-danger"><?php echo $errors['ticket_price']; ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="status" class="form-label">Status *</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="">Select</option>
                                    <?php foreach(App\Constants\EventConstants::STATUS_LABELS as $key => $label) { ?>
                                        <option value="<?php echo $key ?>" <?php echo (isset($_POST['status']) && $_POST['status'] == $key) || (isset($old['status']) && $old['status'] == $key) ? 'selected' : ''; ?>><?php echo $label ?></option>
                                    <?php } ?>
                                </select>
                                <?php if (isset($errors['status'])): ?>
                                    <div class="text-danger"><?php echo $errors['status']; ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="text-end">
                                    <button type="submit" class="btn btn-primary">Create Event</button>
                                </div>
                            </div>
                        </div>
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
    document.getElementById('createEventForm').addEventListener('submit', function(e) {
        const eventDate = new Date(document.getElementById('event_date').value);
        const registrationDeadline = new Date(document.getElementById('registration_deadline').value);
        const now = new Date();

        if (eventDate < now) {
            e.preventDefault();
            toastr.error('Event date cannot be in the past');
        }

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