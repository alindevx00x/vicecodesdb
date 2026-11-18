-- ============================================================================
-- GTA 6 Cheat Codes (ViceCodes) - Initial Seed Data
-- Date Context: November 17, 2026 (2 days prior to Nov 19, 2026 launch)
-- Honest Pre-Launch State: 0 Verified Codes until official release testing
-- ============================================================================

-- USE `gta6cheatcodes`;
SET NAMES utf8mb4;

-- 1. Default Admin User (Email: admin@vicecodes.com | Password: ViceCodes2026!)
INSERT INTO `users` (`id`, `email`, `password_hash`, `name`, `role`, `bio`) VALUES
(1, 'admin@vicecodes.com', '$2y$12$8B/jQEpLxQancW45P3qkC.0r6s9d.ODmsmwSVmiTfxb5k5vLLTIy2', 'ViceCodes Editorial Team', 'admin', 'Lead GTA 6 verification editors testing PS5 and Xbox Series X|S button combinations and phone cheats live on launch day.')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- 2. Cheat & Blog Categories
INSERT INTO `categories` (`id`, `name`, `slug`, `type`, `icon`, `description`, `meta_title`, `meta_description`, `sort_order`) VALUES
(1, 'Weapons & Combat', 'weapons-combat', 'cheat', 'crosshair', 'All weapon spawns, explosive ammo, flaming bullets, and combat modifiers for GTA 6.', 'GTA 6 Weapons & Combat Cheat Codes (PS5 & Xbox)', 'Complete list of GTA 6 weapon cheats, explosive ammo, and combat codes for PS5 and Xbox Series X|S with live verification status.', 1),
(2, 'Health & Armor', 'health-armor', 'cheat', 'shield', 'Invincibility, max health and body armor, and stamina refill codes in Leonida.', 'GTA 6 Health, Armor & Invincibility Cheat Codes', 'Find verified GTA 6 invincibility, max health, and full armor cheat codes for PS5 and Xbox Series X|S.', 2),
(3, 'Vehicles & Spawns', 'vehicles-spawns', 'cheat', 'car', 'Spawn supercars, Vice City classics, helicopters, boats, and off-road trucks instantly.', 'GTA 6 Vehicle Spawn Cheat Codes (Cars, Choppers, Boats)', 'Spawn sports cars, helicopters, jet skis, and classics in GTA 6 with our verified PS5 and Xbox vehicle cheat code list.', 3),
(4, 'Wanted Level', 'wanted-level', 'cheat', 'star', 'Raise or lower your Leonida police wanted level instantly during free roam.', 'GTA 6 Lower & Raise Wanted Level Cheat Codes', 'Clear cops fast or trigger a 5-star chase with GTA 6 wanted level cheat codes for PS5 and Xbox.', 4),
(5, 'Player Abilities', 'player-abilities', 'cheat', 'bolt', 'Super jump, fast run, fast swim, slow-motion aim, skyfall, and drunk mode.', 'GTA 6 Player Ability Cheat Codes (Super Jump, Skyfall, Fast Run)', 'Unlock super jump, fast run, skyfall, and slow-motion aiming in GTA 6 with verified controller sequences.', 5),
(6, 'Weather & World', 'weather-world', 'cheat', 'sun', 'Change Leonida weather, toggle moon gravity, slippery cars, and slow-motion gameplay.', 'GTA 6 Weather, Gravity & World Cheat Codes', 'Control tropical storms, Vice City sunsets, low gravity, and world physics in GTA 6 with verified codes.', 6),
(7, 'Launch & Verification', 'launch-verification', 'blog', 'newspaper', 'Live GTA 6 launch coverage, verification logs, and official Rockstar cheat announcements.', 'GTA 6 Cheat Codes News & Launch Updates', 'Latest GTA 6 cheat code news, launch verification logs, and official Rockstar updates.', 1),
(8, 'Guides & Mechanics', 'guides-mechanics', 'blog', 'book', 'How to enter cheats in GTA 6, trophy/achievement warnings, and GTA V comparisons.', 'GTA 6 Cheat Guides & Mechanics', 'Step-by-step guides on entering GTA 6 cheats, saving safely, and avoiding disabled trophies.', 2)
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- 3. Cheats (Honest Pre-Launch Entries: Coming Soon, Unconfirmed, Debunked)
INSERT INTO `cheats` (`id`, `title`, `slug`, `short_description`, `content`, `effects`, `verification_notes`, `category_id`, `status`, `is_featured`, `sort_order`, `last_verified_at`, `meta_title`, `meta_description`, `focus_keyword`, `faqs_json`, `changelog_json`, `views`, `published_at`) VALUES
(1, 'Invincibility (God Mode)', 'invincibility-god-mode', 'Temporarily makes Jason or Lucia immune to all gunfire, explosions, and fall damage in free roam.',
'<p><strong>Quick Answer:</strong> Rockstar Games has not published GTA 6 cheat codes ahead of the November 19, 2026 release. Based on GTA V, invincibility in GTA 6 is expected to last 5 minutes per activation and disable trophies/achievements for your current session.</p><h2>How Invincibility Works in Grand Theft Auto</h2><p>In previous Rockstar titles, God Mode grants temporary immunity to bullets, melee attacks, fire, and explosions while leaving mission fail states intact. Once GTA 6 unlocks on PS5 and Xbox Series X|S, our editorial team will test button sequences and cell phone dial codes live and update this page immediately.</p><h2>Expected Requirements &amp; Trophy Warning</h2><ul><li><strong>Story Mode Only:</strong> Cheats never work inside GTA 6 Online / multiplayer modes.</li><li><strong>Trophy Lock:</strong> Entering any cheat disables PlayStation Trophies and Xbox Achievements until you reload a clean save file.</li><li><strong>Duration:</strong> Historically capped at 5 minutes (re-enter to refresh).</li></ul>',
'Grants 5 minutes of immunity to bullets, explosions, fire, and fall damage.',
'Pre-launch placeholder tracked for Nov 19, 2026 launch testing. 0 verified sequences published by Rockstar as of Nov 17, 2026.',
2, 'coming_soon', 1, 1, '2026-11-17 10:00:00',
'GTA 6 Invincibility Cheat Code (God Mode) - PS5 & Xbox Status',
'Looking for the GTA 6 Invincibility (God Mode) cheat code? Check live verification status for PS5 and Xbox Series X|S ahead of the Nov 19, 2026 launch.',
'GTA 6 invincibility cheat code',
'[{"q":"Is there an official GTA 6 invincibility cheat code yet?","a":"No. As of November 17, 2026, Rockstar Games has not released official cheat codes before the November 19, 2026 launch. This page updates live on release day."},{"q":"Does God Mode disable trophies in GTA 6?","a":"Yes, like GTA V and Red Dead Redemption 2, activating cheat codes is expected to disable trophies and achievements for that session until you reload an uncheated save."}]',
'[{"date":"2026-11-17","note":"Page created with pre-launch tracking status (Coming at Launch: Nov 19, 2026)."}]',
1420, '2026-11-17 09:00:00'),

(2, 'Max Health & Full Armor', 'max-health-and-armor', 'Instantly restores player health to 100%, refills body armor, and repairs your current vehicle.',
'<p><strong>Quick Answer:</strong> No verified button sequence exists yet for GTA 6 Max Health &amp; Armor prior to the November 19, 2026 launch. This entry is queued for immediate launch-day verification on PS5 and Xbox Series X|S.</p><h2>What Max Health &amp; Armor Typically Restores</h2><p>Historically one of the fastest emergency cheats in Grand Theft Auto, this input refills your health bar, tops up your ballistic vest, and in GTA V also repaired the vehicle you were driving. Save before activating to keep trophy progress intact.</p>',
'Refills health bar, adds full body armor, and repairs active vehicle.',
'Queued for Day-1 verification on PS5 DualSense and Xbox Series X|S controllers.',
2, 'coming_soon', 1, 2, '2026-11-17 10:00:00',
'GTA 6 Max Health & Armor Cheat Code (PS5 & Xbox Series X|S)',
'Track the official GTA 6 Max Health and Full Armor cheat code for PS5 and Xbox. Updated live at launch with verified controller button combos.',
'GTA 6 max health armor cheat',
'[{"q":"How do you refill health and armor with cheats in GTA 6?","a":"Official controller button sequences and phone numbers will be tested and published here on November 19, 2026 as soon as the game unlocks."}]',
'[{"date":"2026-11-17","note":"Queued for launch-day verification."}]',
980, '2026-11-17 09:05:00'),

(3, 'Lower Wanted Level', 'lower-wanted-level', 'Removes one wanted star per activation so you can escape Leonida Police Department pursuits.',
'<p><strong>Quick Answer:</strong> The GTA 6 Lower Wanted Level cheat code is not yet confirmed because the game launches November 19, 2026. Check back on launch day for verified PS5 and Xbox button inputs.</p><h2>Escaping Police in Leonida</h2><p>Lower Wanted Level cheats traditionally remove one star each time you enter the combination, letting you drop from a 5-star Vice City chase down to zero heat in seconds.</p>',
'Decreases current police wanted level by 1 star per input.',
'Awaiting official release on Nov 19, 2026. Rumored GTA V sequence (R1, R1, Circle, R2...) is unverified for GTA 6.',
4, 'coming_soon', 1, 3, '2026-11-17 10:00:00',
'GTA 6 Lower Wanted Level Cheat Code (Clear Cops) - PS5 & Xbox',
'Need to lose the cops fast in GTA 6? Track the verified Lower Wanted Level cheat code for PS5 and Xbox Series X|S.',
'GTA 6 lower wanted level cheat',
'[{"q":"Does the GTA V lower wanted level cheat work in GTA 6?","a":"We cannot verify whether GTA V inputs carry over until GTA 6 launches on November 19, 2026. Never trust sites claiming verified codes prior to release."}]',
'[{"date":"2026-11-17","note":"Added to pre-launch tracker."}]',
1150, '2026-11-17 09:10:00'),

(4, 'Give All Weapons & Ammo', 'give-all-weapons-ammo', 'Unlocks a full arsenal of pistols, SMGs, rifles, heavy weapons, grenades, and extra ammunition.',
'<p><strong>Quick Answer:</strong> A GTA 6 All Weapons cheat code has not been confirmed ahead of launch. Once GTA 6 releases on November 19, 2026, we will verify weapon wheel unlocks on both PS5 and Xbox Series X|S.</p><h2>Weapon Wheel &amp; Arsenal Cheats</h2><p>Because GTA 6 introduces a more realistic weapon carry system alongside duffel/trunk storage, Rockstar may modify how the classic weapon pack cheat behaves. Our launch team will document exact weapons granted.</p>',
'Adds full weapon loadout and ammo reserves to inventory.',
'Pending launch testing on PS5 and Xbox Series X|S.',
1, 'coming_soon', 1, 4, '2026-11-17 10:00:00',
'GTA 6 All Weapons Cheat Code (Guns & Ammo) - PS5 & Xbox',
'Get the verified GTA 6 All Weapons and Ammo cheat code for PS5 and Xbox Series X|S as soon as it is confirmed at launch.',
'GTA 6 all weapons cheat code',
'[{"q":"Can you spawn every weapon in GTA 6 with a cheat?","a":"Official weapon cheat confirmation will go live on November 19, 2026 after hands-on testing."}]',
'[{"date":"2026-11-17","note":"Initial pre-launch entry."}]',
890, '2026-11-17 09:15:00'),

(5, 'Spawn Vice Supercar (Cheetah Classic)', 'spawn-vice-supercar', 'Spawns a high-speed Vice City sports car directly in front of your character.',
'<p><strong>Quick Answer:</strong> Rumored social media sequences claiming to spawn a Cheetah Classic or Comet in GTA 6 are currently <strong>unconfirmed</strong>. Official vehicle spawn codes will be tested at launch on November 19, 2026.</p><h2>Why Early Leaks Are Marked Unconfirmed</h2><p>Several TikTok and YouTube posts are circulating recycled GTA V vehicle codes (like the Comet or Rapid GT) labeled as GTA 6 leaks. Until our team tests them on retail PS5 and Xbox builds, we strictly mark these as Unconfirmed.</p>',
'Spawns a sports car in clear road space ahead of player.',
'Circulating social media sequences match GTA V Comet/Rapid GT inputs and remain UNCONFIRMED for GTA 6.',
3, 'unconfirmed', 1, 5, '2026-11-17 10:00:00',
'GTA 6 Spawn Supercar Cheat Code - Unconfirmed Rumor Tracker',
'Track rumors vs verified facts for GTA 6 vehicle and supercar spawn cheat codes on PS5 and Xbox Series X|S.',
'GTA 6 spawn car cheat code',
'[{"q":"Are the GTA 6 car spawn codes on TikTok real?","a":"Most pre-launch videos reuse GTA V button combinations. Until GTA 6 launches on November 19, 2026, all vehicle spawn codes remain unconfirmed."}]',
'[{"date":"2026-11-17","note":"Flagged circulating GTA V copycat codes as Unconfirmed."}]',
760, '2026-11-17 09:20:00'),

(6, 'Unlimited Money Glitch / Cheat ($999,999,999)', 'unlimited-money-cheat', 'Claims of a button code that grants instant cash in GTA 6 Story Mode or GTA Online.',
'<p><strong>Quick Answer:</strong> <strong>Debunked.</strong> Rockstar Games has never included an infinite money button cheat in GTA IV, GTA V, or Red Dead Redemption 2 Story Mode, and any site offering an "Unlimited GTA 6 Online Money Generator" is fake or malicious.</p><h2>Why Money Cheats Are Debunked</h2><ul><li>Modern GTA titles intentionally exclude direct cash cheat codes to preserve Story Mode progression.</li><li>Online currency generators violate Rockstar ToS and often attempt account phishing.</li><li>Legitimate in-game money methods (heists, stock/property systems, robberies) will be covered in our Guides section after launch.</li></ul>',
'None: marked Debunked to protect players from fake generators.',
'Debunked by ViceCodes editors: Rockstar has not supported money button cheats since GTA San Andreas, and online generators are scams.',
5, 'debunked', 0, 6, '2026-11-17 10:00:00',
'Is There a GTA 6 Unlimited Money Cheat Code? (Debunked)',
'Fact-checking GTA 6 unlimited money cheat codes and generators. Learn why money button codes are debunked and how to stay safe.',
'GTA 6 money cheat code',
'[{"q":"Is there an unlimited money cheat code in GTA 6?","a":"No. Rockstar removed direct money cheat codes after GTA San Andreas, and any site claiming an unlimited money generator for GTA 6 is fake."}]',
'[{"date":"2026-11-17","note":"Marked Debunked based on Rockstar legacy policy and pre-launch scam warnings."}]',
640, '2026-11-17 09:25:00'),

(7, 'Super Jump & Skyfall', 'super-jump-skyfall', 'Enables massive vertical leaps or teleports your character high above Leonida for a skydiving drop.',
'<p><strong>Quick Answer:</strong> Super Jump and Skyfall are expected fan-favorite GTA 6 cheats, but verified button combos will only be available once the game launches on November 19, 2026.</p>',
'Increases jump height dramatically or spawns player mid-air for skydiving.',
'Pre-launch placeholder for November 19, 2026 testing.',
5, 'coming_soon', 0, 7, '2026-11-17 10:00:00',
'GTA 6 Super Jump & Skyfall Cheat Codes (PS5 & Xbox)',
'Check the status of GTA 6 Super Jump and Skyfall cheat codes for PS5 and Xbox Series X|S.',
'GTA 6 super jump cheat',
'[]',
'[{"date":"2026-11-17","note":"Added to pre-launch ability tracker."}]',
410, '2026-11-17 09:30:00'),

(8, 'Change Weather & Tropical Storm', 'change-weather-tropical-storm', 'Cycles through Leonida weather states: sunny Vice sunset, overcast, tropical storm, and clear night.',
'<p><strong>Quick Answer:</strong> Weather cycle codes for GTA 6 will be verified at launch on November 19, 2026. Expect cycling through sunny, rain, thunderstorm, and fog states.</p>',
'Cycles active world weather and lighting conditions.',
'Pre-launch placeholder for November 19, 2026 testing.',
6, 'coming_soon', 0, 8, '2026-11-17 10:00:00',
'GTA 6 Weather Cheat Code (Rain, Storm, Sunny) - PS5 & Xbox',
'Change weather in Vice City and Leonida with our upcoming verified GTA 6 Weather Cheat Code tracker.',
'GTA 6 weather cheat code',
'[]',
'[{"date":"2026-11-17","note":"Added to weather & world tracker."}]',
390, '2026-11-17 09:35:00')
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`);

-- 4. Platform Rows for Cheats
INSERT INTO `cheat_platforms` (`cheat_id`, `platform`, `code_sequence`, `cell_number`, `notes`) VALUES
(1, 'ps5', 'Coming at launch (Nov 19, 2026)', NULL, 'Button sequence will be verified live on PS5 DualSense.'),
(1, 'xbox', 'Coming at launch (Nov 19, 2026)', NULL, 'Button sequence will be verified live on Xbox Series X|S.'),
(1, 'pc', 'Pending PC release announcement', NULL, 'PC version not yet released at console launch.'),
(2, 'ps5', 'Coming at launch (Nov 19, 2026)', NULL, 'PS5 verification queued.'),
(2, 'xbox', 'Coming at launch (Nov 19, 2026)', NULL, 'Xbox Series X|S verification queued.'),
(3, 'ps5', 'Coming at launch (Nov 19, 2026)', NULL, 'PS5 verification queued.'),
(3, 'xbox', 'Coming at launch (Nov 19, 2026)', NULL, 'Xbox Series X|S verification queued.'),
(4, 'ps5', 'Coming at launch (Nov 19, 2026)', NULL, 'PS5 verification queued.'),
(4, 'xbox', 'Coming at launch (Nov 19, 2026)', NULL, 'Xbox Series X|S verification queued.'),
(5, 'ps5', 'UNCONFIRMED: Rumored GTA V carryover', NULL, 'Do not rely on pre-launch leaked codes until Nov 19 testing.'),
(5, 'xbox', 'UNCONFIRMED: Rumored GTA V carryover', NULL, 'Awaiting retail build check.'),
(6, 'ps5', 'NO CODE (Debunked)', NULL, 'Rockstar does not provide infinite money button cheats.'),
(6, 'xbox', 'NO CODE (Debunked)', NULL, 'Rockstar does not provide infinite money button cheats.'),
(7, 'ps5', 'Coming at launch (Nov 19, 2026)', NULL, 'PS5 verification queued.'),
(7, 'xbox', 'Coming at launch (Nov 19, 2026)', NULL, 'Xbox Series X|S verification queued.'),
(8, 'ps5', 'Coming at launch (Nov 19, 2026)', NULL, 'PS5 verification queued.'),
(8, 'xbox', 'Coming at launch (Nov 19, 2026)', NULL, 'Xbox Series X|S verification queued.')
ON DUPLICATE KEY UPDATE `code_sequence` = VALUES(`code_sequence`);

-- 5. Blog Posts (AEO + GEO Optimized)
INSERT INTO `blog_posts` (`id`, `title`, `slug`, `excerpt`, `quick_answer`, `content`, `category_id`, `featured_image`, `featured_image_alt`, `status`, `author_id`, `meta_title`, `meta_description`, `focus_keyword`, `faqs_json`, `reading_time`, `views`, `published_at`) VALUES
(1, 'Does GTA 6 Have Cheat Codes? Current Verification Status (Nov 2026)',
'does-gta-6-have-cheat-codes-status',
'Everything we know about GTA 6 cheat codes ahead of the November 19, 2026 launch, including how we verify PS5 and Xbox inputs.',
'As of November 17, 2026, Rockstar Games has not officially published any GTA 6 cheat codes ahead of the November 19, 2026 console launch. Zero codes are verified right now. Any website claiming confirmed GTA 6 button combinations before release day is recycling GTA V cheats or guessing.',
'<h2>Current Status of GTA 6 Cheat Codes</h2><p>With <strong>Grand Theft Auto VI</strong> launching worldwide on <strong>November 19, 2026</strong> for PlayStation 5 and Xbox Series X|S, players are naturally searching for invincibility, weapon packs, and Vice City supercar spawn codes. Right now, our verified counter sits honestly at <strong>0 Verified Codes</strong>.</p><h2>Why Honesty Matters Before Launch</h2><p>Every major Rockstar release spawns thousands of copy-paste articles claiming "confirmed" cheats days before the game unlocks. At ViceCodes, we refuse to publish unverified GTA V codes as working GTA 6 inputs. Each code on our directory carries an explicit badge:</p><ul><li><strong>Verified:</strong> Tested directly on retail PS5 or Xbox Series X|S builds by our editors.</li><li><strong>Unconfirmed:</strong> Community rumor or datamined string awaiting hands-on confirmation.</li><li><strong>Debunked:</strong> Proven false (such as online money generators).</li><li><strong>Coming at Launch:</strong> Tracked slot ready for immediate November 19 testing.</li></ul><h2>What Cheats Are Most Likely to Return?</h2><p>Looking at Rockstar''s history across GTA V and Red Dead Redemption 2, expect core single-player sandbox modifiers: Raise/Lower Wanted Level, Invincibility (5-minute timer), Max Health &amp; Armor, Weapons Loadout, Weather Cycle, Super Jump, and select Vehicle Spawns.</p><h2>How to Get Notified the Minute Codes Go Live</h2><p>Join our Launch Alert newsletter below. We will send a single clean digest the moment our team confirms the first wave of working PS5 and Xbox Series X|S codes on November 19.</p>',
7, NULL, 'GTA 6 Leonida neon skyline cheat status guide', 'published', 1,
'Does GTA 6 Have Cheat Codes? Official Status (Nov 2026)',
'Are there GTA 6 cheat codes yet? Read our honest pre-launch status tracker for PS5 and Xbox Series X|S ahead of the Nov 19, 2026 release.',
'does GTA 6 have cheat codes',
'[{"q":"Are any GTA 6 cheat codes confirmed before November 19, 2026?","a":"No. Rockstar Games has not released official cheat codes before launch. ViceCodes will verify and publish working PS5 and Xbox codes live on November 19, 2026."},{"q":"Will GTA 6 cheat codes work on both PS5 and Xbox Series X|S?","a":"Yes, Rockstar traditionally supports both controller button sequences and in-game phone dial numbers across PlayStation and Xbox single-player story modes."}]',
4, 2150, '2026-11-17 08:00:00'),

(2, 'How to Enter Cheat Codes in GTA 6 (PS5, Xbox & Phone Guide)',
'how-to-enter-cheat-codes-in-gta-6',
'Learn the two ways Rockstar games accept cheat inputs: fast controller button sequences and in-game smartphone dialing.',
'In Rockstar''s modern GTA titles, cheat codes are entered either by pressing a rapid sequence of controller buttons during free roam or by dialing a specific phone number on your character''s smartphone. Always create a manual save first, because activating any cheat disables trophies and achievements for that session.',
'<h2>Method 1: Controller Button Sequences (PS5 &amp; Xbox)</h2><p>When standing in free roam (not inside a cutscene or pause menu), input the directional D-Pad and face buttons smoothly without long pauses. A small notification banner appears above the radar when accepted.</p><h2>Method 2: Smartphone Dial Pad</h2><p>Since GTA IV and GTA V, Rockstar has also let players dial numeric codes (like <code>1-999-...</code>) on the in-game phone keypad. Phone cheats are easier to type without timing pressure and work identically to button combos.</p><h2>Important Rules Before Using Any Cheat</h2><table><thead><tr><th>Rule</th><th>Impact in GTA Story Mode</th></tr></thead><tbody><tr><td>Trophies &amp; Achievements</td><td>Disabled for the current session after activating a cheat.</td></tr><tr><td>Manual Save Files</td><td>Save before cheating, then reload that save to re-enable trophies.</td></tr><tr><td>Online / Multiplayer</td><td>Cheats never function in GTA Online modes.</td></tr></tbody></table>',
8, NULL, 'PS5 DualSense and Xbox controller cheat input guide for GTA 6', 'published', 1,
'How to Enter Cheat Codes in GTA 6 (PS5, Xbox & Phone)',
'Step-by-step guide on how to enter GTA 6 cheat codes using PS5 DualSense, Xbox Series X|S controllers, and the in-game phone without losing trophies.',
'how to enter cheat codes in GTA 6',
'[{"q":"How do I re-enable trophies after using cheats in GTA 6?","a":"Save your game before entering any cheat code. When finished messing around, quit or reload your clean pre-cheat save file to restore trophy and achievement tracking."}]',
5, 1640, '2026-11-16 14:00:00'),

(3, 'GTA 5 vs GTA 6 Cheats: What Rockstar Could Change in Leonida',
'gta-5-vs-gta-6-cheats-comparison',
'Comparing GTA V''s 34 classic codes with what could evolve in GTA 6''s dual-protagonist Story Mode in Vice City and Leonida.',
'GTA V featured 34 official cheat codes covering weapons, vehicles, weather, and player physics. In GTA 6, dual protagonists Jason and Lucia, updated vehicle realism, and a larger Leonida map could introduce new watercraft/airboat spawns and refined weapon-bag cheats while keeping classic sandbox favorites.',
'<h2>What Stayed Consistent Across GTA Titles</h2><p>From Vice City (2002) to Los Santos (2013), Rockstar''s cheat philosophy has focused on pure sandbox fun: chaotic police chases, stunt spawns, weather shifts, and physics modifiers like Moon Gravity and Slidey Cars.</p><h2>New Possibilities in Vice City &amp; Leonida</h2><ul><li><strong>Watercraft &amp; Airboats:</strong> With the Grassrivers wetlands featured prominently in Leonida, an airboat or swamp vehicle spawn cheat would fit naturally.</li><li><strong>Dual Protagonist Support:</strong> Switching between Jason and Lucia may require re-activating temporary timers like Invincibility if a character swap clears active buffs.</li><li><strong>Cell Phone Integration:</strong> Expect cleaner smartphone contact saving or quick-dial numbers alongside classic D-Pad combos.</li></ul>',
8, NULL, 'Comparison of GTA V and GTA 6 cheat mechanics in Vice City', 'published', 1,
'GTA 5 vs GTA 6 Cheat Codes: What Will Change in Vice City?',
'Explore how GTA 6 cheat codes in Leonida may evolve from GTA V, from airboat spawns to dual-protagonist mechanics and phone dialing.',
'GTA 5 vs GTA 6 cheats',
'[{"q":"Will GTA 5 cheat codes work in GTA 6?","a":"Some classic button patterns may feel similar, but Rockstar historically changes exact combinations between titles. Check our verified badge before entering any code."}]',
4, 1120, '2026-11-15 11:30:00'),

(4, 'Do GTA 6 Cheats Disable PS5 Trophies & Xbox Achievements?',
'do-gta-6-cheats-disable-trophies-achievements',
'How GTA 6 cheat activation affects PlayStation 5 Trophies, Xbox Series X|S Achievements, and how to protect your Story Mode save file.',
'Yes—based on every modern Rockstar title including GTA V and Red Dead Redemption 2, activating any cheat code in GTA 6 will disable PlayStation Trophies and Xbox Achievements for your current play session. Always create a separate manual save before entering cheats.',
'<h2>Why Rockstar Locks Trophies When Cheats Are Active</h2><p>Since GTA IV, Rockstar Games has enforced a strict rule: the moment you enter a controller button combination or dial a cheat phone number, an in-game banner warns you that <strong>Trophies / Achievements are disabled</strong> for the remainder of that session.</p><h2>How to Use Cheats Without Losing Trophy Progress</h2><ol><li><strong>Create a Clean Manual Save:</strong> Open the pause menu and save your Story Mode progress into a dedicated slot before testing any codes.</li><li><strong>Turn Off Auto-Save During Chaos Runs:</strong> Disable auto-save temporarily so a cheated session never overwrites your primary progression slot.</li><li><strong>Reload to Re-Enable Trophies:</strong> When you are ready to earn trophies again, close or reload your clean pre-cheat save file—the lock only applies to the active cheated session.</li></ol><h2>What About GTA 6 Online?</h2><p>Cheat codes only work in single-player Story Mode. Attempting to enter button combos in online multiplayer has no effect and will never unlock online cash or RP.</p>',
8, NULL, 'PS5 Trophies and Xbox Achievements warning for GTA 6 cheat codes', 'published', 1,
'Do GTA 6 Cheats Disable PS5 Trophies & Xbox Achievements?',
'Learn whether GTA 6 cheat codes lock PS5 Trophies and Xbox Achievements, and how to safely back up your Story Mode save before activating codes.',
'do GTA 6 cheats disable trophies',
'[{"q":"Are trophies permanently disabled if I use a cheat in GTA 6?","a":"No. Trophies and achievements are only locked for that active session. Reloading a clean manual save made before you entered any cheat restores trophy eligibility."},{"q":"Can I save my game after activating GTA 6 cheats?","a":"In GTA V and RDR2, saving while cheats are active can either be blocked or taint that specific save slot. Keep one dedicated clean save slot untouched."}]',
3, 940, '2026-11-17 14:00:00'),

(5, 'GTA 6 Director Mode & Sandbox Modifiers: What We Know Before Launch',
'gta-6-director-mode-sandbox-modifiers',
'Everything we know about Director Mode, Actor/World modifiers, and sandbox toggles in GTA 6 ahead of the November 19, 2026 release.',
'Rockstar Games has not officially confirmed whether a standalone Director Mode returns at GTA 6 launch on November 19, 2026. If included alongside Story Mode cheat codes, expect weather, wanted-level, and invincibility toggles similar to GTA V''s Interaction Menu.',
'<h2>What Director Mode Offered in GTA V</h2><p>In GTA V, Director Mode let players step into a dedicated sandbox studio with instant toggles for <strong>Invincibility, Low Gravity, Explosive Melee, Super Jump, Wanted Status, and Time of Day</strong>—without manually dialing phone numbers or memorizing D-Pad sequences.</p><h2>Will GTA 6 Include Director Mode on Day 1?</h2><p>Historically, Rockstar added Director Mode to GTA V during the PC and next-gen console era rather than on original launch day. Ahead of GTA 6''s November 19 console release, our editors will check the Pause Menu and Interaction Menu immediately at unlock to confirm whether Director Mode or a similar Leonida Sandbox menu is present from Day 1.</p><h2>Tracked Sandbox Modifiers</h2><ul><li><strong>Wanted Level Lock:</strong> Freeze or disable Leonida police response.</li><li><strong>Physics &amp; Weather Toggles:</strong> Moon gravity, slidey cars, and tropical storm cycles.</li><li><strong>Combat Modifiers:</strong> Explosive rounds, flaming bullets, and infinite stamina.</li></ul>',
7, NULL, 'GTA 6 Director Mode and sandbox modifier overview', 'published', 1,
'GTA 6 Director Mode & Sandbox Modifiers (2026 Launch Tracker)',
'Does GTA 6 have Director Mode at launch? Track expected sandbox toggles, world modifiers, and how Director Mode compares to classic Story Mode cheats.',
'GTA 6 Director Mode',
'[{"q":"Does GTA 6 have Director Mode at launch?","a":"Rockstar has not confirmed Director Mode ahead of the November 19, 2026 launch. ViceCodes will verify whether a sandbox studio menu is included on Day 1."}]',
3, 610, '2026-11-17 18:00:00'),

(6, 'All Tracked GTA 6 Vehicle Categories & Rumored Spawns',
'gta-6-vehicle-categories-rumored-spawns',
'Breakdown of every GTA 6 vehicle class tracked for spawn cheat codes—supercars, muscle classics, helicopters, boats, and Leonida off-roaders.',
'Ahead of GTA 6''s November 19, 2026 launch, ViceCodes is tracking spawn cheat slots for Supercars, Vice City Muscle Classics, Helicopters, Boats/Jet Skis, Off-Road Trucks, and Motorcycles. Zero spawn codes are verified until retail unlock.',
'<h2>Vehicle Categories Tracked for Launch Day</h2><p>Rockstar''s trailers and Leonida showcase highlighted massive vehicle variety across Vice City, Port Gellhorn, and the Grassrivers. Based on GTA V''s vehicle spawn roster, we have prepared dedicated tracker pages for the most likely spawnable classes:</p><ul><li><strong>Supercars &amp; Sports Classics:</strong> Expect rapid spawns akin to the Comet and Rapid GT—check our <a href="/cheats/vehicles">Vehicles &amp; Spawns category</a> on launch morning.</li><li><strong>Helicopters &amp; Aircraft:</strong> Armed Buzzard-style choppers and stunt planes for fast traversal across Leonida.</li><li><strong>Watercraft &amp; Swamp Airboats:</strong> With the Vice Keys and Grassrivers wetlands central to the map, boat or airboat spawns are high on our Day-1 test checklist.</li><li><strong>Motorcycles &amp; Dirt Bikes:</strong> Agile PCJ-style street bikes and Sanchez off-roaders.</li></ul><h2>Important Spawn Rules to Remember</h2><p>In Rockstar games, vehicle spawn cheats require open flat ground in front of your character. Trying to spawn a helicopter or sports car inside tight alleys or indoors usually triggers a "Can''t spawn vehicle here" message.</p>',
8, NULL, 'Tracked GTA 6 vehicle spawn categories for Vice City and Leonida', 'published', 1,
'All Tracked GTA 6 Vehicle Categories & Rumored Spawn Cheats',
'Explore every GTA 6 vehicle category we are tracking for spawn cheats on PS5 and Xbox Series X|S, from Vice City supercars to helicopters and boats.',
'GTA 6 vehicle spawn cheats',
'[{"q":"Can you spawn any car in GTA 6 with a cheat code?","a":"Usually no. Rockstar limits vehicle spawn cheats to a curated list of 8 to 12 iconic cars, bikes, boats, and helicopters rather than every vehicle in the game."}]',
4, 780, '2026-11-18 01:00:00')
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`);

-- 6. Static Pages (Editable via Admin + TinyMCE)
INSERT INTO `pages` (`id`, `title`, `slug`, `quick_answer`, `content`, `meta_title`, `meta_description`, `focus_keyword`, `status`) VALUES
(1, 'How We Verify GTA 6 Cheat Codes', 'how-we-verify',
'Every GTA 6 cheat code listed as Verified on ViceCodes is tested live on retail PS5 and Xbox Series X|S builds by our editorial team, cross-checked across both button sequences and phone numbers, and stamped with the exact date and patch version.',
'<h2>Our 4-Step Verification Standard</h2><p>Search results around major game launches are flooded with AI-generated guesses and fake codes. ViceCodes exists to give players one source they can actually trust.</p><ol><li><strong>Retail Console Testing:</strong> We test every code on unmodded retail PlayStation 5 and Xbox Series X|S hardware.</li><li><strong>Cross-Verification:</strong> Two editors independently reproduce the effect, duration, and trophy-warning prompt.</li><li><strong>Honest Labeling:</strong> Anything not directly reproduced stays marked <em>Coming at Launch</em>, <em>Unconfirmed</em>, or <em>Debunked</em>.</li><li><strong>Patch Tracking:</strong> After every Rockstar Title Update, we re-verify top cheats and record updates in each cheat''s changelog.</li></ol><h2>Status Definitions</h2><ul><li><strong>Verified:</strong> Confirmed working on current game version.</li><li><strong>Unconfirmed:</strong> Reported by the community or leaks, not yet reproduced in-house.</li><li><strong>Debunked:</strong> Tested and proven false or an outright scam (e.g., online money generators).</li><li><strong>Coming at Launch:</strong> Tracked slot awaiting November 19, 2026 release.</li></ul>',
'How We Verify GTA 6 Cheat Codes | ViceCodes Methodology',
'Learn how ViceCodes tests, reproduces, and timestamps every GTA 6 cheat code on PS5 and Xbox Series X|S so you never waste time on fake codes.',
'GTA 6 cheat verification', 'published'),

(2, 'About ViceCodes (GTA 6 Cheat Codes)', 'about',
'ViceCodes is an independent, fan-run Grand Theft Auto VI resource built to deliver the fastest, cleanest, and most honestly verified database of GTA 6 cheat codes, controller inputs, and Leonida guides.',
'<h2>Built for Speed, Honesty &amp; Vice City Vibes</h2><p>ViceCodes launched ahead of GTA 6''s November 19, 2026 release with one mission: cut through clickbait and deliver instant, copy-ready PS5, Xbox Series X|S, and PC cheat sequences the moment they are verified.</p><h2>Editorial Independence</h2><p>We are an unofficial fan publication and are not affiliated with Rockstar Games or Take-Two Interactive. Whenever a code is unconfirmed or debunked, we say so plainly.</p>',
'About ViceCodes | Trusted GTA 6 Cheat Codes Resource',
'About ViceCodes: an independent GTA 6 cheat codes database focused on verified PS5, Xbox, and PC codes, speed, and zero clickbait.',
'About ViceCodes', 'published'),

(3, 'Disclaimer', 'disclaimer',
'ViceCodes is an unofficial fan resource not affiliated with Rockstar Games or Take-Two Interactive. All cheat codes are community-discovered and used at your own risk; activating cheats disables trophies and achievements.',
'<h2>Unofficial Fan Resource</h2><p>ViceCodes is an independent, unofficial fan website dedicated to <em>Grand Theft Auto VI</em>. Grand Theft Auto, GTA 6, Vice City, Leonida, and Rockstar Games are trademarks of Take-Two Interactive Software, Inc. No affiliation or endorsement is implied.</p><h2>Trophies, Achievements &amp; Save Risk</h2><p>Cheat codes are discovered by the gaming community and verified in single-player Story Mode only. Using cheats disables PlayStation Trophies and Xbox Achievements for your active session and may cause unexpected physics behavior. Always create a manual backup save before entering any code. Always prioritize official Rockstar Games communications for authoritative announcements.</p>',
'Disclaimer | ViceCodes GTA 6 Cheat Codes',
'Unofficial fan resource disclaimer, trademark notice, and trophy/achievement warning for GTA 6 cheat codes.',
'GTA 6 cheats disclaimer', 'published'),

(4, 'Privacy Policy', 'privacy-policy',
'ViceCodes collects only the minimum data needed to run our cheat launch newsletter (with double opt-in consent) and contact form, and never sells your email or personal information.',
'<h2>Information We Collect</h2><p>We respect your privacy. When you subscribe to our Launch Alert newsletter, we store your email address, optional name, consent timestamp, and confirmation status via double opt-in.</p><h2>Cookies &amp; Analytics</h2><p>We use essential security cookies (such as CSRF protection) and optional privacy-conscious analytics to understand which cheat categories help players most.</p><h2>Your Rights &amp; Unsubscribing</h2><p>Every newsletter email contains a one-click unsubscribe link. You may also contact us anytime to request deletion of your email record.</p>',
'Privacy Policy | ViceCodes',
'Privacy policy for ViceCodes covering newsletter double opt-in, GDPR rights, cookies, and analytics.',
'ViceCodes privacy policy', 'published'),

(5, 'Terms of Use', 'terms',
'By browsing ViceCodes, you agree to use our guides and cheat listings for lawful personal single-player entertainment and acknowledge that cheat behavior depends on official Rockstar game updates.',
'<h2>Acceptance of Terms</h2><p>By accessing ViceCodes, you agree to these Terms of Use and our Disclaimer. Content is provided "as is" for informational and entertainment purposes.</p><h2>Intellectual Property</h2><p>Original editorial guides, verification logs, and site design belong to ViceCodes. Game titles and trademarks belong to their respective owners.</p>',
'Terms of Use | ViceCodes',
'Terms of Use for ViceCodes GTA 6 Cheat Codes website.',
'ViceCodes terms of use', 'published'),

(6, 'Contact Us', 'contact',
'Have a newly discovered GTA 6 cheat code to submit, a correction to report, or a media inquiry? Use our direct contact form below and our verification editors will review it promptly.',
'<p>Submit newly discovered PS5, Xbox Series X|S, or phone cheat combinations with proof or reproduction steps, report broken links, or reach our editorial team directly.</p>',
'Contact ViceCodes | Submit a GTA 6 Cheat Code',
'Contact ViceCodes to submit a newly discovered GTA 6 cheat code, report a correction, or reach our editors.',
'Contact ViceCodes', 'published')
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`);

-- 7. Site Settings
INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('site_name', 'ViceCodes - GTA 6 Cheat Codes'),
('site_tagline', 'Verified GTA 6 Cheat Codes for PS5, Xbox Series X|S & PC'),
('launch_date', '2026-11-19T00:00:00Z'),
('launch_mode', 'pre_launch'),
('launch_banner_text', '0 Verified Codes (as of Nov 17, 2026) | Updated live at launch on Nov 19, 2026'),
('default_meta_title', 'GTA 6 Cheat Codes (PS5, Xbox, PC) - Live Verified List | ViceCodes'),
('default_meta_description', 'The fastest, most trusted list of GTA 6 cheat codes for PS5, Xbox Series X|S, and PC. Honest verification badges, one-click copy, phone codes, and Leonida guides.'),
('ga4_id', ''),
('plausible_domain', ''),
('social_x', 'https://x.com/'),
('social_youtube', 'https://youtube.com/'),
('social_discord', 'https://discord.com/'),
('contact_email', 'editors@vicecodesdb.com'),
('maintenance_mode', '0'),
('footer_disclaimer', 'Unofficial fan resource. Not affiliated with Rockstar Games or Take-Two Interactive. Cheat codes are community-discovered; using cheats may disable trophies/achievements and is at your own risk.'),
('indexnow_enabled', '1'),
('indexnow_key', '8f4c2b9e1a6d4307b5e92c4a1d7f0832'),
('page_cache_enabled', '1'),
('mail_driver', 'log'),
('mail_from', 'noreply@vicecodesdb.com'),
('mail_api_key', ''),
('smtp_host', ''),
('smtp_port', '587'),
('smtp_user', ''),
('smtp_pass', ''),
('smtp_encryption', 'tls'),
('admin_2fa_enabled', '0'),
('admin_2fa_secret', ''),
('admin_ip_allowlist', '')
ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`);

-- 8. Internal Linking Tags & Relationships
INSERT INTO `tags` (`id`, `name`, `slug`) VALUES
(1, 'PS5 DualSense', 'ps5-dualsense'),
(2, 'Xbox Series X|S', 'xbox-series-x'),
(3, 'Phone Dial Cheats', 'phone-dial-cheats'),
(4, 'Player Abilities', 'player-abilities'),
(5, 'Vehicle Spawns', 'vehicle-spawns'),
(6, 'Wanted Level', 'wanted-level'),
(7, 'Trophies & Saves', 'trophies-and-saves'),
(8, 'Launch Day Guide', 'launch-day-guide')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

INSERT IGNORE INTO `cheat_tags` (`cheat_id`, `tag_id`) VALUES
(1, 1), (1, 2), (1, 4), (1, 7),
(2, 1), (2, 2), (2, 5), (2, 3),
(3, 7), (3, 8),
(4, 1), (4, 2), (4, 4),
(5, 1), (5, 2), (5, 6),
(6, 1), (6, 2), (6, 3),
(7, 1), (7, 2), (7, 5),
(8, 1), (8, 2), (8, 4);

INSERT IGNORE INTO `blog_post_tags` (`blog_post_id`, `tag_id`) VALUES
(1, 7), (1, 8),
(2, 1), (2, 2), (2, 3), (2, 7),
(3, 4), (3, 5), (3, 6), (3, 8),
(4, 1), (4, 2), (4, 7),
(5, 4), (5, 8),
(6, 1), (6, 2), (6, 5), (6, 8);

