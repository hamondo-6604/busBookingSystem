@extends('layouts.admin')
@section('title', 'Landing Page CMS')

@section('content')
<style>
    #cms-form input[type="text"],
    #cms-form input[type="file"],
    #cms-form textarea {
        padding: 0.625rem 1rem;
    }
    #cms-form input[type="file"] {
        padding: 0.5rem 0.75rem;
        border: 1px solid rgb(226 232 240);
        border-radius: 0.75rem;
        background-color: #fff;
        width: 100%;
        max-width: 28rem;
    }
    .dark #cms-form input[type="file"] {
        border-color: rgb(71 85 105);
        background-color: rgb(51 65 85);
        color: rgb(241 245 249);
    }
    .slide-chevron { transition: transform 0.2s ease; }
    .slide-chevron.is-collapsed { transform: rotate(-90deg); }
</style>
<div class="max-w-5xl mx-auto">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Landing Page CMS</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Manage the content that appears on the public homepage.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl mb-6 flex items-center gap-2">
            <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('warning'))
        <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-300 px-4 py-3 rounded-xl mb-6 flex items-center gap-2">
            <i class="fa-solid fa-triangle-exclamation"></i> {{ session('warning') }}
        </div>
    @endif
    @if($errors->any())
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 px-4 py-3 rounded-xl mb-6">
            <ul class="list-disc list-inside text-sm">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form id="cms-form" action="{{ route('admin.cms.landing.update') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
        @csrf
        <input type="hidden" name="hero_slides_submitted" value="1">

        {{-- ══════════════ HERO SECTION ══════════════ --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden shadow-sm">
            <div class="bg-slate-50 dark:bg-slate-900/50 px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex justify-between items-start gap-4">
                <div>
                    <h2 class="text-lg font-bold text-slate-800 dark:text-slate-200 flex items-center gap-2">
                        <i class="fa-solid fa-images text-primary-500"></i> Hero Carousel
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Images upload to Cloudinary. Use 1920×1080 or larger for sharp hero backgrounds.</p>
                </div>
                <div class="flex flex-wrap items-center justify-end gap-2 shrink-0">
                    <button type="button" onclick="expandAllSlides()" class="text-xs font-semibold text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200">Expand all</button>
                    <span class="text-slate-300 dark:text-slate-600">|</span>
                    <button type="button" onclick="collapseAllSlides()" class="text-xs font-semibold text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200">Collapse all</button>
                    <button type="button" onclick="addHeroSlide()" class="text-xs font-bold text-primary-600 dark:text-primary-400 hover:underline ml-1">+ Add Slide</button>
                </div>
            </div>

            <div class="p-6 space-y-3" id="hero-slides-container">
                @php
                    $slides = $settings['hero_slides'] ?? [];
                    if (empty($slides)) {
                        $slides = [
                            [
                                'badge' => 'Instant e-ticket confirmation',
                                'title' => 'Travel Mindanao<br><span class="text-primary-400">Your Way</span>',
                                'subtitle' => 'Book intercity bus trips in seconds.',
                                'image' => 'https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?auto=format&fit=crop&q=80&w=2000'
                            ],
                            [
                                'badge' => 'Premium Comfort',
                                'title' => 'First Class<br><span class="text-primary-400">Experience</span>',
                                'subtitle' => 'Enjoy spacious seating and full air-conditioning.',
                                'image' => 'https://images.unsplash.com/photo-1570125909232-eb263c188f7e?auto=format&fit=crop&q=80&w=2000'
                            ]
                        ];
                    }
                @endphp

                @foreach($slides as $index => $slide)
                    @php
                        $slideLabel = strip_tags($slide['title'] ?? '') ?: ($slide['badge'] ?? 'Untitled slide');
                        $slideExpanded = $index === 0;
                    @endphp
                    <div class="slide-item border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden bg-white dark:bg-slate-800/50">
                        <div class="slide-item-header flex items-center gap-3 px-4 py-3 bg-slate-50 dark:bg-slate-900/40 cursor-pointer select-none"
                             role="button" tabindex="0"
                             onclick="toggleSlidePanel(this)"
                             onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();toggleSlidePanel(this);}">
                            <i class="fa-solid fa-chevron-down slide-chevron text-slate-400 text-xs {{ $slideExpanded ? '' : 'is-collapsed' }}"></i>
                            <div class="flex-1 min-w-0 pr-2">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wide slide-number">Slide {{ $index + 1 }}</span>
                                <p class="slide-summary text-sm font-semibold text-slate-800 dark:text-slate-200 truncate">{{ $slideLabel }}</p>
                            </div>
                            <button type="button" onclick="event.stopPropagation(); requestCmsRemove(this, 'slide')"
                                    class="shrink-0 text-red-500 hover:text-red-700 text-xs font-bold px-2.5 py-1.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                <i class="fa-solid fa-trash"></i> Remove
                            </button>
                        </div>
                        <div class="slide-item-body px-4 pb-4 pt-3 border-t border-slate-100 dark:border-slate-700 {{ $slideExpanded ? '' : 'hidden' }}">
                            <div class="grid md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">Badge Text</label>
                                    <input type="text" name="hero_slides[{{ $index }}][badge]" value="{{ $slide['badge'] ?? '' }}" oninput="updateSlideSummary(this)" class="w-full rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-sm" placeholder="e.g. Instant e-ticket confirmation">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">Title (HTML allowed)</label>
                                    <input type="text" name="hero_slides[{{ $index }}][title]" value="{{ $slide['title'] ?? '' }}" oninput="updateSlideSummary(this)" class="w-full rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-sm" placeholder="e.g. Travel Mindanao">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">Subtitle</label>
                                    <textarea name="hero_slides[{{ $index }}][subtitle]" rows="2" class="w-full rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-sm">{{ $slide['subtitle'] ?? '' }}</textarea>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">Button label (optional)</label>
                                    <input type="text" name="hero_slides[{{ $index }}][cta_text]" value="{{ $slide['cta_text'] ?? '' }}" class="w-full rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-sm" placeholder="e.g. Browse Routes">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">Button link (optional)</label>
                                    <input type="text" name="hero_slides[{{ $index }}][cta_url]" value="{{ $slide['cta_url'] ?? '' }}" class="w-full rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-sm" placeholder="/booking-routes">
                                </div>
                                <div class="md:col-span-2 flex flex-wrap gap-6">
                                    <label class="inline-flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300 cursor-pointer">
                                        <input type="hidden" name="hero_slides[{{ $index }}][show_trust_badges]" value="0">
                                        <input type="checkbox" name="hero_slides[{{ $index }}][show_trust_badges]" value="1" class="rounded border-slate-300" @checked(!empty($slide['show_trust_badges']))>
                                        Show trust badges
                                    </label>
                                    <label class="inline-flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300 cursor-pointer">
                                        <input type="hidden" name="hero_slides[{{ $index }}][show_live_stats]" value="0">
                                        <input type="checkbox" name="hero_slides[{{ $index }}][show_live_stats]" value="1" class="rounded border-slate-300" @checked(!empty($slide['show_live_stats']))>
                                        Show live trip stats
                                    </label>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">Background Image</label>
                                    <div class="flex flex-col sm:flex-row sm:items-start gap-4">
                                        @if(!empty($slide['image']))
                                            <img src="{{ \App\Services\Cloudinary::heroImageUrl($slide['image'], 400, 225) }}" alt="Slide preview" class="w-40 h-24 object-cover rounded-lg border border-slate-200 dark:border-slate-700">
                                            <input type="hidden" name="hero_slides[{{ $index }}][existing_image]" value="{{ $slide['image'] }}">
                                        @endif
                                        <input type="file" name="hero_slides[{{ $index }}][image]" accept="image/jpeg,image/png,image/webp" class="text-sm">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- ══════════════ POPULAR ROUTES ══════════════ --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden shadow-sm">
            <div class="bg-slate-50 dark:bg-slate-900/50 px-6 py-4 border-b border-slate-200 dark:border-slate-700">
                <h2 class="text-lg font-bold text-slate-800 dark:text-slate-200 flex items-center gap-2">
                    <i class="fa-solid fa-fire text-orange-500"></i> Popular Routes Section
                </h2>
            </div>
            <div class="p-6 grid gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">Section Title</label>
                    <input type="text" name="popular_routes_title" value="{{ $settings['popular_routes_title'] ?? 'Popular Routes' }}" class="w-full rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">Section Subtitle</label>
                    <input type="text" name="popular_routes_subtitle" value="{{ $settings['popular_routes_subtitle'] ?? 'Our most-booked intercity routes — reserve early for the best fares.' }}" class="w-full rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-sm">
                </div>
            </div>
        </div>

        {{-- ══════════════ HOW IT WORKS ══════════════ --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden shadow-sm">
            <div class="bg-slate-50 dark:bg-slate-900/50 px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center">
                <h2 class="text-lg font-bold text-slate-800 dark:text-slate-200 flex items-center gap-2">
                    <i class="fa-solid fa-list-ol text-blue-500"></i> How It Works
                </h2>
            </div>

            <div class="p-6">
                @php
                    $steps = $settings['how_it_works'] ?? [
                        ['icon' => 'search', 'title' => 'Search Your Trip', 'desc' => 'Enter your origin, destination, and travel date.'],
                        ['icon' => 'armchair', 'title' => 'Pick Your Seat', 'desc' => 'View the live seat map.'],
                        ['icon' => 'credit-card', 'title' => 'Pay Securely', 'desc' => 'GCash, Maya, card, or OTC.'],
                        ['icon' => 'ticket', 'title' => 'Board & Ride', 'desc' => 'Show your QR e-ticket at the terminal gate.']
                    ];
                @endphp
                <div class="grid md:grid-cols-2 gap-6">
                    @foreach($steps as $index => $step)
                    <div class="border border-slate-100 dark:border-slate-700 rounded-xl p-4">
                        <div class="mb-3">
                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">Step {{ $index + 1 }} Title</label>
                            <input type="text" name="how_it_works[{{ $index }}][title]" value="{{ $step['title'] ?? '' }}" class="w-full rounded-lg border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-sm">
                        </div>
                        <div class="mb-3">
                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">Step {{ $index + 1 }} Description</label>
                            <textarea name="how_it_works[{{ $index }}][desc]" rows="2" class="w-full rounded-lg border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-sm">{{ $step['desc'] ?? '' }}</textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">Lucide Icon Name</label>
                            <input type="text" name="how_it_works[{{ $index }}][icon]" value="{{ $step['icon'] ?? '' }}" class="w-full rounded-lg border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-sm">
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ══════════════ FAQS ══════════════ --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden shadow-sm">
            <div class="bg-slate-50 dark:bg-slate-900/50 px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center">
                <h2 class="text-lg font-bold text-slate-800 dark:text-slate-200 flex items-center gap-2">
                    <i class="fa-solid fa-circle-question text-purple-500"></i> Travel FAQs
                </h2>
                <button type="button" onclick="addFaq()" class="text-xs font-bold text-primary-600 dark:text-primary-400 hover:underline">
                    + Add FAQ
                </button>
            </div>

            <div class="p-6 grid gap-4" id="faqs-container">
                @php
                    $faqs = $settings['faqs'] ?? [
                        ['q' => 'What is your luggage policy?', 'a' => 'Each passenger is allowed 1 hand-carry bag (up to 7kg)...']
                    ];
                @endphp
                @foreach($faqs as $index => $faq)
                    <div class="faq-item border border-slate-100 dark:border-slate-700 rounded-xl p-4 relative">
                        <button type="button" onclick="requestCmsRemove(this, 'faq')" class="absolute top-2 right-4 text-red-500 hover:text-red-700 text-xs font-bold">
                            Remove
                        </button>
                        <div class="mb-3 pr-16">
                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">Question</label>
                            <input type="text" name="faqs[{{ $index }}][q]" value="{{ $faq['q'] ?? '' }}" class="w-full rounded-lg border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">Answer</label>
                            <textarea name="faqs[{{ $index }}][a]" rows="2" class="w-full rounded-lg border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-sm">{{ $faq['a'] ?? '' }}</textarea>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- ══════════════ CTA BANNER ══════════════ --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden shadow-sm">
            <div class="bg-slate-50 dark:bg-slate-900/50 px-6 py-4 border-b border-slate-200 dark:border-slate-700">
                <h2 class="text-lg font-bold text-slate-800 dark:text-slate-200 flex items-center gap-2">
                    <i class="fa-solid fa-bullhorn text-emerald-500"></i> Bottom CTA Banner
                </h2>
            </div>
            <div class="p-6 grid gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">Title</label>
                    <input type="text" name="cta_title" value="{{ $settings['cta_title'] ?? 'Ready to ride?' }}" class="w-full rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">Description</label>
                    <input type="text" name="cta_description" value="{{ $settings['cta_description'] ?? 'Search from our routes and book your seat in under 2 minutes.' }}" class="w-full rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-sm">
                </div>
            </div>
        </div>

        <div class="flex justify-end pt-4 pb-12">
            <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white font-bold py-3 px-8 rounded-xl shadow-lg transition-colors flex items-center gap-2">
                <i class="fa-solid fa-save"></i> Save CMS Changes
            </button>
        </div>
    </form>
</div>

{{-- Remove confirmation modal --}}
<div id="cms-delete-modal" class="hidden fixed inset-0 z-[100] items-center justify-center p-4 sm:p-6 opacity-0 transition-opacity duration-300">
    <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="closeAdminModal('cms-delete-modal')"></div>
    <div class="admin-modal-panel relative w-full max-w-md bg-white dark:bg-slate-800 rounded-2xl shadow-2xl overflow-hidden ring-1 ring-slate-200 dark:ring-slate-700 transform scale-95 transition-transform duration-300">
        <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-700">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <i class="fa-solid fa-triangle-exclamation text-amber-500"></i> Confirm removal
            </h3>
        </div>
        <div class="px-6 py-5">
            <p id="cms-delete-message" class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed"></p>
            <p class="text-xs text-slate-400 dark:text-slate-500 mt-2">The live homepage updates only after you click <strong>Save CMS Changes</strong>.</p>
        </div>
        <div class="px-6 py-4 bg-slate-50 dark:bg-slate-900/50 flex justify-end gap-3 border-t border-slate-100 dark:border-slate-700">
            <button type="button" onclick="closeAdminModal('cms-delete-modal')" class="px-5 py-2.5 text-sm font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition-colors">Cancel</button>
            <button type="button" onclick="confirmCmsDelete()" class="px-5 py-2.5 text-sm font-bold text-white bg-red-600 hover:bg-red-700 rounded-xl transition-colors">Remove</button>
        </div>
    </div>
</div>

<script>
    let slideCount = {{ count($slides ?? []) }};
    let faqCount = {{ count($faqs ?? []) }};
    let pendingRemoveEl = null;

    function toggleSlidePanel(header) {
        const item = header.closest('.slide-item');
        const body = item.querySelector('.slide-item-body');
        const chevron = header.querySelector('.slide-chevron');
        body.classList.toggle('hidden');
        chevron.classList.toggle('is-collapsed', body.classList.contains('hidden'));
    }

    function expandAllSlides() {
        document.querySelectorAll('#hero-slides-container .slide-item').forEach(item => {
            item.querySelector('.slide-item-body').classList.remove('hidden');
            item.querySelector('.slide-chevron').classList.remove('is-collapsed');
        });
    }

    function collapseAllSlides() {
        document.querySelectorAll('#hero-slides-container .slide-item').forEach(item => {
            item.querySelector('.slide-item-body').classList.add('hidden');
            item.querySelector('.slide-chevron').classList.add('is-collapsed');
        });
    }

    function updateSlideNumbers() {
        document.querySelectorAll('#hero-slides-container .slide-item').forEach((item, idx) => {
            const num = item.querySelector('.slide-number');
            if (num) num.textContent = 'Slide ' + (idx + 1);
        });
    }

    function updateSlideSummary(input) {
        const item = input.closest('.slide-item');
        if (!item) return;
        const title = item.querySelector('[name*="[title]"]')?.value || '';
        const badge = item.querySelector('[name*="[badge]"]')?.value || '';
        const summary = item.querySelector('.slide-summary');
        if (!summary) return;
        const plain = title.replace(/<[^>]*>/g, '').trim();
        summary.textContent = plain || badge.trim() || 'Untitled slide';
    }

    function requestCmsRemove(btn, type) {
        pendingRemoveEl = btn.closest(type === 'slide' ? '.slide-item' : '.faq-item');
        const msg = document.getElementById('cms-delete-message');
        if (type === 'slide') {
            const label = pendingRemoveEl.querySelector('.slide-summary')?.textContent?.trim() || 'this slide';
            msg.textContent = 'Remove "' + label + '" from the carousel? Its image and content will be dropped when you save.';
        } else {
            const q = pendingRemoveEl.querySelector('[name*="[q]"]')?.value?.trim();
            msg.textContent = q ? 'Remove the FAQ "' + q + '"?' : 'Remove this FAQ item?';
        }
        openAdminModal('cms-delete-modal');
    }

    function confirmCmsDelete() {
        if (pendingRemoveEl) {
            const isSlide = pendingRemoveEl.classList.contains('slide-item');
            pendingRemoveEl.remove();
            pendingRemoveEl = null;
            if (isSlide) updateSlideNumbers();
        }
        closeAdminModal('cms-delete-modal');
    }

    function buildSlideHtml(i) {
        return `
            <div class="slide-item border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden bg-white dark:bg-slate-800/50">
                <div class="slide-item-header flex items-center gap-3 px-4 py-3 bg-slate-50 dark:bg-slate-900/40 cursor-pointer select-none"
                     role="button" tabindex="0" onclick="toggleSlidePanel(this)"
                     onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();toggleSlidePanel(this);}">
                    <i class="fa-solid fa-chevron-down slide-chevron text-slate-400 text-xs"></i>
                    <div class="flex-1 min-w-0 pr-2">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wide slide-number">Slide</span>
                        <p class="slide-summary text-sm font-semibold text-slate-800 dark:text-slate-200 truncate">New slide</p>
                    </div>
                    <button type="button" onclick="event.stopPropagation(); requestCmsRemove(this, 'slide')"
                            class="shrink-0 text-red-500 hover:text-red-700 text-xs font-bold px-2.5 py-1.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                        <i class="fa-solid fa-trash"></i> Remove
                    </button>
                </div>
                <div class="slide-item-body px-4 pb-4 pt-3 border-t border-slate-100 dark:border-slate-700">
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">Badge Text</label>
                        <input type="text" name="hero_slides[${i}][badge]" oninput="updateSlideSummary(this)" class="w-full rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-sm" placeholder="e.g. Instant e-ticket confirmation">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">Title (HTML allowed)</label>
                        <input type="text" name="hero_slides[${i}][title]" oninput="updateSlideSummary(this)" class="w-full rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-sm" placeholder="e.g. Travel Mindanao">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">Subtitle</label>
                        <textarea name="hero_slides[${i}][subtitle]" rows="2" class="w-full rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-sm"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">Button label (optional)</label>
                        <input type="text" name="hero_slides[${i}][cta_text]" class="w-full rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-sm" placeholder="e.g. Browse Routes">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">Button link (optional)</label>
                        <input type="text" name="hero_slides[${i}][cta_url]" class="w-full rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-sm" placeholder="/booking-routes">
                    </div>
                    <div class="md:col-span-2 flex flex-wrap gap-6">
                        <label class="inline-flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300 cursor-pointer">
                            <input type="hidden" name="hero_slides[${i}][show_trust_badges]" value="0">
                            <input type="checkbox" name="hero_slides[${i}][show_trust_badges]" value="1" class="rounded border-slate-300"> Show trust badges
                        </label>
                        <label class="inline-flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300 cursor-pointer">
                            <input type="hidden" name="hero_slides[${i}][show_live_stats]" value="0">
                            <input type="checkbox" name="hero_slides[${i}][show_live_stats]" value="1" class="rounded border-slate-300"> Show live trip stats
                        </label>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">Background Image</label>
                        <input type="file" name="hero_slides[${i}][image]" accept="image/jpeg,image/png,image/webp" class="text-sm">
                    </div>
                </div>
                </div>
            </div>`;
    }

    function addHeroSlide() {
        collapseAllSlides();
        const container = document.getElementById('hero-slides-container');
        const i = slideCount++;
        container.insertAdjacentHTML('beforeend', buildSlideHtml(i));
        updateSlideNumbers();
        container.lastElementChild.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function addFaq() {
        const container = document.getElementById('faqs-container');
        const i = faqCount++;
        const html = `
            <div class="faq-item border border-slate-100 dark:border-slate-700 rounded-xl p-4 relative">
                <button type="button" onclick="requestCmsRemove(this, 'faq')" class="absolute top-2 right-4 text-red-500 hover:text-red-700 text-xs font-bold">Remove</button>
                <div class="mb-3 pr-16">
                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">Question</label>
                    <input type="text" name="faqs[${i}][q]" class="w-full rounded-lg border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">Answer</label>
                    <textarea name="faqs[${i}][a]" rows="2" class="w-full rounded-lg border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-sm"></textarea>
                </div>
            </div>`;
        container.insertAdjacentHTML('beforeend', html);
    }
</script>
@endsection
