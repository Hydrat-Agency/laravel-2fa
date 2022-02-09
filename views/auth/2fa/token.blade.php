@extends('layout.app')

@section('content')

    @if(session()->has('error'))
        <p class="alert alert-info">
            {!! session()->get('error') !!}
        </p>
    @endif

    <form method="POST" action="{{ route('auth.2fa.store') }}">
        {{ csrf_field() }}
        
        <h1>Two Factor Verification</h1>

        <p class="text-muted">
            For security reasons, you must provide the two factor code sent to your email address
            @if ($reason)
                {{ ' because ' . $reason }}
            @endif
            .
        </p>

        <div class="input-group mb-3">
            <div class="input-group-prepend">
                <span class="input-group-text">
                    <i class="fa fa-lock"></i>
                </span>
            </div>
            
            <input  name="token" 
                    type="text" 
                    class="form-control {{ $errors->has('token') ? 'is-invalid' : '' }}" 
                    required 
                    autofocus 
                    placeholder="Two Factor Code" />

            @if($errors->has('token'))
                <div class="invalid-feedback">
                    {{ $errors->first('token') }}
                </div>
            @endif
        </div>

        <div class="row">
            <div class="col-6">
                <button type="submit" class="btn btn-primary px-4">
                    Verify
                </button>
            </div>
        </div>

        <a class="btn btn-link" href="{{ url('/login') }}">
            Need a new code ?
        </a>
    </form>

@endsection