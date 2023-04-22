@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Tickets</h1>
        <table class="table" id="tickets-table">
            <thead>
            <tr>
                <th>Ticket Number</th>
                <th>Email</th>
                <th>Subject</th>
                <th>Status</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($tickets as $ticket)
                <tr>
                    <td>{{ $ticket->ticket_number }}</td>
                    <td>{{ $ticket->email }}</td>
                    <td>{{ $ticket->subject }}</td>
                    <td>{{ $ticket->status }}</td>
                    <td>{{ $ticket->created_at }}</td>
                    <td>
                        <a href="{{ route('tickets.show', $ticket->id) }}" class="btn btn-primary">View</a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

    </div>

    <script>
        $(document).ready(function() {
            $('#tickets-table').DataTable({
                responsive: true,
                order: [[4, 'desc']]
            });
        });
    </script>
@endsection
