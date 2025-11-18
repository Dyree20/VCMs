@extends('layouts.app')

@section('title', $team->name . ' - Team Details')

@section('content')
<div class="container-fluid" style="padding: 32px; max-width: 1000px; margin: 0 auto;">
    <!-- Header Section -->
    <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 32px;">
        <a href="{{ route('teams.index') }}" style="display: inline-flex; align-items: center; justify-content: center; width: 40px; height: 40px; background: #f0f0f0; color: #666; border-radius: 8px; text-decoration: none; transition: all 0.3s; font-size: 18px;">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div style="flex: 1;">
            <h1 style="margin: 0; font-size: 28px; font-weight: 700; color: #333;">{{ $team->name }}</h1>
            <p style="margin: 8px 0 0 0; color: #666; font-size: 14px;">Team details and member management</p>
        </div>
        <div style="display: flex; gap: 8px;">
            <a href="{{ route('teams.edit', $team) }}" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px; background: #f0f0f0; color: #666; text-decoration: none; border-radius: 8px; font-weight: 600; transition: all 0.3s;">
                <i class="fa-solid fa-pen-to-square"></i>
                Edit
            </a>
            <form action="{{ route('teams.destroy', $team) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this team?');">
                @csrf
                @method('DELETE')
                <button type="submit" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px; background: #fee; color: #dc3545; border: none; border-radius: 8px; font-weight: 600; transition: all 0.3s; cursor: pointer;">
                    <i class="fa-solid fa-trash"></i>
                    Delete
                </button>
            </form>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 32px;">
        <!-- Team Info Card -->
        <div style="background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); padding: 24px;">
            <h3 style="margin: 0 0 16px 0; font-size: 16px; font-weight: 600; color: #333;">Team Information</h3>
            
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 12px; font-weight: 600; color: #999; margin-bottom: 4px; text-transform: uppercase;">Team Name</label>
                <p style="margin: 0; font-size: 14px; color: #333; font-weight: 500;">{{ $team->name }}</p>
            </div>

            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 12px; font-weight: 600; color: #999; margin-bottom: 4px; text-transform: uppercase;">Description</label>
                <p style="margin: 0; font-size: 14px; color: #333;">{{ $team->description ?? '-' }}</p>
            </div>

            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 12px; font-weight: 600; color: #999; margin-bottom: 4px; text-transform: uppercase;">Notes</label>
                <p style="margin: 0; font-size: 14px; color: #333;">{{ $team->notes ?? '-' }}</p>
            </div>

            <div style="margin-bottom: 0;">
                <label style="display: block; font-size: 12px; font-weight: 600; color: #999; margin-bottom: 4px; text-transform: uppercase;">Created By</label>
                <p style="margin: 0; font-size: 14px; color: #333;">
                    {{ $team->creator->f_name }} {{ $team->creator->l_name }}
                </p>
                <p style="margin: 4px 0 0 0; font-size: 12px; color: #666;">
                    {{ $team->created_at->format('M d, Y \a\t h:i A') }}
                </p>
            </div>
        </div>

        <!-- Team Stats Card -->
        <div style="background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); padding: 24px;">
            <h3 style="margin: 0 0 16px 0; font-size: 16px; font-weight: 600; color: #333;">Team Statistics</h3>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <div style="background: #f0f8ff; border-radius: 8px; padding: 16px; text-align: center;">
                    <div style="font-size: 28px; font-weight: 700; color: #2b58ff;">{{ $team->members->count() }}</div>
                    <div style="font-size: 12px; color: #666; margin-top: 4px;">Total Members</div>
                </div>
                <div style="background: #f0fdf4; border-radius: 8px; padding: 16px; text-align: center;">
                    @php
                        $enforcerCount = $team->members->filter(function($member) {
                            return $member->role_id == 2;
                        })->count();
                    @endphp
                    <div style="font-size: 28px; font-weight: 700; color: #16a34a;">{{ $enforcerCount }}</div>
                    <div style="font-size: 12px; color: #666; margin-top: 4px;">Active Enforcers</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Members Section -->
    <div style="background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); padding: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid #e9ecef;">
            <h3 style="margin: 0; font-size: 16px; font-weight: 600; color: #333;">Team Members</h3>
            <button type="button" onclick="openAddEnforcerModal()" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 16px; background: #2b58ff; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.3s; font-size: 13px;">
                <i class="fa-solid fa-plus"></i>
                Add Member
            </button>
        </div>

        @if($team->members()->count() > 0)
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tbody>
                        @foreach($team->members as $member)
                            <tr style="border-bottom: 1px solid #e9ecef; transition: background 0.2s;">
                                <td style="padding: 16px 0; display: flex; align-items: center; gap: 12px;">
                                    <div style="width: 40px; height: 40px; background: #f0f0f0; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #2b58ff; font-weight: 700; font-size: 14px;">
                                        {{ substr($member->f_name, 0, 1) }}{{ substr($member->l_name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div style="font-weight: 600; color: #333; font-size: 14px;">{{ $member->f_name }} {{ $member->l_name }}</div>
                                        <div style="font-size: 12px; color: #666;">{{ $member->email }}</div>
                                    </div>
                                </td>
                                <td style="padding: 16px 0; text-align: right;">
                                    <span style="display: inline-block; padding: 6px 12px; background: #f0f0f0; color: #666; border-radius: 20px; font-size: 12px; font-weight: 600;">
                                        {{ $member->role->name ?? 'Enforcer' }}
                                    </span>
                                </td>
                                <td style="padding: 16px 0; text-align: right; font-size: 12px; color: #666;">
                                    @php
                                        $assignedAt = $member->pivot->assigned_at ?? null;
                                    @endphp
                                    {{ $assignedAt ? \Carbon\Carbon::parse($assignedAt)->format('M d, Y') : '-' }}
                                </td>
                                <td style="padding: 16px 0; text-align: right;">
                                    <form action="{{ route('teams.remove-enforcer', $team) }}" method="POST" style="display: inline;" onsubmit="return confirm('Remove this member from the team?');">
                                        @csrf
                                        <input type="hidden" name="enforcer_id" value="{{ $member->id }}">
                                        <button type="submit" style="display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; background: #fee; color: #dc3545; border-radius: 6px; border: none; cursor: pointer; transition: all 0.3s; font-size: 14px;" title="Remove">
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div style="padding: 32px 16px; text-align: center;">
                <i style="font-size: 32px; color: #ddd; display: block; margin-bottom: 12px;" class="fa-solid fa-people-group"></i>
                <p style="color: #999; margin: 0;">No members assigned yet. Add enforcers to this team.</p>
            </div>
        @endif
    </div>
</div>

<!-- Add Enforcer Modal -->
<div id="addEnforcerModal" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: none; align-items: center; justify-content: center; z-index: 2000;">
    <div style="background: white; border-radius: 12px; padding: 32px; max-width: 500px; width: 100%; box-shadow: 0 20px 60px rgba(0,0,0,0.3);">
        <h2 style="margin: 0 0 24px 0; font-size: 20px; font-weight: 700; color: #333;">Add Team Member</h2>

        <form action="{{ route('teams.add-enforcer', $team) }}" method="POST">
            @csrf
            
            <label style="display: block; font-weight: 600; color: #333; margin-bottom: 8px; font-size: 14px;">
                Select Enforcer <span style="color: #dc3545;">*</span>
            </label>
            <select name="enforcer_id" style="width: 100%; padding: 12px 16px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; transition: all 0.3s; box-sizing: border-box; margin-bottom: 24px;" required>
                <option value="">-- Select an enforcer --</option>
                @foreach($availableEnforcers as $enforcer)
                    <option value="{{ $enforcer->id }}">{{ $enforcer->f_name }} {{ $enforcer->l_name }} ({{ $enforcer->email }})</option>
                @endforeach
            </select>

            <div style="display: flex; gap: 12px;">
                <button type="submit" style="flex: 1; padding: 12px 24px; background: #2b58ff; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.3s; font-size: 14px;">
                    <i class="fa-solid fa-check" style="margin-right: 8px;"></i>
                    Add Member
                </button>
                <button type="button" onclick="closeAddEnforcerModal()" style="flex: 1; padding: 12px 24px; background: #f0f0f0; color: #666; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.3s; font-size: 14px;">
                    <i class="fa-solid fa-xmark" style="margin-right: 8px;"></i>
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    table tbody tr:hover {
        background: #f9f9f9;
    }

    button:hover {
        transform: translateY(-2px);
        box-shadow: 0 2px 8px rgba(43, 88, 255, 0.2) !important;
    }

    select:focus {
        border-color: #2b58ff;
        box-shadow: 0 0 0 3px rgba(43, 88, 255, 0.1);
    }
</style>

<script>
    function openAddEnforcerModal() {
        document.getElementById('addEnforcerModal').style.display = 'flex';
    }

    function closeAddEnforcerModal() {
        document.getElementById('addEnforcerModal').style.display = 'none';
    }

    document.getElementById('addEnforcerModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeAddEnforcerModal();
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeAddEnforcerModal();
        }
    });
</script>
@endsection
