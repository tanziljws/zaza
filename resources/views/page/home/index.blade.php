@extends('layout.page.layout')
@section('title', 'Beranda')

@push('styles')
<style>
    .hero-video {
        width: 100%;
        height: 100%;
        position: absolute;
        inset: 0;
        pointer-events: none;
        overflow: hidden;
    }
    .hero-video iframe {
        width: 140%;
        height: 140%;
        margin-left: -20%;
        filter: brightness(0.6) saturate(1.2);
    }
</style>
@endpush

@section('content')
<section class="relative min-h-[80vh] w-full overflow-hidden ">
    @php
    $videoId = ui_value('banner','yt_embed_id');
    $baseUrl = 'https://www.youtube.com/embed/' . $videoId;
    $params = 'autoplay=1&mute=1&loop=1&controls=0&color=white&modestbranding=1&rel=0&playsinline=1&enablejsapi=1';
    $finalSrc = $baseUrl . '?' . $params . '&playlist=' . $videoId;
    @endphp
    <div class="hero-video -z-10">
        <iframe src="{{ $finalSrc }}" title="YouTube video player" frameborder="0"
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
            allowfullscreen></iframe>
    </div>
    <div class="relative min-h-[80vh] w-full bg-gradient-to-b from-black/40 via-black/30 to-black/60">
        <div class="mx-auto max-w-7xl px-6 sm:px-8 flex min-h-[80vh] items-center justify-center text-center">
            <div class="max-w-3xl">
                <h1 class="text-4xl sm:text-5xl font-black tracking-wider uppercase text-white" style="font-family:'Merriweather', serif;">{{ ui_value('web-setting','title') }}</h1>
                <p class="mt-4 text-base sm:text-lg text-gray-200">{{ Str::limit(ui_value('about','description'), 160) }}</p>
                <div class="mt-8 flex items-center justify-center gap-3">
                    <a href="#quick-links" class="inline-flex items-center gap-2 rounded-md bg-indigo-600 px-5 py-3 text-white shadow hover:bg-indigo-700">Jelajahi Jurusan</a>
                    <a href="#gallery" class="inline-flex items-center gap-2 rounded-md bg-white/90 px-5 py-3 text-gray-900 shadow hover:bg-white">Lihat Galeri</a>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="about-us" class="py-20 bg-slate-950">
    <div class="mx-auto max-w-7xl px-6 sm:px-8">
        <div class="grid items-center gap-10 md:grid-cols-12">
            <div class="md:col-span-5">
                <div class="relative">
                    <img src="{{ asset('storage/'.ui_value('about','image')) }}" class="w-full rounded-2xl object-cover shadow-xl" alt="Tentang Kami">
                    <div class="absolute inset-0 rounded-2xl ring-1 ring-white/20"></div>
                </div>
            </div>
            <div class="md:col-span-7">
                <div class="rounded-2xl border border-white/10 bg-white/5 p-8 text-white shadow backdrop-blur">
                    <h2 class="text-3xl font-black tracking-wider uppercase" style="font-family:'Merriweather', serif;">{{ ui_value('about','title') }}</h2>
                    <p class="mt-4 text-slate-200 leading-relaxed">{{ ui_value('about','description') }}</p>
                    <div class="mt-6">
                        <a href="#agenda-informasi" class="inline-flex items-center gap-2 rounded-md bg-indigo-600 px-5 py-3 text-white shadow hover:bg-indigo-700">Agenda & Informasi</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


<section id="quick-links" class="py-16 bg-gray-900">
    <div class="mx-auto max-w-7xl px-6 sm:px-8">
        <div class="flex gap-6 overflow-x-auto scroll-smooth snap-x snap-mandatory">
            @foreach($majors as $major)
            <div class="min-w-[18rem] snap-start rounded-2xl bg-gradient-to-br from-indigo-700 via-purple-700 to-pink-600 p-6 text-white shadow-lg">
                <div class="mb-4 flex items-center gap-3">
                    <img src="{{ asset('storage/'.$major->image) }}" class="h-12 w-12 rounded-lg object-cover shadow" alt="Icon">
                    <h3 class="text-lg font-semibold">{{ $major->title }}</h3>
                </div>
                <p class="text-sm text-white/90">{{ $major->description }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

<section id="gallery" class="py-16 bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900">
    <div class="mx-auto max-w-7xl px-6 sm:px-8">
        <div class="mb-10">
            <h2 class="text-3xl font-black tracking-wider uppercase text-white" style="font-family:'Merriweather', serif;">Galeri Sekolah</h2>
            <p class="mt-2 text-sm text-slate-300">Dokumentasi kegiatan terbaru dan unggulan</p>
        </div>
        <div class="relative">
            <div class="gallery-swiper swiper">
                <div class="swiper-wrapper">
                    @foreach ($gallery as $item)
                    @php
                        $imgGal = (isset($item->fotos[0]) && isset($item->fotos[0]->file)) ? asset('storage/'.$item->fotos[0]->file) : 'https://placehold.co/1200x800?text=Gambar+Tidak+Ada';
                    @endphp
                    <div class="swiper-slide">
                        <div class="relative overflow-hidden rounded-2xl shadow-xl">
                            <div class="h-[28rem] w-full bg-cover bg-center" style="background-image:url('{{ $imgGal }}')"></div>
                            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
                            <div class="absolute bottom-0 left-0 right-0 p-8 text-white">
                                <h3 class="text-2xl font-semibold">{{ $item->judul }}</h3>
                                <p class="mt-3 text-white/90">{!! Str::limit($item->isi, 160) !!}</p>
                                <div class="mt-4">
                                    <a href="{{ route('detail.show', $item->id) }}" class="inline-flex items-center gap-2 rounded-md bg-white/90 px-5 py-2 text-gray-900 hover:bg-white">Selengkapnya</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="gallery-pagination mt-6"></div>
            </div>
            <div class="gallery-prev absolute left-0 top-1/2 -translate-y-1/2 z-10 hidden md:block">
                <button class="rounded-full bg-white/80 backdrop-blur p-3 shadow-lg ring-1 ring-gray-200 hover:bg-white">◀</button>
            </div>
            <div class="gallery-next absolute right-0 top-1/2 -translate-y-1/2 z-10 hidden md:block">
                <button class="rounded-full bg-white/80 backdrop-blur p-3 shadow-lg ring-1 ring-gray-200 hover:bg-white">▶</button>
            </div>
        </div>
    </div>
</section>

<section id="agenda-informasi" class="py-20 bg-gradient-to-br from-indigo-50 via-white to-sky-50">
    <div class="mx-auto max-w-7xl px-6 sm:px-8">
        <div class="mb-10">
            <h2 class="text-3xl font-black tracking-wider uppercase text-gray-900" style="font-family:'Merriweather', serif;">Agenda & Informasi</h2>
            <p class="mt-2 text-sm text-gray-600">Rangkuman kegiatan dan kabar terbaru sekolah</p>
        </div>
        <div class="grid grid-cols-1 gap-10 md:grid-cols-2">
            <div id="agenda" class="rounded-2xl border border-gray-200 bg-white p-6 shadow">
                <h3 class="mb-6 text-2xl font-semibold text-gray-900">Agenda Sekolah</h3>
                <div class="grid grid-cols-1 gap-6">
                    @forelse(($agenda ?? []) as $ag)
                    <article class="relative overflow-hidden rounded-xl bg-gradient-to-br from-indigo-600 to-blue-600 p-5 text-white shadow">
                        <h4 class="text-lg font-semibold">{{ $ag->judul }}</h4>
                        <p class="mt-2 text-white/90">{!! Str::limit($ag->isi, 110) !!}</p>
                        <a href="{{ route('detail.show', $ag->id) }}" class="absolute inset-0" aria-label="Lihat detail agenda"></a>
                    </article>
                    @empty
                    <div class="text-gray-600">Belum ada agenda.</div>
                    @endforelse
                </div>
            </div>
            <div id="informasi" class="rounded-2xl border border-gray-200 bg-white p-6 shadow">
                <h3 class="mb-6 text-2xl font-semibold text-gray-900">Informasi Terkini</h3>
                <div class="grid grid-cols-1 gap-6">
                    @forelse(($informasi ?? []) as $info)
                    @php
                        $gal = \App\Models\Galery::with('fotos')->where('post_id', $info->id)->first();
                        $img = ($gal && isset($gal->fotos[0])) ? asset('storage/'.$gal->fotos[0]->file) : 'https://placehold.co/400x280?text=Gambar';
                    @endphp
                    <article class="group relative overflow-hidden rounded-xl bg-white shadow ring-1 ring-gray-200">
                        <div class="flex items-start gap-4 p-4">
                            <img src="{{ $img }}" class="h-24 w-36 rounded-lg object-cover shadow" alt="{{ $info->judul }}">
                            <div class="flex-1">
                                <h4 class="text-lg font-medium text-gray-900">{{ $info->judul }}</h4>
                                <p class="mt-1 text-gray-700">{!! Str::limit($info->isi, 90) !!}</p>
                            </div>
                        </div>
                        <a href="{{ route('detail.show', $info->id) }}" class="absolute inset-0" aria-label="Lihat detail informasi"></a>
                    </article>
                    @empty
                    <div class="text-gray-600">Belum ada informasi.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</section>





<section id="peta" class="py-20 bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900">
    <div class="mx-auto max-w-7xl px-6 sm:px-8">
        <div class="mb-10">
            <h2 class="text-3xl font-black tracking-wider uppercase text-white" style="font-family:'Merriweather', serif;">Peta Sekolah</h2>
            <p class="mt-2 text-sm text-slate-300">Orientasi area kampus dan fasilitas utama</p>
        </div>
        <div class="relative">
            <div class="map-swiper swiper">
                <div class="swiper-wrapper">
                    @foreach ($maps as $item)
                    @php
                        $imgMap = (isset($item->gallery[0]) && isset($item->gallery[0]->fotos[0]) && isset($item->gallery[0]->fotos[0]->file)) ? asset('storage/'.$item->gallery[0]->fotos[0]->file) : 'https://placehold.co/1200x800?text=Gambar+Tidak+Ada';
                    @endphp
                    <div class="swiper-slide">
                        <div class="relative overflow-hidden rounded-2xl shadow-xl">
                            <div class="h-[28rem] w-full bg-cover bg-center" style="background-image:url('{{ $imgMap }}')"></div>
                            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
                            <div class="absolute bottom-0 left-0 right-0 p-8 text-white">
                                <h3 class="text-2xl font-semibold">{{ $item->judul }}</h3>
                                <p class="mt-3 text-white/90">{!! nl2br(Str::limit($item->isi, 160)) !!}</p>
                                <div class="mt-4">
                                    <a href="{{ route('detail.show', $item->id) }}" class="inline-flex items-center gap-2 rounded-md bg-white/90 px-5 py-2 text-gray-900 hover:bg-white">Selengkapnya</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="map-pagination mt-6"></div>
            </div>
            <div class="map-prev absolute left-0 top-1/2 -translate-y-1/2 z-10 hidden md:block">
                <button class="rounded-full bg-white/80 backdrop-blur p-3 shadow-lg ring-1 ring-gray-200 hover:bg-white">◀</button>
            </div>
            <div class="map-next absolute right-0 top-1/2 -translate-y-1/2 z-10 hidden md:block">
                <button class="rounded-full bg-white/80 backdrop-blur p-3 shadow-lg ring-1 ring-gray-200 hover:bg-white">▶</button>
            </div>
        </div>
    </div>
</section>

@endsection