@extends('components.app')

@section('content')
    <style>
        body {
            background: linear-gradient(135deg, #38ef7d, #11998e);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 15px;
        }

        .reset-card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
        }

        .reset-card .card-body {
            padding: 2rem;
        }

        .reset-card h3 {
            font-weight: 600;
            color: #11998e;
        }

        .form-control {
            border-radius: 10px;
            padding: 12px;
        }

        .btn-success {
            background: #11998e;
            border: none;
            border-radius: 10px;
            font-weight: 500;
            padding: 12px;
            transition: all 0.3s ease;
        }

        .btn-success:hover {
            background: #0c7b6f;
            transform: translateY(-2px);
        }

        /* Mobile optimization */
        @media (max-width: 576px) {
            .reset-card {
                width: 100%;
            }
        }
    </style>

    <div class="container py-5">
        <div class="card shadow-lg reset-card mx-auto w-100" style="max-width: 450px;">
            <div class="card-body">
                <h3 class="text-center mb-4">🔒 Reset Password</h3>

                @if (session('status'))
                    <div class="alert alert-success text-center">{{ session('status') }}</div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>⚠️ {{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('reset.password.form') }}">
                    @csrf

                    <!-- Token -->
                    <input type="hidden" name="token" value="{{ $token }}">

                    <!-- Email -->
                    <div class="mb-3">
                        <label for="email" class="form-label">📧 Alamat Email</label>
                        <input type="email" id="email" name="email" class="form-control"
                            value="{{ old('email', $email ?? '') }}" readonly>
                    </div>

                    <!-- Password Baru -->
                    <div class="mb-3">
                        <label for="password" class="form-label">🔑 Password Baru</label>
                        <input type="password" id="password" name="password"
                            class="form-control @error('password') is-invalid @enderror" required>
                    </div>

                    <!-- Konfirmasi Password -->
                    <div class="mb-4">
                        <label for="password_confirmation" class="form-label">✅ Konfirmasi Password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control"
                            required>
                    </div>

                    <button type="submit" class="btn btn-success w-100">🚀 Reset Password</button>
                </form>
            </div>
        </div>
    </div>
@endsection
