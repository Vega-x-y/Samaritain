@extends('layouts.base')

@section('title', $property->title)

@section('content')
    <style>
        :root {
            --cream: #FAFAF8;
            --ink: #1A1A18;
            --gold: #C9A96E;
            --gold-lt: #F0E6D3;
            --muted: #6B6B66;
            --border: #E8E6E1;
        }

        body {
            background: var(--cream);
            color: var(--ink);
        }

        /* ── Typography ── */
        .font-display {
            font-family: 'Playfair Display', Georgia, serif;
        }

        .font-body {
            font-family: 'Inter', system-ui, sans-serif;
        }

        /* ── Status badge ── */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            font-family: 'Inter', sans-serif;
            font-size: .7rem;
            font-weight: 600;
            letter-spacing: .08em;
            text-transform: uppercase;
            padding: .35rem .9rem;
            border-radius: 999px;
            border: 1px solid currentColor;
        }

        .status-badge.available {
            color: #3a7d44;
            background: #e6f4ea;
            border-color: #b7dfc0;
        }

        .status-badge.rented {
            color: #b45309;
            background: #fef3c7;
            border-color: #fcd34d;
        }

        .status-badge.sold {
            color: #991b1b;
            background: #fee2e2;
            border-color: #fca5a5;
        }

        /* ── Gallery ── */
        .gallery-main {
            position: relative;
            border-radius: 1rem;
            overflow: hidden;
            background: var(--border);
        }

        .gallery-main img {
            width: 100%;
            height: 480px;
            object-fit: cover;
            display: block;
            transition: transform .6s ease;
        }

        .gallery-main:hover img {
            transform: scale(1.02);
        }

        .gallery-thumb {
            border-radius: .6rem;
            overflow: hidden;
            cursor: pointer;
            border: 2px solid transparent;
            transition: border-color .2s, opacity .2s;
            aspect-ratio: 4/3;
            background: var(--border);
        }

        .gallery-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .gallery-thumb:hover {
            border-color: var(--gold);
            opacity: .9;
        }

        .gallery-thumb.active {
            border-color: var(--gold);
        }

        /* ── Price badge ── */
        .price-badge {
            position: absolute;
            bottom: 1.25rem;
            left: 1.25rem;
            background: var(--ink);
            color: #fff;
            font-family: 'Playfair Display', serif;
            font-size: 1.4rem;
            font-weight: 700;
            padding: .7rem 1.4rem;
            border-radius: .75rem;
            box-shadow: 0 8px 30px rgba(0, 0, 0, .25);
            display: flex;
            align-items: baseline;
            gap: .35rem;
        }

        .price-badge span {
            font-family: 'Inter', sans-serif;
            font-size: .75rem;
            font-weight: 400;
            color: #aaa;
        }

        /* ── Feature strip ── */
        .feature-strip {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 0;
            border: 1px solid var(--border);
            border-radius: 1rem;
            overflow: hidden;
            background: #fff;
        }

        .feature-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: .35rem;
            padding: 1.25rem 1rem;
            border-right: 1px solid var(--border);
            text-align: center;
        }

        .feature-item:last-child {
            border-right: none;
        }

        .feature-item svg {
            color: var(--gold);
            width: 1.25rem;
            height: 1.25rem;
        }

        .feature-item .fi-value {
            font-family: 'Playfair Display', serif;
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--ink);
        }

        .feature-item .fi-label {
            font-family: 'Inter', sans-serif;
            font-size: .7rem;
            font-weight: 500;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: .06em;
        }

        /* ── Section title ── */
        .section-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.35rem;
            font-weight: 600;
            color: var(--ink);
            padding-bottom: .6rem;
            border-bottom: 2px solid var(--gold-lt);
            margin-bottom: 1.25rem;
        }

        /* ── Description ── */
        .description-text {
            font-family: 'Inter', sans-serif;
            font-size: .95rem;
            line-height: 1.8;
            color: #444;
        }

        /* ── Amenity chips ── */
        .amenity-chip {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            font-family: 'Inter', sans-serif;
            font-size: .8rem;
            font-weight: 500;
            color: var(--ink);
            background: var(--cream);
            border: 1px solid var(--border);
            padding: .45rem .9rem;
            border-radius: .5rem;
            transition: background .2s, border-color .2s;
        }

        .amenity-chip:hover {
            background: var(--gold-lt);
            border-color: var(--gold);
        }

        .amenity-chip svg {
            width: .9rem;
            height: .9rem;
            color: var(--gold);
        }

        /* ── Contact card (sticky) ── */
        .contact-card {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 1.25rem;
            padding: 2rem;
            box-shadow: 0 4px 40px rgba(0, 0, 0, .06);
        }

        .contact-card .price-display {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            font-weight: 700;
            color: var(--ink);
        }

        .contact-card .price-display sub {
            font-family: 'Inter', sans-serif;
            font-size: .8rem;
            font-weight: 400;
            color: var(--muted);
            vertical-align: middle;
            margin-left: .2rem;
        }

        .btn-primary {
            width: 100%;
            display: block;
            text-align: center;
            background: var(--ink);
            color: #fff;
            font-family: 'Inter', sans-serif;
            font-size: .85rem;
            font-weight: 600;
            letter-spacing: .04em;
            padding: .9rem 1.5rem;
            border-radius: .75rem;
            border: 2px solid var(--ink);
            cursor: pointer;
            transition: background .2s, color .2s;
            text-decoration: none;
        }

        .btn-primary:hover {
            background: transparent;
            color: var(--ink);
        }

        .btn-outline {
            width: 100%;
            display: block;
            text-align: center;
            background: transparent;
            color: var(--ink);
            font-family: 'Inter', sans-serif;
            font-size: .85rem;
            font-weight: 600;
            letter-spacing: .04em;
            padding: .9rem 1.5rem;
            border-radius: .75rem;
            border: 2px solid var(--border);
            cursor: pointer;
            transition: border-color .2s;
            text-decoration: none;
        }

        .btn-outline:hover {
            border-color: var(--gold);
        }

        /* ── Agent mini-card ── */
        .agent-row {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding-top: 1.25rem;
            border-top: 1px solid var(--border);
        }

        .agent-avatar {
            width: 2.75rem;
            height: 2.75rem;
            border-radius: 50%;
            background: var(--gold-lt);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            overflow: hidden;
        }

        .agent-avatar svg {
            width: 1.25rem;
            height: 1.25rem;
            color: var(--gold);
        }

        .agent-name {
            font-family: 'Inter', sans-serif;
            font-size: .85rem;
            font-weight: 600;
            color: var(--ink);
        }

        .agent-role {
            font-family: 'Inter', sans-serif;
            font-size: .75rem;
            color: var(--muted);
        }

        /* ── Map placeholder ── */
        .map-placeholder {
            width: 100%;
            height: 160px;
            background: linear-gradient(135deg, var(--border) 25%, #e0ddd7 100%);
            border-radius: .75rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            color: var(--muted);
            font-family: 'Inter', sans-serif;
            font-size: .8rem;
            border: 1px solid var(--border);
        }

        .map-placeholder svg {
            width: 1.5rem;
            height: 1.5rem;
            color: var(--gold);
        }

        /* ── Breadcrumb ── */
        .breadcrumb {
            display: flex;
            align-items: center;
            gap: .5rem;
            font-family: 'Inter', sans-serif;
            font-size: .8rem;
            color: var(--muted);
            margin-bottom: 1.5rem;
        }

        .breadcrumb a {
            color: var(--muted);
            text-decoration: none;
        }

        .breadcrumb a:hover {
            color: var(--gold);
        }

        .breadcrumb svg {
            width: .75rem;
            height: .75rem;
        }

        /* ── Divider ── */
        .gold-divider {
            width: 3rem;
            height: 2px;
            background: var(--gold);
            border-radius: 99px;
            margin: 1.25rem 0;
        }

        /* ── Responsive ── */
        @media (max-width: 1024px) {
            .layout-grid {
                display: flex;
                flex-direction: column;
            }

            .sidebar-col {
                position: static !important;
            }
        }

        @media (max-width: 640px) {
            .gallery-main img {
                height: 280px;
            }

            .feature-item {
                padding: 1rem .75rem;
            }
        }
    </style>
    <div class="font-body" style="min-height:100vh; padding: 2rem 0 4rem;">
        <x-blade-components::layout.container>

            {{-- Breadcrumb ── ── ── ── ── ── ──  --}}
            <div class="breadcrumb">
                <a href="{{ route('index') }}">Accueil</a>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2">
                    <polyline points="9 18 15 12 9 6" />
                </svg>
                <a href="{{ route('property.index') }}">Propriétés</a>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2">
                    <polyline points="9 18 15 12 9 6" />
                </svg>
                <span style="color: var(--ink);">{{ $property->title }}</span>
            </div>

            {{-- Header ── ── ── ── ── ── ── ──  --}}
            <div style="margin-bottom: 1.75rem;">
                <div style="display:flex; align-items:flex-start; justify-content:space-between; flex-wrap:wrap; gap:1rem;">
                    <div>
                        <div style="display:flex; align-items:center; gap:.75rem; margin-bottom:.75rem;">
                            @php
                                $statusMap = ['disponible' => 'available', 'loué' => 'rented', 'vendu' => 'sold'];
                                $statusClass = $statusMap[strtolower($property->status->value)] ?? 'available';
                            @endphp
                            <span class="status-badge {{ $statusClass }}">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                    style="width:.6rem;height:.6rem;">
                                    <circle cx="12" cy="12" r="10" />
                                </svg>
                                {{ $property->status }}
                            </span>

                            @if ($property->type)
                                <span
                                    style="font-family:'Inter',sans-serif; font-size:.75rem; font-weight:500; color:var(--muted); text-transform:uppercase; letter-spacing:.07em;">
                                    {{ $property->type }}
                                </span>
                            @endif
                        </div>

                        <h1 class="font-display"
                            style="font-size:clamp(1.8rem,4vw,3rem); font-weight:700; line-height:1.15; color:var(--ink); max-width:32ch;">
                            {{ $property->title }}
                        </h1>

                        <div
                            style="display:flex; align-items:center; gap:.5rem; margin-top:.75rem; font-family:'Inter',sans-serif; font-size:.85rem; color:var(--muted);">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" style="width:1rem;height:1rem;color:var(--gold);">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z" />
                                <circle cx="12" cy="10" r="3" />
                            </svg>
                            {{ $property->address ?? '' }}{{ $property->address && $property->city ? ', ' : '' }}{{ $property->city->name ?? 'Brazzaville' }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Main layout ── ── ── ── ── ── ── ──  --}}
            <div class="layout-grid" style="display:grid; grid-template-columns:1fr 340px; gap:2.5rem; align-items:start;">

                {{-- LEFT COLUMN ── ── ── ── ── ──  --}}
                <div style="min-width:0;">

                    {{-- Gallery --}}
                    <div style="display:grid; grid-template-columns:1fr 100px; gap:.75rem; margin-bottom:2rem;"
                        id="gallery">

                        {{-- Main image --}}
                        <div class="gallery-main">
                            <img id="main-img" src="{{ $property->images->first()?->image_url }}"
                                alt="{{ $property->title }}">
                            @if ($property->price)
                                <div class="price-badge">
                                    {{ number_format($property->price, 0, ',', ' ') }}
                                    <span>XAF{{ $property->price_type === 'monthly' ? ' / mois' : '' }}</span>
                                </div>
                            @endif
                        </div>

                        {{-- Thumbs --}}
                        <div style="display:flex; flex-direction:column; gap:.6rem; overflow-y:auto; max-height:480px;">
                            @foreach ($property->images as $i => $image)
                                <div class="gallery-thumb {{ $i === 0 ? 'active' : '' }}"
                                    onclick="switchImage(this, '{{ $image->image_url }}')" style="flex-shrink:0;">
                                    <img src="{{ $image->image_url }}" alt="">
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Feature strip --}}
                    @if ($property->surface || $property->rooms || $property->bedrooms || $property->bathrooms || $property->floor)
                        <div class="feature-strip" style="margin-bottom:2rem;">
                            @if ($property->surface)
                                <div class="feature-item">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="1.5">
                                        <rect x="3" y="3" width="18" height="18" rx="2" />
                                        <path d="M3 9h18M9 21V9" />
                                    </svg>
                                    <span class="fi-value">{{ $property->surface }} m²</span>
                                    <span class="fi-label">Surface</span>
                                </div>
                            @endif
                            @if ($property->rooms)
                                <div class="feature-item">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="1.5">
                                        <path d="M3 10a7 7 0 0114 0v4H3v-4z" />
                                        <path d="M1 14h22" />
                                        <path d="M3 18v2M21 18v2" />
                                    </svg>
                                    <span class="fi-value">{{ $property->rooms }}</span>
                                    <span class="fi-label">Pièces</span>
                                </div>
                            @endif
                            @if ($property->bedrooms)
                                <div class="feature-item">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="1.5">
                                        <path d="M2 20v-8a2 2 0 012-2h16a2 2 0 012 2v8" />
                                        <path d="M2 10V6a2 2 0 012-2h16a2 2 0 012 2v4" />
                                        <path d="M10 10V8" />
                                    </svg>
                                    <span class="fi-value">{{ $property->bedrooms }}</span>
                                    <span class="fi-label">Chambres</span>
                                </div>
                            @endif
                            @if ($property->bathrooms)
                                <div class="feature-item">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="1.5">
                                        <path d="M4 12h16M4 12a2 2 0 01-2-2V6a2 2 0 012-2h4l2 3h8a2 2 0 012 2v1" />
                                        <path d="M6 20v-8M18 20v-8M6 20h12" />
                                    </svg>
                                    <span class="fi-value">{{ $property->bathrooms }}</span>
                                    <span class="fi-label">SDB</span>
                                </div>
                            @endif
                            @if ($property->floor)
                                <div class="feature-item">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="1.5">
                                        <polyline points="22 7 13 16 8.5 11.5 2 18" />
                                        <polyline points="16 7 22 7 22 13" />
                                    </svg>
                                    <span class="fi-value">{{ $property->floor }}</span>
                                    <span class="fi-label">Étage</span>
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- Description --}}
                    <div style="margin-bottom:2.5rem;">
                        <h2 class="section-title font-display">Description</h2>
                        <p class="description-text">{{ $property->description }}</p>
                    </div>

                    {{-- Amenities --}}
                    @if ($property->amenities->isNotEmpty())
                        <div style="margin-bottom:2.5rem;">
                            <h2 class="section-title font-display">Équipements & services</h2>
                            <div style="display:flex; flex-wrap:wrap; gap:.6rem;">
                                @foreach ($property->amenities as $amenity)
                                    <span class="amenity-chip">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2">
                                            <polyline points="20 6 9 17 4 12" />
                                        </svg>
                                        {{ $amenity->name }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                </div>{{-- /left --}}

                {{-- RIGHT COLUMN (sticky) ── ── ── ──  --}}
                <div class="sidebar-col" style="position:sticky; top:1.5rem;">
                    <div class="contact-card">

                        {{-- Price --}}
                        <div style="margin-bottom:1.5rem;">
                            @if ($property->price)
                                <p
                                    style="font-family:'Inter',sans-serif; font-size:.75rem; font-weight:500; color:var(--muted); text-transform:uppercase; letter-spacing:.08em; margin-bottom:.35rem;">
                                    {{ $property->price_type === 'monthly' ? 'Loyer mensuel' : 'Prix de vente' }}
                                </p>
                                <div class="price-display">
                                    {{ number_format($property->price, 0, ',', ' ') }}
                                    <sub>XAF{{ $property->price_type === 'monthly' ? '/mois' : '' }}</sub>
                                </div>
                            @endif
                            <div class="gold-divider"></div>
                        </div>

                        {{-- Location pill --}}
                        <div
                            style="display:flex; align-items:center; gap:.6rem; font-family:'Inter',sans-serif; font-size:.82rem; color:var(--muted); margin-bottom:1.5rem; background:var(--cream); padding:.65rem 1rem; border-radius:.6rem; border:1px solid var(--border);">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2"
                                style="width:1rem;height:1rem;color:var(--gold);flex-shrink:0;">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z" />
                                <circle cx="12" cy="10" r="3" />
                            </svg>
                            {{ $property->address ?? '' }}{{ $property->address && $property->city ? ', ' : '' }}{{ $property->city->name ?? 'Brazzaville' }}
                        </div>

                        {{-- Map placeholder --}}
                        <div class="map-placeholder" style="margin-bottom:1.5rem;">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="1.5">
                                <polygon points="3 6 9 3 15 6 21 3 21 18 15 21 9 18 3 21 3 6" />
                                <line x1="9" y1="3" x2="9" y2="18" />
                                <line x1="15" y1="6" x2="15" y2="21" />
                            </svg>
                            <span>Voir sur la carte</span>
                        </div>

                        {{-- CTAs --}}
                        <div style="display:flex; flex-direction:column; gap:.75rem; margin-bottom:1.5rem;">
                            <a href="#contact" class="btn-primary">Contacter l'agence</a>
                            <a href="tel:{{ $property->agent?->phone ?? '+242000000000' }}" class="btn-outline">
                                Appeler directement
                            </a>
                        </div>

                        {{-- Agent --}}
                        @if ($property->agent)
                            <div class="agent-row">
                                <div class="agent-avatar">
                                    @if ($property->agent->avatar)
                                        <img src="{{ $property->agent->avatar }}" alt="{{ $property->agent->name }}"
                                            style="width:100%;height:100%;object-fit:cover;">
                                    @else
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="1.5">
                                            <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" />
                                            <circle cx="12" cy="7" r="4" />
                                        </svg>
                                    @endif
                                </div>
                                <div>
                                    <div class="agent-name">{{ $property->agent->name }}</div>
                                    <div class="agent-role">Agent immobilier</div>
                                </div>
                            </div>
                        @endif

                    </div>

                    {{-- Reference --}}
                    @if ($property->reference)
                        <p
                            style="font-family:'Inter',sans-serif; font-size:.72rem; color:var(--muted); text-align:center; margin-top:.75rem;">
                            Réf. {{ $property->reference }}
                        </p>
                    @endif
                </div>

            </div>{{-- /layout-grid --}}

        </x-blade-components::layout.container>
    </div>
@endsection

@push('scripts')
    <script>
        function switchImage(thumb, src) {
            document.getElementById('main-img').src = src;
            document.querySelectorAll('.gallery-thumb').forEach(t => t.classList.remove('active'));
            thumb.classList.add('active');
        }
    </script>
@endpush
