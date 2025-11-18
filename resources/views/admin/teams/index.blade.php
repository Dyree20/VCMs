@extends('layouts.app')

@section('title', 'Teams Management')

@section('content')
<div class="container-fluid" style="padding: 32px;">
    <!-- Header Section -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px;">
        <div>
            <h1 style="margin: 0; font-size: 28px; font-weight: 700; color: #333;">Teams</h1>
            <p style="margin: 8px 0 0 0; color: #666; font-size: 14px;">Manage enforcer teams and assignments</p>
        </div>
        <a href="{{ route('teams.create') }}" style="display: inline-flex; align-items: center; gap: 8px; padding: 12px 24px; background: #2b58ff; color: white; text-decoration: none; border-radius: 8px; font-weight: 600; transition: all 0.3s; box-shadow: 0 2px 8px rgba(43, 88, 255, 0.2);">
            <i class="fa-solid fa-plus" style="font-size: 16px;"></i>
            Create Team
        </a>
    </div>

    <!-- Teams Table -->
    @if($teams->count() > 0)
        <div style="background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f8f9fa; border-bottom: 1px solid #e9ecef;">
                        <th style="padding: 16px 20px; text-align: left; font-weight: 600; color: #333; font-size: 14px;">Team Name</th>
                        <th style="padding: 16px 20px; text-align: left; font-weight: 600; color: #333; font-size: 14px;">Description</th>
                        <th style="padding: 16px 20px; text-align: center; font-weight: 600; color: #333; font-size: 14px;">Members</th>
                        <th style="padding: 16px 20px; text-align: left; font-weight: 600; color: #333; font-size: 14px;">Created By</th>
                        <th style="padding: 16px 20px; text-align: left; font-weight: 600; color: #333; font-size: 14px;">Created Date</th>
                        <th style="padding: 16px 20px; text-align: center; font-weight: 600; color: #333; font-size: 14px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($teams as $team)
                        <tr style="border-bottom: 1px solid #e9ecef; transition: background 0.2s;">
                            <td style="padding: 16px 20px; color: #333; font-weight: 600;">
                                <a href="{{ route('teams.show', $team) }}" style="color: #2b58ff; text-decoration: none; transition: all 0.3s;">
                                    {{ $team->name }}
                                </a>
                            </td>
                            <td style="padding: 16px 20px; color: #666; font-size: 14px;">
                                {{ $team->description ?? '-' }}
                            </td>
                            <td style="padding: 16px 20px; text-align: center;">
                                <span style="display: inline-block; padding: 6px 12px; background: #e3f2fd; color: #2b58ff; border-radius: 20px; font-size: 13px; font-weight: 600;">
                                    {{ $team->members_count ?? $team->members()->count() }}
                                </span>
                            </td>
                            <td style="padding: 16px 20px; color: #666; font-size: 14px;">
                                {{ $team->creator->f_name ?? 'N/A' }} {{ $team->creator->l_name ?? '' }}
                            </td>
                            <td style="padding: 16px 20px; color: #666; font-size: 14px;">
                                {{ $team->created_at->format('M d, Y') }}
                            </td>
                            <td style="padding: 16px 20px; text-align: center;">
                                <div style="display: flex; gap: 8px; justify-content: center;">
                                    <a href="{{ route('teams.show', $team) }}" style="display: inline-flex; align-items: center; justify-content: center; width: 36px; height: 36px; background: #f0f0f0; color: #2b58ff; border-radius: 6px; text-decoration: none; transition: all 0.3s; font-size: 16px;" title="View">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    <a href="{{ route('teams.edit', $team) }}" style="display: inline-flex; align-items: center; justify-content: center; width: 36px; height: 36px; background: #f0f0f0; color: #666; border-radius: 6px; text-decoration: none; transition: all 0.3s; font-size: 16px;" title="Edit">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <form action="{{ route('teams.destroy', $team) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this team?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" style="display: inline-flex; align-items: center; justify-content: center; width: 36px; height: 36px; background: #f0f0f0; color: #dc3545; border-radius: 6px; border: none; cursor: pointer; transition: all 0.3s; font-size: 16px;" title="Delete">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($teams->hasPages())
            <div style="margin-top: 24px; display: flex; justify-content: center;">
                {{ $teams->links() }}
            </div>
        @endif
    @else
        <!-- Empty State -->
        <div style="background: white; border-radius: 12px; padding: 60px 32px; text-align: center; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <i style="font-size: 48px; color: #ddd; display: block; margin-bottom: 16px;" class="fa-solid fa-people-group"></i>
            <h3 style="margin: 0 0 8px 0; color: #666; font-size: 20px; font-weight: 600;">No Teams Yet</h3>
            <p style="color: #999; font-size: 14px; margin: 0;">Create your first team to get started.</p>
        </div>
    @endif
</div>

<style>
    .container-fluid {
        max-width: 1400px;
        margin: 0 auto;
    }

    table tbody tr:hover {
        background: #f9f9f9;
    }

    a[style*="inline-flex"] {
        cursor: pointer;
    }

    a[style*="inline-flex"]:hover {
        background-color: #e3f2fd !important;
        color: #2b58ff !important;
    }

    .pagination {
        gap: 8px;
    }

    .pagination a, .pagination span {
        padding: 8px 12px;
        border-radius: 6px;
        border: 1px solid #ddd;
        color: #2b58ff;
        text-decoration: none;
        transition: all 0.3s;
    }

    .pagination a:hover {
        background: #e3f2fd;
        border-color: #2b58ff;
    }

    .pagination .active span {
        background: #2b58ff;
        color: white;
        border-color: #2b58ff;
    }
</style>
@endsection
