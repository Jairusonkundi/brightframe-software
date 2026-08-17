<?php
/**
 * Deeper, category-specific content for services/<slug>.php — the "why
 * this matters" paragraphs, "why choose Brightframe" differentiators, and
 * educational "types of X" copy. Kept separate from icons.php (which
 * holds the shorter blurb/intro copy reused in compact contexts too).
 *
 * Content rules these all follow (see the round that introduced this
 * file for the full brief): no invented client names, portfolios, or
 * case studies; no specific years of company history; no multi-country
 * claims; no unverifiable superlatives ("best", "top", "#1"); honest
 * about being a founder-led/small operation rather than implying a large
 * team. "Types of X" sections are general industry knowledge, not claims
 * about Brightframe specifically.
 */

/**
 * One or two paragraphs expanding on why the category matters to a
 * business — the problem it solves, written in a confident-but-honest
 * tone. Returns an array of paragraph strings.
 */
function category_why_matters(string $slug): array
{
    $copy = [
        'web-development-design' => [
            "Your website or web app is often the first real interaction someone has with your business — and increasingly, it's not just a brochure, it's where transactions, bookings, and support happen. A site that's slow, hard to update, or built on a fragile template can quietly cost you customers long before anyone tells you why they left.",
            "We build with that in mind: a clear technical foundation, code that's documented and organized rather than tangled together to hit a deadline, and a structure that makes sense to whoever maintains it next — including your own team, if that's ever necessary.",
        ],
        'mobile-development' => [
            "A mobile app lives or dies on how it feels in someone's hand — a slow launch, a confusing flow, or a crash on an older phone will get it uninstalled faster than almost any other kind of software gets abandoned. Unlike a website, users generally give a mobile app one real chance to prove itself useful.",
            "We build with that reality in mind: performance and reliability aren't an afterthought, they're part of the initial build, checked on real devices rather than assumed from a simulator.",
        ],
        'software-systems-development' => [
            "Most businesses eventually hit a point where spreadsheets, email, and generic tools stop being enough — processes get held together by manual work and knowledge that lives in one person's head. Custom software exists to close exactly that gap: automating the repetitive parts and giving your team a system built around your actual workflow, not a workflow bent to fit someone else's software.",
            "The real risk in custom software isn't the idea — it's the execution: a poorly structured database or an undocumented codebase can turn into a liability a few years down the line. We build with maintainability as a first-class concern, not an afterthought.",
        ],
        'erp-business-systems' => [
            "A business system that's badly configured doesn't just fail to help — it actively creates extra work, as staff route around it with spreadsheets and workarounds instead of trusting it. The value of an ERP, CRM, or HR system comes almost entirely from how well it's configured to your actual processes, not from the software license itself.",
            "We treat configuration and process-mapping as the real work, not a formality before \"the real build\" — understanding how your team actually operates before deciding how the system should be set up around it.",
        ],
        'branding' => [
            "Inconsistent branding — a different logo treatment on the website than on a business card, colors that drift slightly between materials — quietly undermines trust even when nobody can articulate exactly why. A clear, consistent identity does the opposite: it makes a business look established and considered, which matters especially early on, before you've built up a long track record to lean on.",
            "We approach branding as a system, not a single deliverable: a logo is only the most visible piece of a set of decisions — color, type, tone — that need to hold together consistently across everywhere your business appears.",
        ],
        'digital-marketing' => [
            "It's easy to spend a marketing budget on activity — posts, ads, emails — without a clear sense of what any of it is actually supposed to achieve. The businesses that get real value from digital marketing are the ones that start with a specific goal (leads, sign-ups, sales) and work backwards to the channels and content that will actually move that number.",
            "We plan campaigns the same way: starting from what you're trying to achieve and your realistic budget, then choosing the channels genuinely worth the spend rather than defaulting to \"a bit of everything.\"",
        ],
        'seo' => [
            "Most buying decisions now start with a search, which means a business that's hard to find online is invisible to a large share of people who were already looking for exactly what it offers. Unlike paid ads, SEO's effect compounds and keeps working after the initial effort — but it also takes longer to show results, which makes it easy to under-invest in.",
            "We start with a technical audit rather than guessing — finding what's actually holding a site back (site speed, structure, missing basics) before building a content plan on top of a foundation that can actually support it.",
        ],
        'it-technical-consulting' => [
            "Technical decisions made without the right expertise in the room tend to get expensive to fix later — a system architecture that doesn't scale, an infrastructure choice that becomes a bottleneck, a project scoped by someone without the technical background to catch the risks early. An outside technical review, done before committing budget, is usually far cheaper than the alternative.",
            "We approach consulting as genuinely independent advice — the goal is a clear, honest assessment and a practical recommendation, not a pitch to justify more work than you need.",
        ],
    ];

    return $copy[$slug] ?? [];
}

/**
 * 3-4 short, defensible differentiators for "Why choose Brightframe
 * Software for [category]" — each an ['title' => ..., 'text' => ...]
 * pair, phrased honestly for a founder-led/small operation.
 */
function category_differentiators(string $slug): array
{
    $copy = [
        'web-development-design' => [
            ['title' => 'Direct access to the person building it', 'text' => "You work directly with the developer writing your code, not an account manager relaying messages to someone else. Questions get answered by the person who actually knows the answer."],
            ['title' => 'Full-stack, not just front-end', 'text' => "We handle the interface and the backend and database behind it as one connected system, so the parts that aren't visible on screen are built with the same care as the parts that are."],
            ['title' => 'Built to be maintained, not just launched', 'text' => "Clean, documented code and a sensible file structure, so the site doesn't become unworkable the first time it needs a change six months from now."],
            ['title' => 'The right technology for the job', 'text' => "We choose the stack based on what your project actually needs, not what's trendy — sometimes that's a lightweight static site, sometimes it's a full application framework."],
        ],
        'mobile-development' => [
            ['title' => 'Direct access to the person building it', 'text' => "You're talking to the developer, not a project manager relaying your feedback secondhand."],
            ['title' => 'Full-stack under one roof', 'text' => "The app, its backend, and its APIs are built by the same person as one connected system, so the mobile side and the server side are never out of sync."],
            ['title' => 'Built for real devices', 'text' => "Checked on actual hardware and real network conditions, not just a simulator on a fast office connection."],
            ['title' => 'The right approach for your budget', 'text' => "Native where it genuinely matters, cross-platform where it gets you to market faster without a meaningful compromise."],
        ],
        'software-systems-development' => [
            ['title' => 'Direct access to the person building it', 'text' => "Architecture decisions are made by the person who has to live with them, not handed down and reinterpreted through a chain of people."],
            ['title' => 'Built around your actual process', 'text' => "Software fitted to how your business works, not the other way around."],
            ['title' => 'Documented, structured code', 'text' => "A codebase and database schema built to be understood by someone else later, not just by the person who wrote it under deadline pressure."],
            ['title' => 'Integration-minded', 'text' => "Built to connect with the tools and systems you already rely on, not to become another disconnected island of data."],
        ],
        'erp-business-systems' => [
            ['title' => 'Configuration built around your process', 'text' => "Not a default setup you're expected to adapt to."],
            ['title' => 'Direct involvement throughout', 'text' => "The person doing the implementation is the person you talk to — fewer handoffs, fewer things lost in translation between what you asked for and what gets built."],
            ['title' => 'Full-stack capability when you need it', 'text' => "When a system needs a custom field, a custom report, or a small integration a standard configuration can't cover, we can build that rather than telling you it's not possible."],
            ['title' => 'Built for adoption, not just installation', 'text' => "A system your team will actually use is worth more than a technically correct one nobody logs into."],
        ],
        'branding' => [
            ['title' => 'One person, one consistent vision', 'text' => "Your identity is developed by the same person throughout, so the logo, guidelines, and collateral actually cohere instead of feeling like separate deliverables stitched together."],
            ['title' => 'Built with implementation in mind', 'text' => "Because we also build websites and software, your brand is designed knowing how it needs to actually translate onto a real interface, not just a static mockup."],
            ['title' => 'Practical guidelines, not just a pretty deck', 'text' => "Documentation clear enough that anyone producing materials for you later can stay consistent without guessing."],
            ['title' => 'Honest, direct feedback', 'text' => "You'll hear if a direction isn't working before you commit to it, not after."],
        ],
        'digital-marketing' => [
            ['title' => 'Strategy before spend', 'text' => "A clear plan for what a campaign is meant to achieve before any budget goes into it."],
            ['title' => 'Direct communication', 'text' => "You're talking to the person actually planning and running the campaign, so reporting reflects what's really happening, not a summarized version passed through an account manager."],
            ['title' => 'Connected to what you\'re building', 'text' => "Because we also build websites and software, campaigns can be planned alongside the pages and tracking that actually support them, not bolted on afterward."],
            ['title' => 'Honest reporting', 'text' => "Results reported as they are, including what isn't working yet, so decisions about budget are based on real numbers."],
        ],
        'seo' => [
            ['title' => 'Technical grounding', 'text' => "Because we also build the websites SEO runs on, we understand the technical side (site speed, structure, mobile-friendliness) that a lot of SEO work treats as someone else's problem."],
            ['title' => 'Honest timelines', 'text' => "SEO takes months to show results, and we'll tell you that clearly rather than promising a quick ranking spike that isn't realistic."],
            ['title' => 'Built around real search behavior', 'text' => "Keyword and content decisions grounded in what your customers actually search for, not assumptions about what sounds good."],
            ['title' => 'One point of contact', 'text' => "The person doing the audit and strategy work is the same person you talk to about it, so nothing gets lost in a handoff."],
        ],
        'it-technical-consulting' => [
            ['title' => 'Hands-on technical background', 'text' => "Advice grounded in actually building software and systems, not a purely theoretical framework."],
            ['title' => 'Direct, independent assessment', 'text' => "The person giving the advice isn't managing a large team that needs to be kept busy, so recommendations aren't shaped by an incentive to sell more work than you need."],
            ['title' => 'Practical, not just theoretical', 'text' => "Recommendations come with a realistic sense of what's actually achievable given your budget and timeline, not just the ideal-world answer."],
            ['title' => 'Plain language', 'text' => "Technical findings explained in terms that make sense to whoever needs to make the decision, not just to another engineer."],
        ],
    ];

    return $copy[$slug] ?? [];
}

/**
 * Educational "types of X" comparison content — general industry
 * knowledge, not claims about Brightframe. Returns
 * ['heading' => ..., 'intro' => ..., 'items' => [['title'=>, 'text'=>], ...]].
 */
function category_types(string $slug): array
{
    $copy = [
        'web-development-design' => [
            'heading' => 'Types of web presence',
            'intro'   => "Not every business needs the same kind of site — the right starting point depends on what it actually needs to do.",
            'items'   => [
                ['title' => 'Brochure / marketing websites', 'text' => "Informational sites presenting a business, its services, and how to get in touch. Content-focused, and usually the right starting point for a business that doesn't yet need logins or transactions."],
                ['title' => 'Web applications', 'text' => "Interactive systems with accounts, dashboards, and logic behind the scenes — booking systems, portals, internal tools. This is where a \"web app\" differs from a \"website\": it does something, not just displays something."],
                ['title' => 'E-commerce sites', 'text' => "Online stores with product catalogs, carts, and payment processing, with their own considerations around security and checkout flow."],
            ],
        ],
        'mobile-development' => [
            'heading' => 'Native vs. cross-platform apps',
            'intro'   => "The right approach depends on your budget, timeline, and how much you need to lean on platform-specific features.",
            'items'   => [
                ['title' => 'Native apps', 'text' => "Built specifically for one platform using its own official tools (Swift/SwiftUI for iOS, Kotlin for Android). Typically the best performance and earliest access to new platform features, at the cost of maintaining two separate codebases if you need both platforms."],
                ['title' => 'Cross-platform apps', 'text' => "Built once — commonly with frameworks like Flutter or React Native — and shipped to both Android and iOS from a single codebase. Performance is very close to native for most apps today, and development is faster and less expensive to maintain across two platforms."],
                ['title' => 'Progressive web apps as an option', 'text' => "For some products, a well-built PWA (see Web Development & Design) can cover a surprising amount of what a \"real\" app needs, without an app-store release at all — worth ruling out before committing to a full native or cross-platform build."],
            ],
        ],
        'software-systems-development' => [
            'heading' => 'Approaches to software architecture',
            'intro'   => "How software is structured affects how easily it can grow and connect to other systems later — worth deciding deliberately, not by default.",
            'items'   => [
                ['title' => 'Monolithic applications', 'text' => "One unified codebase handling everything — interface, logic, data. Simpler to build and deploy for a smaller system, and often the right starting point."],
                ['title' => 'API-first / service-based systems', 'text' => "Logic and data exposed through APIs that other systems — a website, mobile app, or third-party service — can consume. The right approach once multiple front-ends need to share the same underlying data and rules."],
                ['title' => 'Off-the-shelf vs. custom', 'text' => "A genuine decision point worth making deliberately: an existing tool can be faster and cheaper when it's a close fit; custom development earns its cost when your process is specific enough that forcing it into someone else's software creates more friction than it saves."],
            ],
        ],
        'erp-business-systems' => [
            'heading' => 'Types of business systems',
            'intro'   => "\"ERP\" and \"CRM\" get used loosely — worth being clear on what each actually covers before choosing one.",
            'items'   => [
                ['title' => 'ERP (Enterprise Resource Planning)', 'text' => "A unified system covering core operations — finance, inventory, procurement, sometimes HR — in one connected platform, so departments share the same underlying data instead of keeping separate records that drift apart."],
                ['title' => 'CRM (Customer Relationship Management)', 'text' => "Focused specifically on sales and customer interactions: tracking leads, deals, and communication history in one place instead of scattered across email and spreadsheets."],
                ['title' => 'On-premise vs. cloud-based', 'text' => "On-premise systems run on your own infrastructure — more control, more responsibility for upkeep. Cloud-based systems are hosted and maintained by the provider — less infrastructure to manage, an ongoing subscription cost. The right choice depends on your existing setup, budget model, and compliance needs."],
            ],
        ],
        'branding' => [
            'heading' => 'Parts of a brand identity',
            'intro'   => "\"Branding\" covers more than a logo — here's how the pieces usually break down.",
            'items'   => [
                ['title' => 'Visual identity', 'text' => "The tangible elements: logo, color palette, typography, imagery style — what most people mean when they say \"branding.\""],
                ['title' => 'Brand strategy / positioning', 'text' => "The thinking underneath the visuals: who you're for, what you stand for, and how you're different from alternatives. This should usually come before the visual identity, not after."],
                ['title' => 'Brand guidelines', 'text' => "The reference document that keeps everything consistent once the identity exists — rules for logo usage, color codes, type pairing — so the brand doesn't quietly drift over time as new materials get made by different people."],
            ],
        ],
        'digital-marketing' => [
            'heading' => 'Types of digital marketing',
            'intro'   => "Most effective marketing plans use a mix of these, weighted differently depending on budget and timeline.",
            'items'   => [
                ['title' => 'Organic', 'text' => "Content, social media, and SEO efforts that build an audience over time without paying for placement. Slower to show results, but compounds and doesn't stop the moment you stop spending."],
                ['title' => 'Paid', 'text' => "PPC and paid social campaigns that buy visibility directly. Faster results, but the visibility stops as soon as the budget does — works best alongside an organic foundation rather than instead of one."],
                ['title' => 'Owned channels', 'text' => "Email lists, your own website and content — audiences and assets you actually control, rather than renting attention on a platform whose algorithm and rules can change without notice."],
            ],
        ],
        'seo' => [
            'heading' => 'Types of SEO',
            'intro'   => "SEO work generally falls into three areas, and a healthy strategy usually needs some of each.",
            'items'   => [
                ['title' => 'On-page SEO', 'text' => "Optimizing the content and structure of individual pages themselves: titles, headings, content quality, internal linking — the parts directly within your control."],
                ['title' => 'Technical SEO', 'text' => "The underlying site health that search engines evaluate before content even matters: site speed, mobile-friendliness, crawlability, structured data."],
                ['title' => 'Off-page / local SEO', 'text' => "Factors outside your own site: backlinks, citations, and for local businesses, accurate listings and reviews on platforms like Google Business Profile — signals of trust and relevance from elsewhere on the web."],
            ],
        ],
        'it-technical-consulting' => [
            'heading' => 'Types of consulting engagements',
            'intro'   => "The right format depends on whether you have one specific question or an ongoing need for technical input.",
            'items'   => [
                ['title' => 'One-off audit / assessment', 'text' => "A focused review of a specific system, decision, or problem, with a clear set of findings and recommendations — the right fit when you need a second opinion on something specific."],
                ['title' => 'Ongoing advisory', 'text' => "A continuing relationship for technical decisions as they come up, rather than a single engagement — useful for a business making a steady stream of smaller technical decisions."],
                ['title' => 'Pre-project scoping', 'text' => "Technical input before a project starts, to catch architecture or infrastructure risks while they're still cheap to change, rather than after development is already underway."],
            ],
        ],
    ];

    return $copy[$slug] ?? ['heading' => '', 'intro' => '', 'items' => []];
}
