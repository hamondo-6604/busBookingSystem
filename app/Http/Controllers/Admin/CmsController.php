<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LandingPage;
use App\Services\Cloudinary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CmsController extends Controller
{
    public function landing()
    {
        $settings = LandingPage::pluck('content', 'section_key')->toArray();

        return view('admin.cms.landing', compact('settings'));
    }

    public function updateLanding(Request $request)
    {
        $validated = $request->validate([
            'hero_slides' => ['nullable', 'array'],
            'hero_slides.*.badge' => ['nullable', 'string', 'max:120'],
            'hero_slides.*.title' => ['nullable', 'string', 'max:500'],
            'hero_slides.*.subtitle' => ['nullable', 'string', 'max:1000'],
            'hero_slides.*.existing_image' => ['nullable', 'string', 'max:2048'],
            'hero_slides.*.image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:10240'],
            'hero_slides.*.cta_text' => ['nullable', 'string', 'max:80'],
            'hero_slides.*.cta_url' => ['nullable', 'string', 'max:500'],
            'hero_slides.*.show_trust_badges' => ['nullable', 'boolean'],
            'hero_slides.*.show_live_stats' => ['nullable', 'boolean'],
            'faqs' => ['nullable', 'array'],
            'faqs.*.q' => ['nullable', 'string', 'max:300'],
            'faqs.*.a' => ['nullable', 'string', 'max:2000'],
            'how_it_works' => ['nullable', 'array'],
            'how_it_works.*.title' => ['nullable', 'string', 'max:120'],
            'how_it_works.*.desc' => ['nullable', 'string', 'max:500'],
            'how_it_works.*.icon' => ['nullable', 'string', 'max:60'],
            'popular_routes_title' => ['nullable', 'string', 'max:120'],
            'popular_routes_subtitle' => ['nullable', 'string', 'max:300'],
            'cta_title' => ['nullable', 'string', 'max:120'],
            'cta_description' => ['nullable', 'string', 'max:300'],
        ]);

        $uploadErrors = [];

        if ($request->has('hero_slides_submitted')) {
            $slides = [];
            $heroSlides = $request->input('hero_slides', []);

            foreach ($heroSlides as $index => $slide) {
                $imageUrl = $slide['existing_image'] ?? null;
                $uploadedFile = $request->file("hero_slides.{$index}.image");

                if ($uploadedFile) {
                    try {
                        $imageUrl = Cloudinary::uploadHero($uploadedFile->getRealPath());
                    } catch (\Exception $e) {
                        Log::error('Hero slide Cloudinary upload failed: ' . $e->getMessage());
                        $uploadErrors[] = $e->getMessage();
                    }
                }

                $slides[] = [
                    'badge' => $slide['badge'] ?? '',
                    'title' => $slide['title'] ?? '',
                    'subtitle' => $slide['subtitle'] ?? '',
                    'image' => $imageUrl ?? '',
                    'cta_text' => $slide['cta_text'] ?? '',
                    'cta_url' => $slide['cta_url'] ?? '',
                    'show_trust_badges' => !empty($slide['show_trust_badges']),
                    'show_live_stats' => !empty($slide['show_live_stats']),
                ];
            }

            LandingPage::updateOrCreate(
                ['section_key' => 'hero_slides'],
                ['content' => $slides]
            );
        }

        if (!empty($validated['faqs'])) {
            LandingPage::updateOrCreate(
                ['section_key' => 'faqs'],
                ['content' => array_values($validated['faqs'])]
            );
        }

        if (!empty($validated['how_it_works'])) {
            LandingPage::updateOrCreate(
                ['section_key' => 'how_it_works'],
                ['content' => array_values($validated['how_it_works'])]
            );
        }

        $textFields = [
            'popular_routes_title',
            'popular_routes_subtitle',
            'cta_title',
            'cta_description',
        ];

        foreach ($textFields as $key) {
            if (array_key_exists($key, $validated)) {
                LandingPage::updateOrCreate(
                    ['section_key' => $key],
                    ['content' => $validated[$key] ?? '']
                );
            }
        }

        Cache::forget('home:cmsSettings');

        if (!empty($uploadErrors)) {
            return redirect()
                ->route('admin.cms.landing')
                ->with('warning', 'Settings saved, but some images failed to upload: ' . implode(' ', $uploadErrors));
        }

        return redirect()
            ->route('admin.cms.landing')
            ->with('success', 'Landing page settings updated successfully.');
    }
}
