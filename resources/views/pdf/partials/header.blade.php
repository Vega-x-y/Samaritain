{{-- 
    En-tête partagé pour tous les PDFs Samaritain
    Usage: @include('pdf.partials.header', ['title' => 'Devis n° XXX', 'waveBase64' => $waveBase64, 'logoBase64' => $logoBase64])
--}}

<div class="header-wave">
    <img src="{{ $waveBase64 }}" class="header-wave-img" alt="">
    <div class="header-content">
        <div class="document-title">
            {{ $title }}
        </div>
        @if(isset($logoBase64) && $logoBase64)
            <div class="logo-block">
                <img src="{{ $logoBase64 }}" alt="Samaritain">
                <p>VIVEZ SEREINEMENT</p>
            </div>
        @endif
    </div>
</div>
