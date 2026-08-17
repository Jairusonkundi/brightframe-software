<?php
/**
 * Larger inline SVG illustrations for image+text "media row" sections
 * across the site — distinct from the small per-service icons in
 * icons.php. All share one navy "frame" (dot pattern + dashed border,
 * matching index.php's hero-art) so the whole set reads as one family;
 * only the inner graphic changes.
 */
function render_illustration_frame(string $uid, string $inner, string $label): string
{
    $safeLabel = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
    return <<<SVG
<svg viewBox="0 0 480 360" fill="none" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="{$safeLabel}">
  <defs>
    <linearGradient id="{$uid}-line" x1="0" y1="0" x2="480" y2="360" gradientUnits="userSpaceOnUse">
      <stop offset="0" stop-color="#5B5FEF"/>
      <stop offset="1" stop-color="#00D9C0"/>
    </linearGradient>
    <linearGradient id="{$uid}-bar" x1="0" y1="0" x2="0" y2="1">
      <stop offset="0" stop-color="#00D9C0"/>
      <stop offset="1" stop-color="#5B5FEF"/>
    </linearGradient>
    <pattern id="{$uid}-dots" width="26" height="26" patternUnits="userSpaceOnUse">
      <circle cx="2" cy="2" r="1.4" fill="#ffffff" opacity="0.14"/>
    </pattern>
  </defs>
  <rect width="480" height="360" rx="28" fill="#0B0E1A"/>
  <rect width="480" height="360" rx="28" fill="url(#{$uid}-dots)"/>
  <rect x="18" y="18" width="444" height="324" rx="20" stroke="url(#{$uid}-line)" stroke-width="1.5" stroke-dasharray="5 7" opacity="0.5"/>
  {$inner}
</svg>
SVG;
}

function render_category_illustration(string $slug): string
{
    $uid    = 'illo-' . $slug;
    $labels = [
        'web-development-design'       => 'Illustration of a browser window showing a website layout being built',
        'mobile-development'           => 'Illustration of a mobile app interface on a phone screen',
        'software-systems-development' => 'Illustration of connected software system nodes exchanging data',
        'erp-business-systems'         => 'Illustration of a business dashboard with charts and status widgets',
        'branding'                     => 'Illustration of a brand color palette, typography sample, and a pen',
        'digital-marketing'            => 'Illustration of a megaphone broadcasting alongside a growth chart',
        'seo'                          => 'Illustration of a search magnifying glass over a rising growth chart',
        'it-technical-consulting'      => 'Illustration of a network of connected technical infrastructure',
    ];
    $label = $labels[$slug] ?? 'Illustration representing this service category';
    return render_illustration_frame($uid, category_illustration_inner($slug, $uid), $label);
}

function render_whyus_illustration(): string
{
    $uid = 'illo-whyus';
    return render_illustration_frame($uid, <<<SVG
  <g>
    <circle cx="120" cy="180" r="34" fill="rgba(91,95,239,0.18)" stroke="rgba(91,95,239,0.4)"/>
    <circle cx="120" cy="168" r="11" fill="rgba(255,255,255,0.5)"/>
    <path d="M100 200 a20 20 0 0 1 40 0" fill="rgba(255,255,255,0.5)"/>
  </g>
  <path d="M154 172 C 190 160, 210 175, 234 178" stroke="url(#{$uid}-line)" stroke-width="1.5" stroke-dasharray="3 6"/>
  <circle cx="234" cy="178" r="3.5" fill="#00D9C0"/>
  <g>
    <rect x="234" y="128" width="180" height="96" rx="16" fill="rgba(255,255,255,0.05)" stroke="rgba(255,255,255,0.2)"/>
    <circle cx="262" cy="160" r="16" fill="rgba(0,217,192,0.16)" stroke="rgba(0,217,192,0.4)"/>
    <path d="M255 160 L260 165 L270 153" stroke="#00D9C0" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>
    <rect x="290" y="152" width="104" height="9" rx="4.5" fill="rgba(255,255,255,0.35)"/>
    <rect x="290" y="170" width="80" height="7" rx="3.5" fill="rgba(255,255,255,0.16)"/>
    <rect x="252" y="194" width="142" height="7" rx="3.5" fill="rgba(255,255,255,0.14)"/>
  </g>
  <g>
    <rect x="80" y="250" width="112" height="66" rx="12" fill="rgba(255,255,255,0.05)" stroke="rgba(255,255,255,0.18)"/>
    <path d="M136 292a20 20 0 1 0 -0.01 0Z" fill="none" stroke="#5B5FEF" stroke-width="3"/>
    <path d="M128 282 L136 290 L148 274" stroke="#00D9C0" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/>
  </g>
  <circle cx="60" cy="120" r="3.5" fill="#5B5FEF" opacity="0.6"/>
  <circle cx="416" cy="270" r="3" fill="#00D9C0" opacity="0.5"/>
SVG, 'Illustration of a founder avatar connected to an approved message, next to a verified badge');
}

function render_mission_illustration(): string
{
    $uid = 'illo-mission';
    return render_illustration_frame($uid, <<<SVG
  <g>
    <path d="M150 110 L110 110 L110 150" stroke="url(#{$uid}-line)" stroke-width="4" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
    <path d="M330 110 L370 110 L370 150" stroke="url(#{$uid}-line)" stroke-width="4" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
    <path d="M150 250 L110 250 L110 210" stroke="url(#{$uid}-line)" stroke-width="4" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
    <path d="M330 250 L370 250 L370 210" stroke="url(#{$uid}-line)" stroke-width="4" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
    <rect x="140" y="130" width="200" height="100" rx="4" fill="none" stroke="rgba(255,255,255,0.14)" stroke-dasharray="4 6"/>
    <path d="M240 90 L253.8 129.4 L294 142 L253.8 154.6 L240 194 L226.2 154.6 L186 142 L226.2 129.4 Z" fill="url(#{$uid}-bar)"/>
  </g>
  <rect x="176" y="256" width="60" height="8" rx="4" fill="rgba(255,255,255,0.16)"/>
  <rect x="244" y="256" width="60" height="8" rx="4" fill="rgba(255,255,255,0.16)"/>
  <circle cx="82" cy="180" r="3.5" fill="#00D9C0" opacity="0.6"/>
  <circle cx="398" cy="180" r="3" fill="#5B5FEF" opacity="0.6"/>
  <circle cx="240" cy="72" r="3" fill="#ffffff" opacity="0.35"/>
SVG, 'Illustration of a glowing spark centered inside corner frame brackets, on a blueprint grid');
}

function category_illustration_inner(string $slug, string $uid): string
{
    switch ($slug) {
        case 'web-development-design':
            return <<<SVG
  <g>
    <rect x="70" y="66" width="340" height="228" rx="14" fill="rgba(255,255,255,0.05)" stroke="rgba(255,255,255,0.18)"/>
    <rect x="70" y="66" width="340" height="34" rx="14" fill="rgba(255,255,255,0.06)"/>
    <circle cx="90" cy="83" r="5" fill="#FF6B6B" opacity="0.8"/>
    <circle cx="107" cy="83" r="5" fill="#FFD166" opacity="0.8"/>
    <circle cx="124" cy="83" r="5" fill="#00D9C0" opacity="0.9"/>
    <rect x="152" y="76" width="200" height="14" rx="7" fill="rgba(255,255,255,0.1)"/>
    <rect x="94" y="122" width="140" height="60" rx="10" fill="rgba(91,95,239,0.16)" stroke="rgba(91,95,239,0.35)"/>
    <rect x="252" y="122" width="134" height="60" rx="10" fill="rgba(0,217,192,0.12)" stroke="rgba(0,217,192,0.32)"/>
    <rect x="94" y="196" width="292" height="12" rx="6" fill="rgba(255,255,255,0.14)"/>
    <rect x="94" y="216" width="220" height="12" rx="6" fill="rgba(255,255,255,0.14)"/>
    <rect x="94" y="252" width="110" height="30" rx="8" fill="url(#{$uid}-bar)" opacity="0.9"/>
  </g>
  <circle cx="366" cy="248" r="4" fill="#00D9C0" opacity="0.6"/>
  <circle cx="60" cy="240" r="3" fill="#5B5FEF" opacity="0.5"/>
SVG;

        case 'mobile-development':
            return <<<SVG
  <g>
    <rect x="178" y="46" width="124" height="268" rx="20" fill="rgba(255,255,255,0.05)" stroke="rgba(255,255,255,0.2)"/>
    <rect x="192" y="70" width="96" height="176" rx="8" fill="rgba(255,255,255,0.06)"/>
    <rect x="222" y="58" width="36" height="6" rx="3" fill="rgba(255,255,255,0.3)"/>
    <rect x="202" y="84" width="76" height="34" rx="8" fill="rgba(91,95,239,0.2)" stroke="rgba(91,95,239,0.4)"/>
    <rect x="202" y="126" width="76" height="10" rx="5" fill="rgba(255,255,255,0.16)"/>
    <rect x="202" y="144" width="54" height="10" rx="5" fill="rgba(255,255,255,0.16)"/>
    <circle cx="222" cy="182" r="16" fill="rgba(0,217,192,0.16)" stroke="rgba(0,217,192,0.4)"/>
    <path d="M216 182 L221 187 L230 176" stroke="#00D9C0" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
    <rect x="246" y="174" width="32" height="16" rx="5" fill="rgba(255,255,255,0.14)"/>
    <rect x="202" y="210" width="76" height="10" rx="5" fill="rgba(255,255,255,0.12)"/>
    <circle cx="240" cy="264" r="10" fill="rgba(255,255,255,0.16)"/>
  </g>
  <g opacity="0.85">
    <circle cx="120" cy="120" r="26" fill="rgba(0,217,192,0.1)" stroke="rgba(0,217,192,0.3)"/>
    <path d="M112 120h16M120 112v16" stroke="#00D9C0" stroke-width="2" stroke-linecap="round"/>
  </g>
  <path d="M148 130 C 160 145, 168 150, 178 150" stroke="url(#{$uid}-line)" stroke-width="1.5" stroke-dasharray="3 6" opacity="0.6"/>
  <circle cx="352" cy="150" r="4" fill="#5B5FEF" opacity="0.6"/>
  <circle cx="368" cy="230" r="3" fill="#00D9C0" opacity="0.5"/>
SVG;

        case 'software-systems-development':
            return <<<SVG
  <g>
    <rect x="66" y="150" width="88" height="60" rx="10" fill="rgba(91,95,239,0.16)" stroke="rgba(91,95,239,0.4)"/>
    <rect x="80" y="166" width="60" height="6" rx="3" fill="rgba(255,255,255,0.4)"/>
    <rect x="80" y="180" width="40" height="6" rx="3" fill="rgba(255,255,255,0.22)"/>
    <rect x="326" y="90" width="88" height="60" rx="10" fill="rgba(0,217,192,0.14)" stroke="rgba(0,217,192,0.38)"/>
    <rect x="340" y="106" width="60" height="6" rx="3" fill="rgba(255,255,255,0.4)"/>
    <rect x="340" y="120" width="40" height="6" rx="3" fill="rgba(255,255,255,0.22)"/>
    <rect x="326" y="210" width="88" height="60" rx="10" fill="rgba(255,255,255,0.05)" stroke="rgba(255,255,255,0.2)"/>
    <rect x="340" y="226" width="60" height="6" rx="3" fill="rgba(255,255,255,0.4)"/>
    <rect x="340" y="240" width="40" height="6" rx="3" fill="rgba(255,255,255,0.22)"/>
    <rect x="196" y="150" width="88" height="60" rx="10" fill="rgba(255,255,255,0.07)" stroke="rgba(255,255,255,0.24)"/>
    <circle cx="240" cy="172" r="10" fill="rgba(0,217,192,0.2)"/>
    <path d="M235 172 L239 176 L246 167" stroke="#00D9C0" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
    <rect x="220" y="188" width="40" height="6" rx="3" fill="rgba(255,255,255,0.22)"/>
    <path d="M154 174 L196 174" stroke="url(#{$uid}-line)" stroke-width="1.5" stroke-dasharray="3 6"/>
    <path d="M284 174 L326 174" stroke="url(#{$uid}-line)" stroke-width="1.5" stroke-dasharray="3 6"/>
    <path d="M240 150 C 240 130, 300 130, 326 120" stroke="url(#{$uid}-line)" stroke-width="1.5" stroke-dasharray="3 6"/>
    <path d="M240 210 C 240 232, 300 232, 326 240" stroke="url(#{$uid}-line)" stroke-width="1.5" stroke-dasharray="3 6"/>
    <circle cx="154" cy="174" r="3.5" fill="#5B5FEF"/>
    <circle cx="326" cy="120" r="3.5" fill="#00D9C0"/>
    <circle cx="326" cy="240" r="3.5" fill="#5B5FEF"/>
  </g>
SVG;

        case 'erp-business-systems':
            return <<<SVG
  <g>
    <rect x="72" y="72" width="336" height="216" rx="14" fill="rgba(255,255,255,0.04)" stroke="rgba(255,255,255,0.16)"/>
    <rect x="92" y="96" width="140" height="70" rx="10" fill="rgba(91,95,239,0.16)" stroke="rgba(91,95,239,0.36)"/>
    <rect x="106" y="112" width="60" height="8" rx="4" fill="rgba(255,255,255,0.4)"/>
    <rect x="106" y="128" width="80" height="30" rx="4" fill="none"/>
    <rect x="106" y="138" width="14" height="20" rx="2" fill="url(#{$uid}-bar)" opacity="0.9"/>
    <rect x="126" y="128" width="14" height="30" rx="2" fill="url(#{$uid}-bar)"/>
    <rect x="146" y="145" width="14" height="13" rx="2" fill="url(#{$uid}-bar)" opacity="0.7"/>
    <rect x="248" y="96" width="140" height="70" rx="10" fill="rgba(0,217,192,0.1)" stroke="rgba(0,217,192,0.3)"/>
    <circle cx="278" cy="126" r="18" fill="none" stroke="rgba(255,255,255,0.25)" stroke-width="8"/>
    <path d="M278 108a18 18 0 0 1 0 36" stroke="#00D9C0" stroke-width="8" stroke-linecap="round"/>
    <rect x="308" y="118" width="66" height="8" rx="4" fill="rgba(255,255,255,0.3)"/>
    <rect x="308" y="134" width="50" height="8" rx="4" fill="rgba(255,255,255,0.16)"/>
    <rect x="92" y="184" width="140" height="70" rx="10" fill="rgba(255,255,255,0.05)" stroke="rgba(255,255,255,0.18)"/>
    <rect x="106" y="200" width="90" height="8" rx="4" fill="rgba(255,255,255,0.32)"/>
    <rect x="106" y="216" width="112" height="6" rx="3" fill="rgba(255,255,255,0.16)"/>
    <rect x="106" y="230" width="72" height="6" rx="3" fill="rgba(255,255,255,0.16)"/>
    <rect x="248" y="184" width="140" height="70" rx="10" fill="rgba(91,95,239,0.12)" stroke="rgba(91,95,239,0.3)"/>
    <circle cx="278" cy="219" r="14" fill="rgba(0,217,192,0.18)"/>
    <path d="M271 219 L276 224 L286 212" stroke="#00D9C0" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
    <rect x="304" y="210" width="70" height="8" rx="4" fill="rgba(255,255,255,0.3)"/>
    <rect x="304" y="226" width="50" height="8" rx="4" fill="rgba(255,255,255,0.16)"/>
  </g>
SVG;

        case 'branding':
            return <<<SVG
  <g>
    <rect x="70" y="150" width="60" height="60" rx="30" fill="#5B5FEF"/>
    <rect x="126" y="150" width="60" height="60" rx="30" fill="#00D9C0"/>
    <rect x="182" y="150" width="60" height="60" rx="30" fill="#FFD166"/>
    <rect x="238" y="150" width="60" height="60" rx="30" fill="#FF6B6B" opacity="0.92"/>
    <rect x="70" y="150" width="228" height="60" rx="30" fill="none" stroke="rgba(255,255,255,0.25)" stroke-width="1.5"/>
  </g>
  <g>
    <text x="72" y="112" font-family="Space Grotesk, sans-serif" font-size="46" font-weight="700" fill="#fff">Aa</text>
    <rect x="180" y="80" width="130" height="8" rx="4" fill="rgba(255,255,255,0.35)"/>
    <rect x="180" y="98" width="90" height="6" rx="3" fill="rgba(255,255,255,0.16)"/>
  </g>
  <g>
    <rect x="330" y="150" width="80" height="104" rx="12" fill="rgba(255,255,255,0.06)" stroke="rgba(255,255,255,0.2)"/>
    <path d="M400 168 L346 222 L338 238 L354 230 Z" fill="#0B0E1A" stroke="#00D9C0" stroke-width="2" stroke-linejoin="round"/>
    <path d="M390 178 L378 190" stroke="#00D9C0" stroke-width="2" stroke-linecap="round"/>
  </g>
  <circle cx="60" cy="230" r="4" fill="#00D9C0" opacity="0.6"/>
  <circle cx="320" cy="90" r="3" fill="#5B5FEF" opacity="0.6"/>
SVG;

        case 'digital-marketing':
            return <<<SVG
  <g>
    <path d="M110 150 L110 210 L150 210 L200 244 L200 116 L150 150 Z" fill="rgba(91,95,239,0.22)" stroke="rgba(91,95,239,0.45)" stroke-linejoin="round"/>
    <path d="M226 150 a34 34 0 0 1 0 60" stroke="#00D9C0" stroke-width="4" stroke-linecap="round" fill="none" opacity="0.85"/>
    <path d="M226 128 a56 56 0 0 1 0 104" stroke="#00D9C0" stroke-width="3" stroke-linecap="round" fill="none" opacity="0.45"/>
  </g>
  <g>
    <rect x="270" y="180" width="120" height="90" rx="12" fill="rgba(255,255,255,0.05)" stroke="rgba(255,255,255,0.2)"/>
    <path d="M286 248 L312 220 L332 236 L372 194" stroke="url(#{$uid}-bar)" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
    <circle cx="372" cy="194" r="5" fill="#00D9C0"/>
  </g>
  <circle cx="90" cy="100" r="14" fill="rgba(0,217,192,0.14)" stroke="rgba(0,217,192,0.35)"/>
  <path d="M84 100h12M90 94v12" stroke="#00D9C0" stroke-width="2" stroke-linecap="round"/>
  <circle cx="356" cy="120" r="10" fill="rgba(91,95,239,0.18)" stroke="rgba(91,95,239,0.4)"/>
  <circle cx="120" cy="270" r="3.5" fill="#5B5FEF" opacity="0.6"/>
SVG;

        case 'seo':
            return <<<SVG
  <g>
    <rect x="86" y="110" width="308" height="164" rx="14" fill="rgba(255,255,255,0.04)" stroke="rgba(255,255,255,0.16)"/>
    <path d="M112 250 L112 220 M148 250 L148 190 M184 250 L184 232 M220 250 L220 170 M256 250 L256 200 M292 250 L292 150 M328 250 L328 178 M364 250 L364 130"
          stroke="url(#{$uid}-bar)" stroke-width="16" stroke-linecap="round" opacity="0.85"/>
    <path d="M104 226 L150 186 L214 200 L288 140 L360 122" stroke="#00D9C0" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
  </g>
  <g>
    <circle cx="322" cy="96" r="26" fill="rgba(11,14,26,0.6)" stroke="#00D9C0" stroke-width="3"/>
    <line x1="341" y1="115" x2="360" y2="134" stroke="#00D9C0" stroke-width="4" stroke-linecap="round"/>
  </g>
  <circle cx="108" cy="90" r="4" fill="#5B5FEF" opacity="0.6"/>
  <circle cx="70" cy="200" r="3" fill="#00D9C0" opacity="0.5"/>
SVG;

        case 'it-technical-consulting':
            return <<<SVG
  <g>
    <circle cx="240" cy="180" r="46" fill="rgba(91,95,239,0.14)" stroke="rgba(91,95,239,0.4)" stroke-width="1.5"/>
    <circle cx="240" cy="180" r="16" fill="rgba(11,14,26,0.4)" stroke="#00D9C0" stroke-width="3"/>
    <g stroke="#00D9C0" stroke-width="4" stroke-linecap="round">
      <line x1="240" y1="128" x2="240" y2="140"/>
      <line x1="240" y1="220" x2="240" y2="232"/>
      <line x1="188" y1="180" x2="200" y2="180"/>
      <line x1="280" y1="180" x2="292" y2="180"/>
      <line x1="203" y1="143" x2="211" y2="151"/>
      <line x1="269" y1="209" x2="277" y2="217"/>
      <line x1="277" y1="143" x2="269" y2="151"/>
      <line x1="211" y1="209" x2="203" y2="217"/>
    </g>
  </g>
  <g opacity="0.9">
    <circle cx="110" cy="110" r="22" fill="rgba(0,217,192,0.12)" stroke="rgba(0,217,192,0.35)"/>
    <rect x="100" y="102" width="20" height="16" rx="2" fill="none" stroke="#00D9C0" stroke-width="2"/>
    <path d="M104 102 v-6 a6 6 0 0 1 12 0 v6" stroke="#00D9C0" stroke-width="2" fill="none"/>
  </g>
  <g opacity="0.9">
    <rect x="330" y="230" width="56" height="40" rx="8" fill="rgba(255,255,255,0.06)" stroke="rgba(255,255,255,0.22)"/>
    <rect x="340" y="240" width="36" height="6" rx="3" fill="rgba(255,255,255,0.3)"/>
    <rect x="340" y="252" width="24" height="6" rx="3" fill="rgba(255,255,255,0.16)"/>
  </g>
  <path d="M132 120 C 160 140, 175 150, 194 160" stroke="url(#{$uid}-line)" stroke-width="1.5" stroke-dasharray="3 6" opacity="0.6"/>
  <path d="M330 250 C 300 240, 290 220, 278 205" stroke="url(#{$uid}-line)" stroke-width="1.5" stroke-dasharray="3 6" opacity="0.6"/>
  <circle cx="360" cy="110" r="3.5" fill="#5B5FEF" opacity="0.6"/>
SVG;

        default:
            return <<<SVG
  <circle cx="240" cy="180" r="60" fill="rgba(91,95,239,0.14)" stroke="rgba(91,95,239,0.35)"/>
SVG;
    }
}
