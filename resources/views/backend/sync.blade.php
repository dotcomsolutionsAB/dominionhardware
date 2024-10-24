<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<style>
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        padding: 8px 12px;
        margin-right: 5px;
        border-radius: 4px;
        transition: background-color 0.3s ease;
    }

    .btn i {
        margin-right: 5px;
    }

    .btn-warning:hover {
        background-color: #e6a700;
    }

    .btn-primary:hover {
        background-color: #0056b3;
    }

    .btn-success:hover {
        background-color: #28a745;
    }

    .table {
        margin-top: 20px;
    }

    .table th, .table td {
        text-align: center;
        vertical-align: middle;
    }

    .table thead th {
        background-color: #f8f9fa;
        font-weight: bold;
    }
</style>
<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- FontAwesome (optional, for icons) -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">


@extends('backend.layouts.app')

@section('content')
    <div class="container">
        <div class="h" style="display: flex; justify-content:space-between;">
            <h1>Sync</h1>

            <!-- Add Button to trigger the modal -->
            <button type="button" class="btn btn-primary mb-4" data-toggle="modal" data-target="#addGoogleSheetModal">
                <i class="fas fa-plus"></i> Add Google Sheet
            </button>
        </div>

        <!-- Existing Table -->
        <table class="table table-bordered table-striped table-hover">
            <thead>
                <tr>
                    <th>Id</th>
                    <th>Seller Name</th>
                    <th>Sheet Name</th>
                    <th>Sheet Path</th>
                    <th>Status</th>
                    <th>Last Sync</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $row)
                    <tr>
                        <td>{{ $row->id }}</td>
                        <td>{{ $row->seller_name }} 
                            <span style="color:rgb(236, 91, 91); font-weight:500; font-size: smaller;">
                                @if($row->seller_type == 'admin')
                                    Admin 
                                @else
                                    <span style="color:rgb(92, 92, 221); font-weight:700; font-size: small;"> (Seller ✅) </span>
                                @endif
                            </span>
                        </td>
                        <td>{{ $row->sheet_name }}</td>
                        <td>{{ $row->sheet_path }}</td>
                        <td id="status-value-{{ $row->id }}">{{ $row->status }}</td> 
                        <!-- Last Update Column -->
                        <td id="last-update-{{ $row->id }}">{{ $row->last_update }}</td>
                        <td>
                            <span id="status-text-{{ $row->id }}">
                                @if($row->status == 1)
                                    <form id="sync-form-{{ $row->id }}" action="{{ route('sync.status', $row->id) }}" method="POST" style="display: inline;">
                                        @csrf
                                        <button type="button" class="btn btn-sm btn-warning sync-btn" data-id="{{ $row->id }}" title="Sync Status">
                                            <i class="fas fa-sync-alt"></i> Sync Status
                                        </button>
                                    </form>
                                @else
                                    Imported shortly
                                @endif
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Modal for adding a Google Sheet -->
        <div class="modal fade" id="addGoogleSheetModal" tabindex="-1" role="dialog" aria-labelledby="addGoogleSheetModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addGoogleSheetModalLabel">Add Google Sheet</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('sync.store') }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label for="seller_id">Seller Name</label>
                                <select name="seller_id" id="seller_id" class="form-control">
                                    <option value="">Select Seller</option>
                                    @foreach($sellers as $seller)
                                        <option value="{{ $seller->id }}">{{ $seller->name }}</option> <!-- Populate seller names -->
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="sheet_name">Sheet Name</label>
                                <input type="text" name="sheet_name" id="sheet_name" class="form-control" placeholder="Enter Sheet Name" required>
                            </div>
                            <div class="form-group">
                                <label for="sheet_path">Sheet Path</label>
                                <input type="text" name="sheet_path" id="sheet_path" class="form-control" placeholder="Enter Sheet Path" required>
                            </div>
                            <button type="submit" class="btn btn-success mt-2">
                                <i class="fas fa-plus"></i> Add Google Sheet
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection

<!-- jQuery and Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
{{-- <script>
    document.addEventListener("DOMContentLoaded", function () {
        // Attach click event listener to all sync buttons
        addSyncButtonEventListeners(); // Initial attachment of event listeners

        // Function to attach event listeners to all sync buttons
        function addSyncButtonEventListeners() {
            document.querySelectorAll('.sync-btn').forEach(function(button) {
                button.addEventListener('click', function() {
                    let id = this.getAttribute('data-id');

                    // Make an AJAX request to sync the status
                    fetch(`/sync/status/${id}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            id: id
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        // If the status was successfully updated, hide the button and show "Imported shortly"
                        if (data.success) {
                            document.getElementById(`status-text-${id}`).innerHTML = "Imported shortly";

                            // Update the status column in the table to reflect the new status (0)
                            document.getElementById(`status-value-${id}`).innerText = 0;

                            // Start polling the server to check if the status has changed back to 1
                            setTimeout(function() {
                                pollStatus(id);
                            }, 5000); // Poll after 5 seconds
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
                });
            });
        }

        // Poll the server to check if the status has become 1 again
        function pollStatus(id, attempts = 0) {
            let pollLimit = 12; // Stop polling after 12 attempts (1 minute)
            
            if (attempts >= pollLimit) {
                console.log('Polling stopped after reaching the limit.');
                return;
            }

            fetch(`/sync/status/check/${id}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.status == 1) {
                    // If status is 1, show the sync button again and update the status column
                    document.getElementById(`status-text-${id}`).innerHTML = `
                        <form id="sync-form-${id}" action="/sync/status/${id}" method="POST" style="display: inline;">
                            @csrf
                            <button type="button" class="btn btn-sm btn-warning sync-btn" data-id="${id}" title="Sync Status">
                                <i class="fas fa-sync-alt"></i> Sync Status
                            </button>
                        </form>
                    `;

                    // Update the status column to reflect the new status (1)
                    document.getElementById(`status-value-${id}`).innerText = 1;

                    // Re-attach event listeners to the newly created button
                    addSyncButtonEventListeners();
                } else {
                    // Keep polling until status is 1
                    setTimeout(function() {
                        pollStatus(id, attempts + 1); // Retry with incremented attempts
                    }, 5000); // Poll again after 5 seconds
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }
    });
</script> --}}
<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Attach click event listener to all sync buttons
        addSyncButtonEventListeners(); // Initial attachment of event listeners

        function addSyncButtonEventListeners() {
            document.querySelectorAll('.sync-btn').forEach(function(button) {
                button.addEventListener('click', function() {
                    let id = this.getAttribute('data-id');

                    // Make an AJAX request to sync the status
                    fetch(`/sync/status/${id}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            id: id
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        // If the status was successfully updated, hide the button and show "Imported shortly"
                        if (data.success) {
                            document.getElementById(`status-text-${id}`).innerHTML = "Imported shortly";

                            // Update the status column in the table to reflect the new status (0)
                            document.getElementById(`status-value-${id}`).innerText = 0;

                            // Update the last_update column in the table with the new timestamp
                            document.getElementById(`last-update-${id}`).innerText = data.last_update;

                            // Start polling the server to check if the status has changed back to 1
                            setTimeout(function() {
                                pollStatus(id);
                            }, 5000); // Poll after 5 seconds
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
                });
            });
        }

        function pollStatus(id, attempts = 0) {
            let pollLimit = 12; // Stop polling after 12 attempts (1 minute)
            
            if (attempts >= pollLimit) {
                console.log('Polling stopped after reaching the limit.');
                return;
            }

            fetch(`/sync/status/check/${id}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.status == 1) {
                    // If status is 1, show the sync button again and update the status column
                    document.getElementById(`status-text-${id}`).innerHTML = `
                        <form id="sync-form-${id}" action="/sync/status/${id}" method="POST" style="display: inline;">
                            @csrf
                            <button type="button" class="btn btn-sm btn-warning sync-btn" data-id="${id}" title="Sync Status">
                                <i class="fas fa-sync-alt"></i> Sync Status
                            </button>
                        </form>
                    `;

                    // Update the status column to reflect the new status (1)
                    document.getElementById(`status-value-${id}`).innerText = 1;

                    // Update the last_update column with the new timestamp (if applicable)
                    document.getElementById(`last-update-${id}`).innerText = data.last_update;

                    // Re-attach event listeners to the newly created button
                    addSyncButtonEventListeners();
                } else {
                    // Keep polling until status is 1
                    setTimeout(function() {
                        pollStatus(id, attempts + 1); // Retry with incremented attempts
                    }, 30000); // Poll again after 30 seconds
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }
    });
</script>



