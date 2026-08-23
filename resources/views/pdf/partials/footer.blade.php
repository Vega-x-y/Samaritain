{{-- 
    Pied de page partagé
    Usage: @include('pdf.partials.footer', ['message' => 'Merci de votre confiance.'])
--}}

<div class="footer">
    <p>Document généré le {{ now()->format('d/m/Y à H:i') }}</p>
    @if(isset($message))
        <p>{{ $message }}</p>
    @endif
</div>
