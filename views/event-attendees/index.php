<?php ob_start(); ?>

<div class="container-fluid p-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Event Registrations</h5>
                    <a href="/admin/event-attendees/export-csv<?php echo isset($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : ''; ?>" 
                       class="btn btn-success">
                        <i class="fas fa-download"></i> Export CSV
                    </a>
                </div>
                
                <div class="card-body">
                    <!-- Filters -->
                    <form method="GET" action="/admin/event-attendees" class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Event</label>
                                <select name="event_id" class="form-select">
                                    <option value="">All Events</option>
                                    <?php foreach ($events as $event): ?>
                                        <option value="<?php echo $event['id']; ?>" 
                                            <?php echo isset($filters['event_id']) && $filters['event_id'] == $event['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($event['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-3">
                                <label class="form-label">Registration Type</label>
                                <select name="registration_type" class="form-select">
                                    <option value="">All Types</option>
                                    <?php foreach(App\Constants\UserConstants::USER_TYPE as $key => $label) { ?>
                                        <option value="<?php echo $key ?>" <?php echo isset($filters['registration_type']) && $filters['registration_type'] == $key ? 'selected' : ''; ?>><?php echo $label ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            
                            <div class="col-md-2">
                                <label class="form-label">Date From</label>
                                <input type="date" name="date_from" class="form-control" 
                                       value="<?php echo $filters['date_from'] ?? ''; ?>">
                            </div>
                            
                            <div class="col-md-2">
                                <label class="form-label">Date To</label>
                                <input type="date" name="date_to" class="form-control" 
                                       value="<?php echo $filters['date_to'] ?? ''; ?>">
                            </div>
                            
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2">Filter</button>
                                <a href="/admin/event-attendees" class="btn btn-secondary">Reset</a>
                            </div>
                        </div>
                    </form>

                    <!-- Registrations Table -->
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Event Name</th>
                                    <th>Attendee Details</th>
                                    <th>Registration Type</th>
                                    <th>Contact Info</th>
                                    <th>Registration Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($registrations as $reg): ?>
                                    <tr>
                                        <td><?php echo $reg['id']; ?></td>
                                        <td><?php echo htmlspecialchars($reg['event_name']); ?></td>
                                        <td>
                                            <?php if ($reg['user_id']): ?>
                                                <?php echo htmlspecialchars($reg['attendee_name']); ?>
                                                <span class="badge bg-primary">Registered User</span>
                                            <?php else: ?>
                                                <?php echo htmlspecialchars($reg['attendee_name']); ?>
                                                <span class="badge bg-secondary">Guest</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($reg['user_id']): ?>
                                                <span class="badge bg-primary">Registered User</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Guest</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div>Email: <?php echo htmlspecialchars($reg['attendee_email']); ?></div>
                                            <div>Phone: <?php echo $reg['attendee_phone']; ?></div>
                                        </td>
                                        <td><?php echo date('Y-m-d H:i', strtotime($reg['registration_date'])); ?></td>
                                        <td>
                                            <button type="button" 
                                                    class="btn btn-sm btn-info view-details" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#detailsModal"
                                                    data-registration='<?php echo json_encode($reg); ?>'>
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <nav aria-label="Page navigation" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                        <a class="page-link" 
                                           href="?page=<?php echo $i; ?>&<?php echo http_build_query(array_merge($filters, ['sort' => $sort, 'order' => $order])); ?>">
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
    </div>
</div>

<!-- Details Modal -->
<div class="modal fade" id="detailsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Registration Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <h6>Event Details</h6>
                    <p class="event-name mb-1"></p>
                    <p class="event-date mb-0"></p>
                </div>
                <div class="mb-3">
                    <h6>Attendee Information</h6>
                    <p class="attendee-name mb-1"></p>
                    <p class="attendee-email mb-1"></p>
                    <p class="attendee-phone mb-0"></p>
                </div>
                <div>
                    <h6>Registration Information</h6>
                    <p class="registration-date mb-1"></p>
                    <p class="registration-type mb-0"></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle view details button click
    const buttons = document.querySelectorAll('.view-details');
    buttons.forEach(button => {
        button.addEventListener('click', function() {
            const data = JSON.parse(this.dataset.registration);
            const modal = document.getElementById('detailsModal');
            
            // Update modal content
            modal.querySelector('.event-name').textContent = `Event: ${data.event_name}`;
            modal.querySelector('.event-date').textContent = `Date: ${data.event_date}`;

            modal.querySelector('.attendee-name').textContent = `Name: ${data.attendee_name}`;
            modal.querySelector('.attendee-email').textContent = `Email: ${data.attendee_email}`;
            modal.querySelector('.attendee-phone').textContent = `Phone: ${data.attendee_phone ?? ''}`;

            if (data.user_id) {
                modal.querySelector('.registration-type').textContent = 'Type: Registered User';
            } else {
                modal.querySelector('.registration-type').textContent = 'Type: Guest';
            }
            
            modal.querySelector('.registration-date').textContent = 
                `Registration Date: ${new Date(data.registration_date).toLocaleString()}`;
        });
    });
});
</script>

<?php
$content = ob_get_clean();
require_once(__DIR__ . '/../layouts/master.php');
?>