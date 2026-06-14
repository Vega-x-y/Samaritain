@extends('layouts.base')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">{{ __('Vérifiez votre adresse email') }}</div>

                    <div class="card-body">
                        @if (session('status') == 'verification-link-sent')
                            <div class="alert alert-success" role="alert">
                                {{ __('Un nouveau lien de vérification a été envoyé à votre adresse email.') }}
                            </div>
                        @endif

                        <p>{{ __('Avant de continuer, veuillez vérifier votre email pour un lien de vérification.') }}</p>
                        <p>{{ __('Si vous n\'avez pas reçu l\'email') }}</p>

                        <form method="POST" action="{{ route('verification.send') }}">
                            @csrf
                            <button type="submit" class="btn btn-primary">
                                {{ __('Renvoiyer l\'email de vérification') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
