{{-- 
    Bloc d'informations client partagé
    Usage: @include('pdf.partials.client-info', ['client' => $client, 'artisan' => $artisan])
--}}

<div class="client-block">
    <h3>Artisan : {{ $artisan->business_name ?? 'N/A' }}</h3>
    <p>{{ $artisan->profession ?? '' }} — {{ $artisan->city ?? '' }}</p>
    <p>{{ $artisan->phone ?? '' }}</p>

    <h3 style="margin-top: 15px;">Client : {{ $client->nom ?? 'N/A' }}</h3>
    <p>{{ $client->email ?? '' }}</p>
    <p>{{ $client->telephone ?? '' }}</p>
    <p>Type : {{ $client->type?->label() ?? 'N/A' }}</p>
</div>
