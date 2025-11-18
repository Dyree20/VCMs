@extends('layouts.app')

@section('title', 'Users Dashboard')

@section('content')
<div>

    <!-- Reports Section -->
    <div class="user-reports">
        <div class="card">
            <i class="fa-solid fa-users"></i>
            <p>Total Users</p>
            <h2 id="totalUsers">{{ $totalUsers }}</h2>
        </div>
        <div class="card">
            <i class="fa-solid fa-user-check"></i>
            <p>Active Users</p>
            <h2 id="activeUsers">{{ $activeUsers }}</h2>
        </div>
        <div class="card">
            <i class="fa-solid fa-hourglass-half"></i>
            <p>Pending Users</p>
            <h2 id="pendingUsers">{{ $pendingUsers }}</h2>
        </div>
        <div class="card">
            <i class="fa-solid fa-user-slash"></i>
            <p>Inactive Users</p>
            <h2 id="inactiveUsers">{{ $inactiveUsers }}</h2>
        </div>
    </div>

    
    <div class="userTable-container">
        <h2 class="page-title">User Management</h2>
        <!-- Filters -->
        <div class="filters">
            <input type="text" id="searchInput" placeholder="Search users...">
            <select id="statusFilter">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="pending">Pending</option>
                <option value="inactive">Inactive</option>
            </select>
            <select id="roleFilter">
                <option value="">All Roles</option>
                <option value="admin">Admin</option>
                <option value="enforcer">Enforcer</option>
                <option value="front desk">Front Desk</option>
            </select>
        </div>

        <!-- User Table -->
        <table id="userTable">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Username</th>
                    <th>User ID</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Account Status</th>
                    <th>Actions</th>
                    <th>Assigned Area</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $index => $user)
                @php
                    $status = strtolower($user->status->status ?? 'unknown');
                    $roleName = strtolower($user->role->name ?? '');
                    // Map status for filter: approved = active, suspended = inactive
                    $filterStatus = match($status) {
                        'approved' => 'active',
                        'suspended' => 'inactive',
                        'pending' => 'pending',
                        default => '',
                    };
                @endphp
                <tr data-user-id="{{ $user->id }}" 
                    data-name="{{ strtolower($user->f_name . ' ' . $user->l_name) }}"
                    data-username="{{ strtolower($user->username) }}"
                    data-email="{{ strtolower($user->email) }}"
                    data-user-id-code="{{ strtolower($user->enforcer_id ?? 'n/a') }}"
                    data-role="{{ $roleName }}"
                    data-status="{{ $filterStatus }}">
                    <td class="user-profile">
                        <img src="{{ $user->details && $user->details->photo ? asset('storage/' . $user->details->photo) : asset('images/default-avatar.png') }}" 
                            alt="{{ $user->f_name }} {{ $user->l_name }}">
                        <span>{{ $user->f_name }} {{ $user->l_name }}</span>
                    </td>

                    <td>{{ $user->username }}</td>
                    <td><code style="background: #f5f5f5; padding: 4px 8px; border-radius: 4px; font-weight: 600; color: #007bff;">{{ $user->enforcer_id ?? 'N/A' }}</code></td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->phone ?? '—' }}</td>
                    <td>{{ $user->role->name ?? '—' }}</td>
                    <td>
                        @php
                            // map database statuses to CSS classes
                            $statusClass = match($status) {
                                'approved' => 'active',
                                'pending' => 'probation',
                                'suspended' => 'inactive',
                                default => '',
                            };
                        @endphp
                        <span class="status {{ $statusClass }}">
                            {{ ucfirst($status) }}
                        </span>
                    </td>

                    <td class="account-status-cell">
                        @php
                            $isActive = strtolower($user->status->status ?? '') === 'approved';
                            $statusText = $isActive ? 'Active' : 'Inactive';
                            $statusClass = $isActive ? 'account-active' : 'account-inactive';
                        @endphp
                        <span class="account-status-badge {{ $statusClass }}" data-user-id="{{ $user->id }}">
                            {{ $statusText }}
                        </span>
                        <button class="toggle-account-btn {{ $isActive ? 'active' : '' }}" 
                                data-user-id="{{ $user->id }}" 
                                data-current-status="{{ strtolower($user->status->status ?? '') }}"
                                title="{{ $isActive ? 'Deactivate Account' : 'Activate Account' }}">
                            <i class="fa-solid {{ $isActive ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>
                        </button>
                    </td>

                    <td class="actions">
                        <button class="view-btn">View</button>
                        @if(strtolower($user->status->status ?? '') === 'pending')
                            <button class="approve-btn">Approve</button>
                            <button class="reject-btn">Reject</button>
                        @endif
                    </td>

                    <td class="assigned-area-cell">
                        @if(strtolower($user->role->name ?? '') === 'enforcer')
                            <span class="assigned-area-display" data-user-id="{{ $user->id }}">
                                {{ $user->assigned_area ?? 'Not Assigned' }}
                            </span>
                            <button class="assign-area-btn" data-user-id="{{ $user->id }}" data-current-area="{{ $user->assigned_area ?? '' }}" title="Assign/Edit Area">
                                <i class="fa-solid fa-map-location-dot"></i>
                            </button>
                        @else
                            <span style="color: #999;">—</span>
                        @endif
                    </td>
                </tr>
                @endforeach

            </tbody>
        </table>
    </div>
</div>

@endsection

<!-- Area Assignment Modal -->
<div id="areaAssignmentModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: white; padding: 25px; border-radius: 12px; max-width: 400px; width: 90%; box-shadow: 0 4px 20px rgba(0,0,0,0.3);">
        <h3 style="margin-top: 0; margin-bottom: 20px; color: #333;">Assign Area</h3>
        <form id="assignAreaForm">
            <input type="hidden" id="assignAreaUserId" name="user_id">
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #555;">Area Name:</label>
                <input type="text" id="assignAreaInput" name="assigned_area" 
                       placeholder="e.g., Main Street, Downtown, Market Area" 
                       style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;">
            </div>
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" id="cancelAssignArea" style="padding: 10px 20px; background: #6c757d; color: white; border: none; border-radius: 6px; cursor: pointer;">Cancel</button>
                <button type="submit" style="padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 6px; cursor: pointer;">Save</button>
            </div>
        </form>
    </div>
</div>

<script src="{{ asset('js/user-actions.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('areaAssignmentModal');
    const form = document.getElementById('assignAreaForm');
    const cancelBtn = document.getElementById('cancelAssignArea');
    const assignAreaBtns = document.querySelectorAll('.assign-area-btn');

    // Open modal when assign area button is clicked
    assignAreaBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const userId = this.getAttribute('data-user-id');
            const currentArea = this.getAttribute('data-current-area') || '';
            
            document.getElementById('assignAreaUserId').value = userId;
            document.getElementById('assignAreaInput').value = currentArea;
            modal.style.display = 'flex';
        });
    });

    // Close modal
    cancelBtn.addEventListener('click', function() {
        modal.style.display = 'none';
    });

    // Close modal when clicking outside
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    });

    // Handle form submission
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const userId = document.getElementById('assignAreaUserId').value;
        const assignedArea = document.getElementById('assignAreaInput').value;
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        try {
            const response = await fetch(`/users/${userId}/assign-area`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    assigned_area: assignedArea
                })
            });

            const data = await response.json();

            if (data.success) {
                // Update the display
                const displayElement = document.querySelector(`.assigned-area-display[data-user-id="${userId}"]`);
                const btnElement = document.querySelector(`.assign-area-btn[data-user-id="${userId}"]`);
                
                if (displayElement) {
                    displayElement.textContent = data.assigned_area || 'Not Assigned';
                }
                if (btnElement) {
                    btnElement.setAttribute('data-current-area', data.assigned_area || '');
                }

                modal.style.display = 'none';
                alert('Area assigned successfully!');
            } else {
                alert(data.message || 'Failed to assign area');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred while assigning area');
        }
    });

    // Toggle Account Status
    const toggleAccountBtns = document.querySelectorAll('.toggle-account-btn');
    
    toggleAccountBtns.forEach(btn => {
        btn.addEventListener('click', async function() {
            const userId = this.getAttribute('data-user-id');
            const currentStatus = this.getAttribute('data-current-status');
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            // Determine new status
            const isCurrentlyActive = currentStatus === 'approved';
            const newStatus = isCurrentlyActive ? 'suspended' : 'approved';
            const action = isCurrentlyActive ? 'deactivate' : 'activate';
            
            if (!confirm(`Are you sure you want to ${action} this account?`)) {
                return;
            }

            try {
                const response = await fetch(`/users/${userId}/toggle-status`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        status: newStatus
                    })
                });

                // Check if response is ok
                if (!response.ok) {
                    const errorData = await response.json().catch(() => ({ message: 'Server error occurred' }));
                    throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
                }

                const data = await response.json();

                if (data.success) {
                    // Update the badge
                    const badgeElement = document.querySelector(`.account-status-badge[data-user-id="${userId}"]`);
                    const btnElement = document.querySelector(`.toggle-account-btn[data-user-id="${userId}"]`);
                    const iconElement = btnElement.querySelector('i');
                    
                    if (data.status === 'approved') {
                        badgeElement.textContent = 'Active';
                        badgeElement.className = 'account-status-badge account-active';
                        badgeElement.setAttribute('data-user-id', userId);
                        iconElement.className = 'fa-solid fa-toggle-on';
                        btnElement.classList.add('active');
                        btnElement.setAttribute('title', 'Deactivate Account');
                    } else {
                        badgeElement.textContent = 'Inactive';
                        badgeElement.className = 'account-status-badge account-inactive';
                        badgeElement.setAttribute('data-user-id', userId);
                        iconElement.className = 'fa-solid fa-toggle-off';
                        btnElement.classList.remove('active');
                        btnElement.setAttribute('title', 'Activate Account');
                    }
                    
                    btnElement.setAttribute('data-current-status', data.status.toLowerCase());
                    
                    // Update status column as well
                    const statusCell = btnElement.closest('tr').querySelector('td:nth-child(7)');
                    if (statusCell) {
                        const statusSpan = statusCell.querySelector('.status');
                        if (statusSpan) {
                            statusSpan.textContent = data.status.charAt(0).toUpperCase() + data.status.slice(1);
                            statusSpan.className = `status ${data.status === 'approved' ? 'active' : (data.status === 'pending' ? 'probation' : 'inactive')}`;
                        }
                    }

                    alert(`Account ${action}d successfully!`);
                    
                    // Reload page to refresh all data
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    alert(data.message || 'Failed to update account status');
                }
            } catch (error) {
                console.error('Error:', error);
                alert(error.message || 'An error occurred while updating account status');
            }
        });
    });

    // Search and Filter Functionality
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const roleFilter = document.getElementById('roleFilter');
    const tableBody = document.querySelector('#userTable tbody');
    const allRows = Array.from(tableBody.querySelectorAll('tr'));

    function filterTable() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        const statusValue = statusFilter.value.toLowerCase();
        const roleValue = roleFilter.value.toLowerCase();

        allRows.forEach(row => {
            const name = row.getAttribute('data-name') || '';
            const username = row.getAttribute('data-username') || '';
            const email = row.getAttribute('data-email') || '';
            const userIdCode = row.getAttribute('data-user-id-code') || '';
            const role = row.getAttribute('data-role') || '';
            const status = row.getAttribute('data-status') || '';

            // Search filter - check if search term matches any field
            const matchesSearch = !searchTerm || 
                name.includes(searchTerm) ||
                username.includes(searchTerm) ||
                email.includes(searchTerm) ||
                userIdCode.includes(searchTerm);

            // Status filter
            const matchesStatus = !statusValue || status === statusValue;

            // Role filter
            const matchesRole = !roleValue || role === roleValue;

            // Show row if all filters match
            if (matchesSearch && matchesStatus && matchesRole) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });

        // Update "No results" message if needed
        updateNoResultsMessage();
    }

    function updateNoResultsMessage() {
        const visibleRows = allRows.filter(row => {
            const display = row.style.display;
            return !display || display === '' || display === 'table-row';
        });
        let noResultsMsg = document.getElementById('noResultsMessage');
        
        if (visibleRows.length === 0 && allRows.length > 0) {
            if (!noResultsMsg) {
                noResultsMsg = document.createElement('tr');
                noResultsMsg.id = 'noResultsMessage';
                noResultsMsg.innerHTML = `
                    <td colspan="10" style="text-align: center; padding: 40px; color: #999; font-size: 16px;">
                        <i class="fa-solid fa-search" style="font-size: 48px; margin-bottom: 15px; display: block; opacity: 0.5;"></i>
                        No users found matching your search criteria.
                    </td>
                `;
                tableBody.appendChild(noResultsMsg);
            }
        } else {
            if (noResultsMsg) {
                noResultsMsg.remove();
            }
        }
    }

    // Add event listeners
    searchInput.addEventListener('input', filterTable);
    statusFilter.addEventListener('change', filterTable);
    roleFilter.addEventListener('change', filterTable);
});
</script>