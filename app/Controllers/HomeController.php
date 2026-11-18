<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Seo;
use App\Core\View;
use App\Models\BlogPost;
use App\Models\Cheat;

final class HomeController extends Controller
{
    public function index(): void
    {
        $statusCounts = Cheat::statusCounts();
        $featuredCheats = Cheat::listFiltered(['featured' => true], 6);
        if ($featuredCheats === []) {
            $featuredCheats = Cheat::listFiltered([], 6);
        }
        $liveUpdates = Cheat::recentLiveUpdates(6);
        $latestPosts = BlogPost::published([], 3);

        $videoEnabled = ($this->settings['video_section_enabled'] ?? '1') === '1';
        $featuredUrl = (string) ($this->settings['video_featured_url'] ?? 'https://www.youtube.com/watch?v=VEqImC8xFZ0');
        $featuredId = View::extractYoutubeId($featuredUrl);
        $extraRaw = json_decode((string) ($this->settings['video_extra_json'] ?? '[]'), true);
        $extras = [];
        if (is_array($extraRaw)) {
            foreach ($extraRaw as $ev) {
                $ytId = View::extractYoutubeId((string) ($ev['url'] ?? ''));
                if ($ytId) {
                    $extras[] = [
                        'id'      => $ytId,
                        'url'     => (string) $ev['url'],
                        'title'   => trim((string) ($ev['title'] ?? '')) ?: 'GTA 6 Video Update',
                        'is_live' => !empty($ev['is_live']),
                    ];
                }
            }
        }
        $videoHub = [
            'enabled'        => $videoEnabled && ($featuredId !== null || $extras !== []),
            'is_live'        => ($this->settings['video_is_live'] ?? '1') === '1',
            'section_title'  => (string) ($this->settings['video_section_title'] ?? 'GTA 6 Live Stream & Watch Desk'),
            'section_sub'    => (string) ($this->settings['video_section_subtitle'] ?? 'Watch live launch countdown coverage, PS5/Xbox verification testing, and official breakdowns.'),
            'featured_id'    => $featuredId,
            'featured_url'   => $featuredUrl,
            'featured_title' => (string) ($this->settings['video_featured_title'] ?? 'GTA 6 Live Countdown & Launch Verification Stream'),
            'featured_note'  => (string) ($this->settings['video_featured_note'] ?? ''),
            'extras'         => $extras,
        ];

        $homeFaqs = [
            [
                'q' => 'Does GTA 6 have cheat codes on PS5 and Xbox Series X|S?',
                'a' => 'As of November 17, 2026, Rockstar Games has not published official GTA 6 cheat codes ahead of the November 19, 2026 launch. Once the game unlocks, ViceCodes will test and publish verified PS5 and Xbox Series X|S controller sequences and phone codes live.',
            ],
            [
                'q' => 'How do you enter cheat codes in GTA 6?',
                'a' => 'In modern Grand Theft Auto titles, cheat codes are entered in Story Mode free roam either via rapid controller button combinations (D-Pad, face buttons, bumpers/triggers) or by dialing a cheat phone number on your character\'s smartphone.',
            ],
            [
                'q' => 'Do GTA 6 cheat codes disable PlayStation Trophies and Xbox Achievements?',
                'a' => 'Yes. Based on GTA V and Red Dead Redemption 2, activating any cheat code disables trophies and achievements for your active session. Always create a manual backup save first so you can reload cleanly later.',
            ],
            [
                'q' => 'Is there an unlimited money cheat code in GTA 6?',
                'a' => 'No. Rockstar Games has not included direct infinite money button cheats since GTA San Andreas. Any website or video offering an "unlimited GTA 6 money generator" is fake or unsafe.',
            ],
        ];

        $schemas = [
            Seo::organizationSchema($this->settings),
            Seo::websiteSchema($this->settings),
            Seo::videoGameSchema(),
        ];
        if ($faqSchema = Seo::faqSchema($homeFaqs)) {
            $schemas[] = $faqSchema;
        }

        $seo = Seo::buildMeta([
            'title'       => $this->settings['default_meta_title'] ?? 'GTA 6 Cheat Codes (PS5, Xbox, PC) - Live Verified List',
            'description' => $this->settings['default_meta_description'] ?? '',
            'schemas'     => $schemas,
        ], $this->settings);

        $this->render('public/home', [
            'seo'            => $seo,
            'statusCounts'   => $statusCounts,
            'featuredCheats' => $featuredCheats,
            'liveUpdates'    => $liveUpdates,
            'latestPosts'    => $latestPosts,
            'homeFaqs'       => $homeFaqs,
            'videoHub'       => $videoHub,
        ]);
    }
}
