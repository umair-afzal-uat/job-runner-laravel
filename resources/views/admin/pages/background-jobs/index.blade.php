@extends('admin.layouts.app')
@section('content')
<style>

</style>
<div class="container">
    <h1 >Background Jobs</h1>
    <div class="table-responsive">
        <table class="table" >
            <thead style="">
                <tr>
                    <th >ID</th>
                    <th style="text-align: left;">Class</th>
                    <th >Method</th>
                    <th >Status</th>
                    <th >Attempts</th>
                    <th >Priority</th>
                    <th >Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($jobs as $job)
                <tr>
                    <td class="text-center">{{ $job->id }}</td>
                    <td >{{ $job->class_name }}</td>
                    <td class="text-center">{{ $job->method }}</td>
                    <td class="text-center">{{ $job->status }}</td>
                    <td class="text-center">{{ $job->current_attempt }} / {{ $job->retry_attempts }}</td>
                    <td class="text-center">{{ $job->priority }}</td>
                    <td class="text-center">
                        @if ($job->status === 'pending')
                        <form action="/admin/background-jobs/cancel/{{ $job->id }}" style=" text-align:center;" method="POST">
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
</div>
@endsection