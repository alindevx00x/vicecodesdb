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
                'a' => 'Yes. GTA 6 includes 61 cheat codes across player, combat, vehicle, environment, and screen filter categories. However, you must beat the 91-mission main story first to unlock the Cheats menu under Progress, and complete each cheat\'s specific in-game challenge before activating it.',
            ],
            [
                'q' => 'How do you enter cheat codes in GTA 6?',
                'a' => 'Once unlocked after finishing the story and each cheat\'s challenge, press D-Pad Up to open your in-game phone, hold L1 (PS5) or LB (Xbox) and press R1 (PS5) or RB (Xbox), then enter the D-Pad arrow sequence.',
            ],
            [
                'q' => 'Do GTA 6 cheat codes disable PS5 Trophies, Xbox Achievements, and saving?',
                'a' => 'Yes. Activating any cheat code in GTA 6 disables PlayStation Trophies and Xbox Achievements AND blocks saving for your active session. Always keep a clean manual save file ready to reload later.',
            ],
            [
                'q' => 'Is there an instant $250,000 or unlimited money cheat code in GTA 6?',
                'a' => 'No early-game money cheat works at launch. Traditional instant cash codes do not work from the start in GTA 6, and any website or social post claiming an unlimited GTA 6 money generator or crypto coin is fake.',
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
