@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Join a Class</h1>
    <form action="{{ route('student.joinClass', $invitationCode) }}" method="GET">
        @csrf
        <div class="mb-3">
            <label for="invitationCode" class="form-label">Invitation Code</label>
            <input type="text" name="invitationCode" id="invitationCode" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Join Class</button>
    </form>
</div>
@endsection
