<?php ob_start(); ?>

<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Events</h2>
        <a href="/admin/events/create" class="btn btn-primary">
            <i class="fas fa-plus"></i> Create Event
        </a>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="/admin/events">
                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">Select</option>
                            <?php foreach(App\Constants\EventConstants::STATUS_LABELS as $key => $label) { ?>
                                <option value="<?php echo $key ?>" <?php echo isset($filters['status']) && $filters['status'] == $key ? 'selected' : ''; ?>><?php echo $label ?></option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Event Type</label>
                        <select name="event_type" class="form-select">
                            <option value="">Select</option>
                            <?php foreach(App\Constants\EventConstants::EVENT_TYPE_LABELS as $key => $label) { ?>
                                <option value="<?php echo $key ?>" <?php echo isset($filters['event_type']) && $filters['event_type'] == $key ? 'selected' : ''; ?>><?php echo $label ?></option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Registration Type</label>
                        <select name="registration_type" class="form-select">
                            <option value="">Select</option>
                            <?php foreach(App\Constants\EventConstants::REGISTRATION_TYPE_LABELS as $key => $label) { ?>
                                <option value="<?php echo $key ?>" <?php echo isset($filters['registration_type']) && $filters['registration_type'] == $key ? 'selected' : ''; ?>><?php echo $label ?></option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Event Date (From)</label>
                        <input type="datetime-local" name="event_date_from" class="form-control" value="<?php echo $filters['event_date_from'] ?? ''; ?>">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Event Date (To)</label>
                        <input type="datetime-local" name="event_date_to" class="form-control" value="<?php echo $filters['event_date_to'] ?? ''; ?>">
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary px-2">Apply Filters</button>
                        <a href="/admin/events" class="btn btn-warning px-2">Clear Filters</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Events Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>
                                <a href="?sort=name&order=<?php echo $sort === 'name' && $order === 'ASC' ? 'DESC' : 'ASC'; ?>&status=<?php echo $filters['status'] ?? ''; ?>">
                                    Name
                                    <?php if ($sort === 'name'): ?>
                                        <i class="fas fa-sort-<?php echo $order === 'ASC' ? 'up' : 'down'; ?>"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th>Event Date</th>
                            <th>Registration Deadline</th>
                            <th>Location</th>
                            <th>Registration Type</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Registrations</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($events)): ?>
                            <?php foreach ($events as $event): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($event['name']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($event['event_date'])); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($event['registration_deadline'])); ?></td>
                                    <td><?php echo htmlspecialchars($event['event_location']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $event['registration_type'] == 1 ? 'success' : 'primary'; ?>">
                                            <?php echo App\Constants\EventConstants::REGISTRATION_TYPE_LABELS[$event['registration_type']] ?? 'Unknown'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $event['event_type'] == 1 ? 'success' : 'primary'; ?>">
                                            <?php echo App\Constants\EventConstants::EVENT_TYPE_LABELS[$event['event_type']] ?? 'Unknown'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $event['status'] == 1 ? 'success' : 'danger'; ?>">
                                            <?php echo App\Constants\EventConstants::STATUS_LABELS[$event['status']] ?? 'Unknown'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php echo $event['registrations']; ?> / <?php echo $event['max_attendees']; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="/admin/events/edit/<?php echo $event['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="/admin/events/delete/<?php echo $event['id']; ?>" method="POST" class="d-inline px-2">
                                                <input type="hidden" name="_method" value="DELETE">
                                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this event?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">No events found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php echo $page === $i ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>&status=<?php echo $filters['status'] ?? ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this event?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
            </div>
        </div>
    </div>
</div>

<script>
let eventIdToDelete = null;
const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));

function deleteEvent(eventId) {
    eventIdToDelete = eventId;
    deleteModal.show();
}

document.getElementById('confirmDelete').addEventListener('click', function() {
    if (eventIdToDelete) {
        fetch(`/events/delete/${eventIdToDelete}`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                toastr.success('Event deleted successfully');
                setTimeout(() => window.location.reload(), 1000);
            } else {
                toastr.error(data.message || 'Failed to delete event');
            }
        })
        .catch(error => {
            toastr.error('An error occurred while deleting the event');
        })
        .finally(() => {
            deleteModal.hide();
            eventIdToDelete = null;
        });
    }
});
</script>

<?php
$content = ob_get_clean();
require_once(__DIR__ . '/../layouts/master.php');
?>