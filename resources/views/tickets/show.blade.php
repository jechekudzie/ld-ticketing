@extends('layouts.app')

@section('content')

    <style>
        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100%;
        }

        .card {
            max-width: 800px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            background-color: #f2f2f2;
            border-bottom: 1px solid #d9d9d9;
            font-size: 20px;
            font-weight: bold;
        }

        .card-body {
            padding: 20px;
        }

        .font-weight-bold {
            font-weight: bold;
        }

        .media {
            margin-bottom: 20px;
        }

        .media-body {
            border: 1px solid #d9d9d9;
            padding: 10px;
            border-radius: 5px;
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }

        .btn-primary:hover {
            background-color: #0069d9;
            border-color: #0062cc;
        }

        .btn-primary:focus {
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.5);
        }

    </style>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">{{ $ticket->subject }}</div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-3 font-weight-bold">Ticket Number:</div>
                            <div class="col-sm-9">{{ $ticket->ticket_number }}</div>
                        </div>

                        <div class="row">
                            <div class="col-sm-3 font-weight-bold">Email:</div>
                            <div class="col-sm-9">{{ $ticket->email }}</div>
                        </div>

                        <div class="row">
                            <div class="col-sm-3 font-weight-bold">Status:</div>
                            <div class="col-sm-9">{{ $ticket->status }}</div>
                        </div>

                        <div class="row">
                            <div class="col-sm-3 font-weight-bold">Description:</div>
                            <div class="col-sm-9">{!! nl2br(e($ticket->description)) !!}</div>
                            @foreach($ticket->attachments as $attachment)
                                <div class="mt-3">
                                    <a href="{{ asset($attachment->file_path) }}" target="_blank">{{ $attachment->file_name }}</a>
                                </div>
                            @endforeach
                        </div>

                        <hr>

                        <h3>Comments</h3>

                        @foreach($ticket->ticket_comments as $comment)
                            <div class="media mb-3">
                                <div class="media-body">
                                    <h5 class="mt-0">{{ $comment->email }} <small>{{ $comment->created_at->diffForHumans() }}</small></h5>
                                    {!! nl2br(e($comment->message)) !!}
                                    @foreach($comment->attachments as $attachment)
                                        <div class="mt-3">
                                            <a href="{{ asset($attachment->file_path) }}" target="_blank">{{ $attachment->file_name }}</a>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach

                        <hr>

                        <form method="post" action="{{ url('tickets/'. $ticket->id.'/response') }}" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group">
                                <label for="message">Add a comment</label>
                                <textarea class="form-control @error('message') is-invalid @enderror" id="message" name="responseText" rows="3" required></textarea>
                                @error('message')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="attachments">Attachments</label>
                                <input type="file" class="form-control-file @error('attachments.*') is-invalid @enderror" id="attachments" name="attachments[]" multiple>
                                @error('attachments.*')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="d-flex justify-content-center">
                                <button type="submit" class="btn btn-primary btn-lg mt-3">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
