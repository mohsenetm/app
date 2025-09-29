@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Dashboard</h4>
                    </div>
                    <div class="card-body">
                        <h5>Welcome, {{ Auth::user()->name }}!</h5>
                        <p class="text-muted">You are successfully logged in.</p>

                        <div class="mt-4">
                            <h6>Your Account Information:</h6>
                            <ul class="list-unstyled">
                                <li><strong>Name:</strong> {{ Auth::user()->name }}</li>
                                <li><strong>Email:</strong> {{ Auth::user()->email }}</li>
                                <li><strong>Member Since:</strong> {{ Auth::user()->created_at->format('F d, Y') }}</li>
                            </ul>
                        </div>

                        <div class="mt-4">
                            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-danger">Logout</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
