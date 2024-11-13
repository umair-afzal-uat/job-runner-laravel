@extends('admin.layouts.app')

@section('content')
<div class="container">
    <h1>Background Jobs</h1>
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Class</th>
                <th>Method</th>
                <th>Status</th>
                <th>Attempts</th>
                <th>Priority</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($jobs as $job)
            <tr>
                <td>{{ $job->id }}</td>
                <td>{{ $job->class_name }}</td>
                <td>{{ $job->method }}</td>
                <td>{{ $job->status }}</td>
                <td>{{ $job->current_attempt }} / {{ $job->retry_attempts }}</td>
                <td>{{ $job->priority }}</td>
                <td>
                    @if ($job->status === 'pending')
                    <form action="/admin/background-jobs/cancel/{{ $job->id }}" method="POST">
                        @csrf
                        <button class="btn btn-danger btn-sm">Cancel</button>
                    </form>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
