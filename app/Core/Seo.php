<?php
declare(strict_types=1);

namespace App\Core;

final class Seo
{
    public static function buildMeta(array $seo, array $settings = []): array
    {
        $siteName = $settings['site_name'] ?? 'ViceCodes - GTA 6 Cheat Codes';
        $defaultTitle = $settings['default_meta_title'] ?? $siteName;
        $defaultDesc = $settings['default_meta_description'] ?? 'Verified GTA 6 cheat codes for PS5, Xbox Series X|S, and PC.';

        return [
            'title'       => trim((string) ($seo['title'] ?? '')) ?: $defaultTitle,
            'description' => trim((string) ($seo['description'] ?? '')) ?: $defaultDesc,
            'canonical'   => $seo['canonical'] ?? Url::full(Url::currentPath()),
            'robots'      => $seo['robots'] ?? 'index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1',
            'og_type'     => $seo['og_type'] ?? 'website',
            'og_image'    => !empty($seo['og_image']) ? Url::full((string) $seo['og_image']) : Url::full('/assets/img/og-default.png'),
            'site_name'   => $siteName,
            'keywords'    => $seo['keywords'] ?? 'GTA 6 cheat codes, GTA VI cheats, GTA 6 PS5 cheats, GTA 6 Xbox cheats',
            'published'   => $seo['published'] ?? null,
            'modified'    => $seo['modified'] ?? null,
            'schemas'     => $seo['schemas'] ?? [],
        ];
    }

    public static function organizationSchema(array $settings = []): array
    {
        $socials = array_values(array_filter([
            $settings['social_x'] ?? '',
            $settings['social_youtube'] ?? '',
            $settings['social_discord'] ?? '',
        ], static fn($u) => filter_var($u, FILTER_VALIDATE_URL)));

        return [
            '@context' => 'https://schema.org',
            '@type'    => 'Organization',
            'name'     => $settings['site_name'] ?? 'ViceCodes',
            'url'      => Url::full('/'),
            'logo'     => Url::full('/assets/img/favicon.svg'),
            'sameAs'   => $socials,
        ];
    }

    public static function websiteSchema(array $settings = []): array
    {
        return [
            '@context'        => 'https://schema.org',
            '@type'           => 'WebSite',
            'name'            => $settings['site_name'] ?? 'ViceCodes - GTA 6 Cheat Codes',
            'url'             => Url::full('/'),
            'potentialAction' => [
                '@type'       => 'SearchAction',
                'target'      => Url::full('/search?q={search_term_string}'),
                'query-input' => 'required name=search_term_string',
            ],
        ];
    }

    public static function videoGameSchema(): array
    {
        return [
            '@context'      => 'https://schema.org',
            '@type'         => 'VideoGame',
            'name'          => 'Grand Theft Auto VI',
            'alternateName' => ['GTA 6', 'GTA VI'],
            'datePublished' => '2026-11-19',
            'gamePlatform'  => ['PlayStation 5', 'Xbox Series X|S'],
            'publisher'     => [
                '@type' => 'Organization',
                'name'  => 'Rockstar Games',
            ],
            'genre'         => ['Action-Adventure', 'Open World'],
        ];
    }

    public static function breadcrumbSchema(array $items): array
    {
        $list = [];
        foreach ($items as $index => $item) {
            $list[] = [
                '@type'    => 'ListItem',
                'position' => $index + 1,
                'name'     => $item['label'],
                'item'     => Url::full($item['url']),
            ];
        }
        return [
            '@context'        => 'https://schema.org',
            '@type'           => 'BreadcrumbList',
            'itemListElement' => $list,
        ];
    }

    public static function faqSchema(array $faqs): ?array
    {
        $entities = [];
        foreach ($faqs as $faq) {
            $q = trim((string) ($faq['q'] ?? ''));
            $a = trim((string) ($faq['a'] ?? ''));
            if ($q === '' || $a === '') {
                continue;
            }
            $entities[] = [
                '@type'          => 'Question',
                'name'           => $q,
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text'  => $a,
                ],
            ];
        }

        if ($entities === []) {
            return null;
        }

        return [
            '@context'   => 'https://schema.org',
            '@type'      => 'FAQPage',
            'mainEntity' => $entities,
        ];
    }

    public static function itemListSchema(string $name, array $items): array
    {
        $list = [];
        foreach ($items as $i => $item) {
            $list[] = [
                '@type'    => 'ListItem',
                'position' => $i + 1,
                'name'     => $item['name'],
                'url'      => Url::full($item['url']),
            ];
        }
        return [
            '@context'        => 'https://schema.org',
            '@type'           => 'ItemList',
            'name'            => $name,
            'itemListElement' => $list,
        ];
    }

    public static function howToSchema(string $name, string $description, array $steps): array
    {
        $stepItems = [];
        foreach ($steps as $i => $step) {
            $stepItems[] = [
                '@type'    => 'HowToStep',
                'position' => $i + 1,
                'name'     => $step['name'],
                'text'     => $step['text'],
            ];
        }
        return [
            '@context'    => 'https://schema.org',
            '@type'       => 'HowTo',
            'name'        => $name,
            'description' => $description,
            'step'        => $stepItems,
        ];
    }

    public static function articleSchema(array $post, string $authorName, array $settings = []): array
    {
        $schema = [
            '@context'         => 'https://schema.org',
            '@type'            => 'BlogPosting',
            'headline'         => $post['title'],
            'description'      => $post['excerpt'] ?? $post['quick_answer'] ?? '',
            'mainEntityOfPage' => Url::full('/blog/' . $post['slug']),
            'datePublished'    => date('c', strtotime((string) ($post['published_at'] ?? $post['created_at']))),
            'dateModified'     => date('c', strtotime((string) ($post['updated_at'] ?? $post['created_at']))),
            'author'           => [
                '@type' => 'Person',
                'name'  => $authorName,
            ],
            'publisher'        => [
                '@type' => 'Organization',
                'name'  => $settings['site_name'] ?? 'ViceCodes',
                'logo'  => [
                    '@type' => 'ImageObject',
                    'url'   => Url::full('/assets/img/favicon.svg'),
                ],
            ],
        ];
        if (!empty($post['featured_image'])) {
            $schema['image'] = Url::full((string) $post['featured_image']);
        }
        return $schema;
    }
}
