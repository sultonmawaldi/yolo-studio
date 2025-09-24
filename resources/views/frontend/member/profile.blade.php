@extends('layouts.app')

@section('title', 'Member Profile')

@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header">
            <h4>My Profile</h4>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <tr>
                    <th>Unique Member ID</th>
                    <td>{{ auth()->user()->role_uid }}</td>
                </tr>
                <tr>
                    <th>Name</th>
                    <td>{{ auth()->user()->name }}</td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td>{{ auth()->user()->email }}</td>
                </tr>
                <tr>
                    <th>Phone</th>
                    <td>{{ auth()->user()->phone ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Joined At</th>
                    <td>{{ auth()->user()->created_at->format('d M Y H:i') }}</td>
                </tr>
            </table>
            <a href="{{ route('member.dashboard') }}" class="btn btn-secondary">⬅ Back to Dashboard</a>
        </div>
    </div>
</div>
@endsection
