<?php
// Eurobillr — index.php (EN)
// Drop-in replacement. No PHP logic changed — all server-side includes/auth
// calls that existed before should be added back around this file as needed.

// ═══════════════════════════════════════════════════════════════
//  ROBOTS.TXT — served inline from index.php
//  Access: https://eurobillr.com/robots.txt
// ═══════════════════════════════════════════════════════════════
if (isset($_SERVER['REQUEST_URI']) && rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/') === '/robots.txt') {
    header('Content-Type: text/plain; charset=utf-8');
    header('Cache-Control: public, max-age=86400');
    echo <<<ROBOTS
# robots.txt — eurobillr.com
# Last updated: 2025-01

# ── Search engines ────────────────────────────────────────────
User-agent: *
Allow: /
Allow: /nl/
Allow: /fr/
Allow: /faq.php
Allow: /nl/faq
Allow: /fr/faq
Allow: /privacy-policy.php
Allow: /terms-of-service.php
Allow: /cookies.php
Allow: /sitemap.xml
Allow: /llms.txt

# Block dashboard, auth, and internal tooling from indexing
Disallow: /auth/
Disallow: /dashboard/
Disallow: /admin/
Disallow: /includes/
Disallow: /temp/
Disallow: /vendor/
Disallow: /api/
Disallow: /?export=
Disallow: /?sync_recommand_status=

# ── AI crawlers — explicitly welcome on public pages ──────────
# Anthropic
User-agent: ClaudeBot
Allow: /
Disallow: /auth/
Disallow: /dashboard/

# OpenAI
User-agent: GPTBot
Allow: /
Disallow: /auth/
Disallow: /dashboard/

# Google AI
User-agent: Google-Extended
Allow: /
Disallow: /auth/
Disallow: /dashboard/

# Perplexity
User-agent: PerplexityBot
Allow: /
Disallow: /auth/
Disallow: /dashboard/

# Meta
User-agent: FacebookBot
Allow: /
Disallow: /auth/
Disallow: /dashboard/

# Common AI training crawlers
User-agent: CCBot
Allow: /
Disallow: /auth/
Disallow: /dashboard/

User-agent: Diffbot
Allow: /
Disallow: /auth/
Disallow: /dashboard/

# ── Sitemaps ──────────────────────────────────────────────────
Sitemap: https://eurobillr.com/sitemap.xml

# ── LLMs.txt (AI agent briefing document) ────────────────────
# https://eurobillr.com/llms.txt
ROBOTS;
    exit;
}

// ═══════════════════════════════════════════════════════════════
//  LLMS.TXT — AI agent briefing document
//  Standard: https://llmstxt.org
//  Access: https://eurobillr.com/llms.txt
// ═══════════════════════════════════════════════════════════════
if (isset($_SERVER['REQUEST_URI']) && rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/') === '/llms.txt') {
    header('Content-Type: text/plain; charset=utf-8');
    header('Cache-Control: public, max-age=3600');
    echo <<<LLMS
# Eurobillr

> Eurobillr is a PEPPOL-certified invoicing and accounting platform for freelancers and small businesses in Belgium and the EU. It combines PEPPOL e-invoicing, automatic expense tracking, payment collection, Belgian tax reporting, and income reports in one place. Free to start, no credit card required.

## Product summary

Eurobillr is a SaaS platform headquartered in Ghent, Belgium (Muelesteedsesteenweg 216, 9000 Gent). It serves freelancers, sole traders, consultants, small businesses, SMEs, and agencies across Belgium, the Netherlands, France, and 38+ PEPPOL-connected countries.

### Core features

- **PEPPOL e-invoicing (B2B and B2G)**: Register once on the EU-mandated PEPPOL network and exchange legally compliant invoices with any connected business or government entity worldwide. Incoming PEPPOL invoices are automatically captured as expenses. Mandatory for Belgian B2B since January 1, 2026.
- **Automatic expense tracking**: Expenses are captured three ways — automatically from incoming PEPPOL invoices (zero manual entry), by scanning receipts with OCR (vendor, amount, date extracted instantly), or by manual entry.
- **Payment collection**: Integrates with Stripe (cards, SEPA, Apple Pay), Mollie (iDEAL, Bancontact, Klarna), PayPal, and IBAN bank transfer with auto-generated structured payment references. All payments go directly to the user's account — Eurobillr never holds funds.
- **Automatic payment reminders**: Eurobillr sends pre-due and overdue email reminders automatically. Marks invoices paid when payment clears.
- **QR code invoices**: Every invoice includes a scannable QR code linking directly to the payment page.
- **Belgian accounting and tax reports**: Quarterly VAT return generation (pre-filled from real invoice and expense data), annual income tax report, Belgian client listing (klantenlisting), EU intra-community sales listing (IC listing), real-time VAT and taxable profit overview. Eurobillr reminds users of filing deadlines but does NOT submit or pay taxes to FOD on behalf of users.
- **Income reports**: Downloadable income statements, expense breakdowns, P&L summaries in PDF and Excel.

### What Eurobillr does NOT do

- Does not submit taxes or VAT returns to FOD / SPF payment on behalf of users
- Does not process or hold payments (all payment gateway funds go directly to the user)
- Does not offer human accountant services

## Target users

- Freelancers (designers, developers, consultants, writers, creatives)
- Sole traders and self-employed professionals (zelfstandigen / indépendants) in Belgium
- Small businesses and SMEs in Belgium, Netherlands, and France
- Agencies needing B2G PEPPOL invoicing

## Key facts

- PEPPOL certified, Belgium and EU
- Multilingual: English (EN), Dutch (NL), French (FR)
- Free plan available, no credit card required
- GDPR compliant
- Address: Muelesteedsesteenweg 216, 9000 Gent, Belgium
- Contact: info@eurobillr.com
- Website: https://eurobillr.com

## Belgian regulatory context

Belgium mandated PEPPOL B2B e-invoicing for all VAT-registered companies from January 1, 2026 (Royal Decree implementing Directive 2014/55/EU). Eurobillr is a certified PEPPOL Access Point. All Belgian public entities have been connected to PEPPOL for B2G invoicing since March 2024.

## Supported payment providers

Stripe, Mollie, PayPal, IBAN bank transfer (SEPA)

## Supported languages

English, Nederlands (Dutch), Français (French)

## PEPPOL network

38+ countries supported including all EU member states, Norway, Iceland, United Kingdom, Singapore, Australia.

## Pages

- Homepage (EN): https://eurobillr.com/
- Homepage (NL): https://eurobillr.com/nl/
- Homepage (FR): https://eurobillr.com/fr/
- FAQ (EN): https://eurobillr.com/faq.php
- FAQ (NL): https://eurobillr.com/nl/faq
- FAQ (FR): https://eurobillr.com/fr/faq
- Privacy Policy: https://eurobillr.com/privacy-policy.php
- Terms of Service: https://eurobillr.com/terms-of-service.php
- Register: https://eurobillr.com/auth/register.php
- Login: https://eurobillr.com/auth/login.php
LLMS;
    exit;
}

// ═══════════════════════════════════════════════════════════════
//  SITEMAP.XML — served inline from index.php
//  Access: https://eurobillr.com/sitemap.xml
// ═══════════════════════════════════════════════════════════════
if (isset($_SERVER['REQUEST_URI']) && rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/') === '/sitemap.xml') {
    header('Content-Type: application/xml; charset=utf-8');
    header('Cache-Control: public, max-age=86400');
    $today = date('Y-m-d');
    echo <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:xhtml="http://www.w3.org/1999/xhtml">

  <!-- Homepage EN -->
  <url>
    <loc>https://eurobillr.com/</loc>
    <lastmod>{$today}</lastmod>
    <changefreq>monthly</changefreq>
    <priority>1.0</priority>
    <xhtml:link rel="alternate" hreflang="en"        href="https://eurobillr.com/"/>
    <xhtml:link rel="alternate" hreflang="nl"        href="https://eurobillr.com/nl/"/>
    <xhtml:link rel="alternate" hreflang="fr"        href="https://eurobillr.com/fr/"/>
    <xhtml:link rel="alternate" hreflang="x-default" href="https://eurobillr.com/"/>
  </url>

  <!-- Homepage NL -->
  <url>
    <loc>https://eurobillr.com/nl/</loc>
    <lastmod>{$today}</lastmod>
    <changefreq>monthly</changefreq>
    <priority>0.9</priority>
    <xhtml:link rel="alternate" hreflang="en"        href="https://eurobillr.com/"/>
    <xhtml:link rel="alternate" hreflang="nl"        href="https://eurobillr.com/nl/"/>
    <xhtml:link rel="alternate" hreflang="fr"        href="https://eurobillr.com/fr/"/>
    <xhtml:link rel="alternate" hreflang="x-default" href="https://eurobillr.com/"/>
  </url>

  <!-- Homepage FR -->
  <url>
    <loc>https://eurobillr.com/fr/</loc>
    <lastmod>{$today}</lastmod>
    <changefreq>monthly</changefreq>
    <priority>0.9</priority>
    <xhtml:link rel="alternate" hreflang="en"        href="https://eurobillr.com/"/>
    <xhtml:link rel="alternate" hreflang="nl"        href="https://eurobillr.com/nl/"/>
    <xhtml:link rel="alternate" hreflang="fr"        href="https://eurobillr.com/fr/"/>
    <xhtml:link rel="alternate" hreflang="x-default" href="https://eurobillr.com/"/>
  </url>

  <!-- FAQ EN -->
  <url>
    <loc>https://eurobillr.com/faq</loc>
    <lastmod>{$today}</lastmod>
    <changefreq>monthly</changefreq>
    <priority>0.8</priority>
    <xhtml:link rel="alternate" hreflang="en" href="https://eurobillr.com/faq.php"/>
    <xhtml:link rel="alternate" hreflang="nl" href="https://eurobillr.com/nl/faq"/>
    <xhtml:link rel="alternate" hreflang="fr" href="https://eurobillr.com/fr/faq"/>
  </url>

  <!-- FAQ NL -->
  <url>
    <loc>https://eurobillr.com/nl/faq</loc>
    <lastmod>{$today}</lastmod>
    <changefreq>monthly</changefreq>
    <priority>0.75</priority>
    <xhtml:link rel="alternate" hreflang="en" href="https://eurobillr.com/faq.php"/>
    <xhtml:link rel="alternate" hreflang="nl" href="https://eurobillr.com/nl/faq"/>
    <xhtml:link rel="alternate" hreflang="fr" href="https://eurobillr.com/fr/faq"/>
  </url>

  <!-- FAQ FR -->
  <url>
    <loc>https://eurobillr.com/fr/faq</loc>
    <lastmod>{$today}</lastmod>
    <changefreq>monthly</changefreq>
    <priority>0.75</priority>
    <xhtml:link rel="alternate" hreflang="en" href="https://eurobillr.com/faq.php"/>
    <xhtml:link rel="alternate" hreflang="nl" href="https://eurobillr.com/nl/faq"/>
    <xhtml:link rel="alternate" hreflang="fr" href="https://eurobillr.com/fr/faq"/>
  </url>

  <!-- Legal pages -->
  <url>
    <loc>https://eurobillr.com/privacy-policy.php</loc>
    <lastmod>{$today}</lastmod>
    <changefreq>yearly</changefreq>
    <priority>0.3</priority>
  </url>
  <url>
    <loc>https://eurobillr.com/terms-of-service.php</loc>
    <lastmod>{$today}</lastmod>
    <changefreq>yearly</changefreq>
    <priority>0.3</priority>
  </url>
  <url>
    <loc>https://eurobillr.com/cookies.php</loc>
    <lastmod>{$today}</lastmod>
    <changefreq>yearly</changefreq>
    <priority>0.2</priority>
  </url>

</urlset>
XML;
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <!-- ── Primary Meta ── -->
  <title>Eurobillr — Invoicing & Accounting Platform for Freelancers & Small Business</title>
  <meta name="description" content="Send invoices, track & management expenses, file VAT returns, and stay PEPPOL-compliant — all in one platform built for freelancers and small businesses in Belgium and the EU. Free to start." />
  <meta name="robots" content="index, follow" />
  <link rel="canonical" href="https://eurobillr.com/" />

  <!-- ── Sitemap & AI discovery ── -->
  <link rel="sitemap" type="application/xml" title="Sitemap" href="https://eurobillr.com/sitemap.xml" />
  <!-- llms.txt: AI agent briefing — https://eurobillr.com/llms.txt -->

  <!-- ── hreflang ── -->
  <link rel="alternate" hreflang="en"        href="https://eurobillr.com/" />
  <link rel="alternate" hreflang="nl"        href="https://eurobillr.com/nl/" />
  <link rel="alternate" hreflang="fr"        href="https://eurobillr.com/fr/" />
  <link rel="alternate" hreflang="x-default" href="https://eurobillr.com/" />

  <!-- ── Open Graph ── -->
  <meta property="og:type"              content="website" />
  <meta property="og:url"               content="https://eurobillr.com/" />
  <meta property="og:site_name"         content="Eurobillr" />
  <meta property="og:title"             content="Eurobillr — Invoicing & Accounting for Freelancers & Small Business" />
  <meta property="og:description"       content="PEPPOL-certified invoicing platform. Send invoices, auto-track expenses, collect payments via Stripe/Mollie/PayPal, and file tax reports — all in one place. Free to start." />
  <meta property="og:image"             content="https://eurobillr.com/images/eurobillr.com.jpg" />
  <meta property="og:image:width"       content="1200" />
  <meta property="og:image:height"      content="630" />
  <meta property="og:image:alt"         content="Eurobillr invoicing dashboard" />
  <meta property="og:locale"            content="en_BE" />
  <meta property="og:locale:alternate"  content="nl_BE" />
  <meta property="og:locale:alternate"  content="fr_BE" />

  <!-- ── Twitter Card ── -->
  <meta name="twitter:card"        content="summary_large_image" />
  <meta name="twitter:title"       content="Eurobillr — Invoicing & Accounting for Freelancers & Small Business" />
  <meta name="twitter:description" content="PEPPOL-certified invoicing platform for freelancers and small businesses in Belgium and the EU. Free to start." />
  <meta name="twitter:image"       content="https://eurobillr.com/images/eurobillr.com.jpg" />

  <!-- ── Schema.org: SoftwareApplication ── -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "SoftwareApplication",
    "name": "Eurobillr",
    "url": "https://eurobillr.com",
    "logo": "https://eurobillr.com/images/eurobillr.com.jpg",
    "description": "Eurobillr is a PEPPOL-certified invoicing and accounting platform for freelancers and small businesses in Belgium and the EU. Features: PEPPOL e-invoicing (B2B & B2G), automatic expense tracking via OCR and PEPPOL, payment collection via Stripe, Mollie, PayPal and bank transfer, automatic payment reminders, QR code invoices, income reports, and tax submission.",
    "applicationCategory": "BusinessApplication",
    "operatingSystem": "Web, iOS, Android",
    "inLanguage": "en",
    "availableLanguage": ["en", "nl", "fr"],
    "offers": {
      "@type": "Offer",
      "price": "0",
      "priceCurrency": "EUR",
      "description": "Free plan available. Paid plans with full features."
    },
    "featureList": [
      "PEPPOL e-invoicing (B2B and B2G)",
      "Automatic expense tracking",
      "Receipt scanning with OCR",
      "Payment collection via Stripe, Mollie, PayPal, bank transfer",
      "Automatic payment reminders",
      "QR code invoices",
      "Quarterly VAT return generation and submission",
      "Annual income tax report preparation",
      "Belgian client listing (klantenlisting/listing clients)",
      "EU intra-community sales listing",
      "Real-time VAT and tax provision overview",
      "Accountant collaboration and data export",
      "Income reports (PDF and Excel)",
      "Multilingual: English, Dutch, French"
    ],
    "audience": {
      "@type": "Audience",
      "audienceType": "Freelancers, Small Businesses, SMEs, Consultants"
    },
    "publisher": {
      "@type": "Organization",
      "name": "Eurobillr",
      "url": "https://eurobillr.com",
      "logo": "https://eurobillr.com/images/eurobillr.com.jpg",
      "email": "info@eurobillr.com",
      "address": {
        "@type": "PostalAddress",
        "streetAddress": "Muelesteedsesteenweg 216",
        "addressLocality": "Gent",
        "postalCode": "9000",
        "addressCountry": "BE"
      },
      "areaServed": { "@type": "Place", "name": "European Union" }
    }
  }
  </script>

  <!-- ── Schema.org: Organization ── -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "Organization",
    "name": "Eurobillr",
    "url": "https://eurobillr.com",
    "logo": "https://eurobillr.com/images/eurobillr.com.jpg",
    "email": "info@eurobillr.com",
    "description": "PEPPOL-certified invoicing and accounting platform for freelancers and small businesses in Belgium and the EU.",
    "address": {
      "@type": "PostalAddress",
      "streetAddress": "Muelesteedsesteenweg 216",
      "addressLocality": "Gent",
      "postalCode": "9000",
      "addressCountry": "BE"
    }
  }
  </script>

  <!-- ── Schema.org: FAQPage ── -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "FAQPage",
    "mainEntity": [
      {
        "@type": "Question",
        "name": "What is PEPPOL and why does it matter?",
        "acceptedAnswer": {
          "@type": "Answer",
          "text": "PEPPOL is the EU-mandated network for exchanging electronic invoices between businesses and governments. Since January 1, 2026, B2B e-invoicing via PEPPOL is mandatory for all VAT-registered companies in Belgium. Eurobillr is PEPPOL-certified — registration takes under 2 minutes and compliance is handled automatically."
        }
      },
      {
        "@type": "Question",
        "name": "How does automatic expense tracking work?",
        "acceptedAnswer": {
          "@type": "Answer",
          "text": "Eurobillr tracks & management expenses three ways: automatically from incoming PEPPOL invoices (zero manual entry), by scanning receipts with OCR that extracts vendor, amount and date instantly, or by manual entry. All three feed into one unified expense overview."
        }
      },
      {
        "@type": "Question",
        "name": "Which payment providers does Eurobillr support?",
        "acceptedAnswer": {
          "@type": "Answer",
          "text": "Eurobillr integrates with Stripe (cards, SEPA, Apple Pay), Mollie (iDEAL, Bancontact, Klarna), PayPal, and classic bank transfer via IBAN with auto-generated structured references. All payments go directly to your account — Eurobillr never holds your funds."
        }
      },
      {
        "@type": "Question",
        "name": "Can I submit VAT and tax reports directly from Eurobillr?",
        "acceptedAnswer": {
          "@type": "Answer",
          "text": "Yes. Eurobillr pre-fills VAT and income tax declarations from your real invoices and expenses. You review, confirm, and submit. You can also download full Income reports in PDF or Excel for your accountant."
        }
      },
      {
        "@type": "Question",
        "name": "Is there a free plan?",
        "acceptedAnswer": {
          "@type": "Answer",
          "text": "Yes. Eurobillr offers a free plan — no credit card required. PEPPOL registration is included in all plans."
        }
      },
      {
        "@type": "Question",
        "name": "How do automatic payment reminders work?",
        "acceptedAnswer": {
          "@type": "Answer",
          "text": "Set a due date when you send an invoice and Eurobillr handles the rest: a polite pre-due reminder 3–7 days before, a professional follow-up if unpaid after the due date, and automatic paid status when payment clears — with instant notification to you."
        }
      }
    ]
  }
  </script>

  <!-- ── Fonts ── -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,300&display=swap" rel="stylesheet" />

  <style>
    /* ═══════════════════════════════
       RESET & TOKENS
    ═══════════════════════════════ */
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --navy:       #0c1527;
      --navy-mid:   #152040;
      --navy-soft:  #1e2d4f;
      --blue:       #2563eb;
      --blue-light: #3b82f6;
      --blue-glow:  rgba(37,99,235,.18);
      --sky:        #dbeafe;
      --mint:       #d1fae5;
      --white:      #ffffff;
      --off-white:  #f8faff;
      --text:       #0f172a;
      --muted:      #64748b;
      --border:     #e2e8f0;
      --radius-sm:  8px;
      --radius:     14px;
      --radius-lg:  22px;
      --shadow-sm:  0 1px 3px rgba(0,0,0,.07), 0 1px 2px rgba(0,0,0,.05);
      --shadow:     0 4px 24px rgba(0,0,0,.09), 0 1px 4px rgba(0,0,0,.06);
      --shadow-lg:  0 20px 60px rgba(0,0,0,.15), 0 4px 16px rgba(0,0,0,.08);
    }

    html { scroll-behavior: smooth; }

    body {
      font-family: 'DM Sans', sans-serif;
      background: var(--white);
      color: var(--text);
      line-height: 1.65;
      font-size: 16px;
      /* Do NOT set overflow-x: hidden on body — it breaks iOS scroll */
    }

    /* Contain horizontal overflow at a wrapper level instead */
    .hero,
    .section,
    .stats-bar,
    .cta-final,
    .footer {
      overflow-x: clip; /* clips without creating a scroll container */
    }

    /* Pull-to-refresh suppression — body-level is WebView-safe.
       html-level overscroll-behavior causes Android WebView ANR. */
    body {
      overscroll-behavior-y: none;
    }

    /* ── Utility ── */
    .container { max-width: 1120px; margin: 0 auto; padding: 0 28px; }
    .container--narrow { max-width: 800px; margin: 0 auto; padding: 0 28px; }

    a { color: inherit; text-decoration: none; }

    /* ── Scroll-reveal ── */
    .reveal {
      opacity: 0;
      transform: translateY(28px);
      transition: opacity .65s ease, transform .65s ease;
    }
    .reveal.visible {
      opacity: 1;
      transform: none;
    }
    .reveal-delay-1 { transition-delay: .1s; }
    .reveal-delay-2 { transition-delay: .2s; }
    .reveal-delay-3 { transition-delay: .3s; }
    .reveal-delay-4 { transition-delay: .4s; }

    /* ═══════════════════════════════
       NAV
    ═══════════════════════════════ */
    .nav {
      position: sticky;
      top: 0;
      z-index: 200;
      background: rgba(255,255,255,.96);
      /* backdrop-filter removed — causes iOS Safari scroll-lock on overscroll */
      border-bottom: 1px solid var(--border);
      height: 64px;
      display: flex;
      align-items: center;
      will-change: auto;
    }
    .nav__inner {
      width: 100%;
      max-width: 1120px;
      margin: 0 auto;
      padding: 0 28px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 16px;
    }
    .nav__logo {
      display: flex;
      align-items: center;
      gap: 10px;
      font-weight: 600;
      font-size: 17px;
      color: var(--text);
      flex-shrink: 0;
    }
    .nav__logo img {
      height: 30px;
      border-radius: 7px;
    }
    .nav__links {
      display: flex;
      align-items: center;
      gap: 4px;
    }
    .nav__link {
      font-size: 14px;
      font-weight: 500;
      padding: 7px 13px;
      border-radius: var(--radius-sm);
      color: var(--muted);
      transition: background .15s, color .15s;
    }
    .nav__link:hover { background: var(--off-white); color: var(--text); }
    .nav__lang {
      font-size: 13px;
      font-weight: 600;
      color: var(--muted);
      padding: 5px 10px;
      border-radius: 6px;
      letter-spacing: .02em;
    }
    .nav__lang:hover { color: var(--blue); }
    .nav__cta {
      display: flex;
      align-items: center;
      gap: 8px;
      margin-left: 8px;
    }
    .btn {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      font-family: 'DM Sans', sans-serif;
      font-size: 14px;
      font-weight: 600;
      padding: 9px 20px;
      border-radius: var(--radius-sm);
      border: none;
      cursor: pointer;
      transition: all .18s;
      text-decoration: none;
      white-space: nowrap;
    }
    .btn--ghost {
      background: transparent;
      color: var(--text);
      border: 1.5px solid var(--border);
    }
    .btn--ghost:hover { border-color: var(--blue-light); color: var(--blue); }
    .btn--primary {
      background: var(--blue);
      color: var(--white);
      box-shadow: 0 1px 3px rgba(37,99,235,.3);
    }
    .btn--primary:hover {
      background: #1d4ed8;
      transform: translateY(-1px);
      box-shadow: 0 4px 14px rgba(37,99,235,.35);
    }
    .btn--lg {
      font-size: 16px;
      padding: 13px 28px;
      border-radius: var(--radius-sm);
    }
    .btn--white {
      background: var(--white);
      color: var(--blue);
      box-shadow: var(--shadow);
    }
    .btn--white:hover { transform: translateY(-1px); box-shadow: var(--shadow-lg); }
    .btn--outline-white {
      background: transparent;
      color: rgba(255,255,255,.9);
      border: 1.5px solid rgba(255,255,255,.3);
    }
    .btn--outline-white:hover { background: rgba(255,255,255,.1); color: white; }

    /* ═══════════════════════════════
       HERO
    ═══════════════════════════════ */
    .hero {
      background: var(--navy);
      position: relative;
      /* overflow:hidden removed — use clip on pseudo-elements instead */
      padding: 100px 0 0;
    }

    /* dot-grid texture */
    .hero::before {
      content: '';
      position: absolute;
      inset: 0;
      background-image: radial-gradient(circle, rgba(255,255,255,.06) 1px, transparent 1px);
      background-size: 28px 28px;
      pointer-events: none;
    }

    /* diagonal gradient orbs */
    .hero::after {
      content: '';
      position: absolute;
      inset: 0;
      background:
        radial-gradient(ellipse 600px 400px at 10% 20%, rgba(37,99,235,.22) 0%, transparent 70%),
        radial-gradient(ellipse 400px 300px at 85% 75%, rgba(16,185,129,.12) 0%, transparent 70%);
      pointer-events: none;
    }

    .hero__content {
      position: relative;
      z-index: 2;
      text-align: center;
      padding: 0 28px;
      max-width: 860px;
      margin: 0 auto;
    }

    .hero__badge {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: rgba(37,99,235,.18);
      border: 1px solid rgba(37,99,235,.35);
      color: #93c5fd;
      font-size: 13px;
      font-weight: 600;
      padding: 6px 16px;
      border-radius: 100px;
      margin-bottom: 28px;
      letter-spacing: .03em;
      animation: fadeDown .7s ease both;
    }
    .hero__badge-dot {
      width: 6px;
      height: 6px;
      border-radius: 50%;
      background: #4ade80;
      box-shadow: 0 0 6px #4ade80;
      animation: pulse 2s infinite;
    }
    @keyframes pulse {
      0%, 100% { opacity: 1; }
      50% { opacity: .4; }
    }
    @keyframes fadeDown {
      from { opacity: 0; transform: translateY(-12px); }
      to   { opacity: 1; transform: none; }
    }

    .hero__h1 {
      font-family: 'Instrument Serif', serif;
      font-size: clamp(42px, 6.5vw, 80px);
      line-height: 1.05;
      color: var(--white);
      margin-bottom: 24px;
      animation: fadeUp .8s .1s ease both;
    }
    .hero__h1 em {
      font-style: italic;
      color: #60a5fa;
    }
    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(20px); }
      to   { opacity: 1; transform: none; }
    }

    .hero__sub {
      font-size: clamp(17px, 2vw, 20px);
      color: rgba(255,255,255,.65);
      max-width: 560px;
      margin: 0 auto 36px;
      font-weight: 300;
      line-height: 1.6;
      animation: fadeUp .8s .2s ease both;
    }

    .hero__actions {
      display: flex;
      gap: 12px;
      justify-content: center;
      flex-wrap: wrap;
      margin-bottom: 48px;
      animation: fadeUp .8s .3s ease both;
    }

    .hero__trust {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 28px;
      flex-wrap: wrap;
      animation: fadeUp .8s .4s ease both;
      margin-bottom: 72px;
    }
    .hero__trust-item {
      display: flex;
      align-items: center;
      gap: 7px;
      font-size: 13px;
      color: rgba(255,255,255,.5);
      font-weight: 500;
    }
    .hero__trust-item svg { color: #4ade80; flex-shrink: 0; }

    /* dashboard card */
    .hero__dashboard {
      position: relative;
      z-index: 2;
      max-width: 900px;
      margin: 0 auto;
      padding: 0 28px;
      animation: fadeUp .9s .5s ease both;
    }
    .dashboard-card {
      background: rgba(255,255,255,.04);
      border: 1px solid rgba(255,255,255,.1);
      border-bottom: none;
      border-radius: var(--radius-lg) var(--radius-lg) 0 0;
      overflow: clip;
      box-shadow: 0 -4px 60px rgba(37,99,235,.15), inset 0 1px 0 rgba(255,255,255,.08);
    }
    .dashboard-bar {
      background: rgba(255,255,255,.06);
      border-bottom: 1px solid rgba(255,255,255,.08);
      padding: 12px 18px;
      display: flex;
      align-items: center;
      gap: 12px;
    }
    .dashboard-dots { display: flex; gap: 6px; }
    .dashboard-dot {
      width: 10px; height: 10px; border-radius: 50%;
    }
    .dashboard-dot:nth-child(1) { background: #ff5f57; }
    .dashboard-dot:nth-child(2) { background: #ffbd2e; }
    .dashboard-dot:nth-child(3) { background: #28c840; }
    .dashboard-url {
      flex: 1;
      background: rgba(255,255,255,.07);
      border-radius: 5px;
      padding: 5px 12px;
      font-size: 12px;
      color: rgba(255,255,255,.35);
      font-family: monospace;
    }
    .dashboard-body {
      padding: 24px;
      display: grid;
      grid-template-columns: 1fr 1fr 1fr;
      gap: 16px;
    }
    .dash-stat {
      background: rgba(255,255,255,.05);
      border: 1px solid rgba(255,255,255,.08);
      border-radius: var(--radius-sm);
      padding: 16px 18px;
    }
    .dash-stat__label {
      font-size: 11px;
      color: rgba(255,255,255,.4);
      font-weight: 600;
      letter-spacing: .06em;
      text-transform: uppercase;
      margin-bottom: 6px;
    }
    .dash-stat__value {
      font-size: 22px;
      font-weight: 600;
      color: var(--white);
      font-variant-numeric: tabular-nums;
    }
    .dash-stat__value--green { color: #4ade80; }
    .dash-stat__value--amber { color: #fbbf24; }
    .dash-invoices {
      grid-column: 1 / -1;
      display: flex;
      flex-direction: column;
      gap: 8px;
    }
    .dash-inv {
      display: flex;
      align-items: center;
      justify-content: space-between;
      background: rgba(255,255,255,.04);
      border: 1px solid rgba(255,255,255,.07);
      border-radius: var(--radius-sm);
      padding: 11px 16px;
      gap: 12px;
    }
    .dash-inv__name { font-size: 13px; color: rgba(255,255,255,.85); font-weight: 500; flex: 1; }
    .dash-inv__tag {
      font-size: 11px;
      font-weight: 700;
      padding: 3px 9px;
      border-radius: 100px;
      letter-spacing: .03em;
    }
    .dash-inv__tag--peppol { background: rgba(37,99,235,.25); color: #93c5fd; }
    .dash-inv__tag--paid   { background: rgba(74,222,128,.2);  color: #4ade80; }
    .dash-inv__tag--pending{ background: rgba(251,191,36,.2);  color: #fbbf24; }
    .dash-inv__tag--expense{ background: rgba(167,139,250,.2); color: #c4b5fd; }
    .dash-inv__amount { font-size: 14px; font-weight: 600; color: var(--white); font-variant-numeric: tabular-nums; }

    /* ═══════════════════════════════
       SECTION LABELS
    ═══════════════════════════════ */
    .section { padding: 96px 0; }
    .section--alt { background: var(--off-white); }
    .section--dark { background: var(--navy); }

    .section__eyebrow {
      font-size: 12px;
      font-weight: 700;
      letter-spacing: .1em;
      text-transform: uppercase;
      color: var(--blue);
      margin-bottom: 12px;
    }
    .section__eyebrow--light { color: #60a5fa; }

    .section__h2 {
      font-family: 'Instrument Serif', serif;
      font-size: clamp(32px, 4.5vw, 52px);
      line-height: 1.1;
      color: var(--text);
      margin-bottom: 16px;
    }
    .section__h2--light { color: var(--white); }

    .section__lead {
      font-size: 18px;
      color: var(--muted);
      max-width: 540px;
      line-height: 1.65;
    }
    .section__lead--light { color: rgba(255,255,255,.6); }

    /* ═══════════════════════════════
       FOR WHO CARDS
    ═══════════════════════════════ */
    .for-who__grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 20px;
      margin-top: 56px;
    }
    .for-who__card {
      background: var(--white);
      border: 1.5px solid var(--border);
      border-radius: var(--radius-lg);
      padding: 36px 32px;
      transition: border-color .2s, box-shadow .2s, transform .2s;
    }
    .for-who__card:hover {
      border-color: var(--blue-light);
      box-shadow: 0 8px 32px rgba(37,99,235,.1);
      transform: translateY(-3px);
    }
    .for-who__icon {
      width: 48px;
      height: 48px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 20px;
      font-size: 22px;
    }
    .for-who__icon--blue  { background: #eff6ff; }
    .for-who__icon--green { background: #f0fdf4; }
    .for-who__icon--violet{ background: #f5f3ff; }
    .for-who__card h3 { font-size: 18px; font-weight: 600; margin-bottom: 10px; }
    .for-who__card p  { font-size: 14.5px; color: var(--muted); line-height: 1.6; margin-bottom: 20px; }
    .for-who__tags { display: flex; flex-wrap: wrap; gap: 7px; }
    .tag {
      font-size: 12px;
      font-weight: 600;
      padding: 4px 11px;
      border-radius: 100px;
      background: var(--off-white);
      color: var(--muted);
      border: 1px solid var(--border);
    }
    .tag--blue   { background: #eff6ff; color: var(--blue); border-color: #bfdbfe; }
    .tag--green  { background: #f0fdf4; color: #059669;     border-color: #a7f3d0; }
    .tag--violet { background: #f5f3ff; color: #7c3aed;     border-color: #ddd6fe; }

    /* ═══════════════════════════════
       FEATURES GRID
    ═══════════════════════════════ */
    .features__grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 2px;
      background: var(--border);
      border-radius: var(--radius-lg);
      overflow: clip;
      margin-top: 56px;
      box-shadow: var(--shadow);
    }
    .feature-cell {
      background: var(--white);
      padding: 36px 32px;
      transition: background .2s;
      position: relative;
    }
    .feature-cell:hover { background: #fafbff; }
    .feature-cell__num {
      font-size: 11px;
      font-weight: 700;
      color: var(--blue);
      opacity: .5;
      letter-spacing: .08em;
      margin-bottom: 14px;
    }
    .feature-cell__icon { font-size: 28px; margin-bottom: 14px; line-height: 1; }
    .feature-cell h3 { font-size: 17px; font-weight: 600; margin-bottom: 8px; }
    .feature-cell p  { font-size: 14px; color: var(--muted); line-height: 1.6; }
    .feature-cell__tags { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 14px; }

    /* ═══════════════════════════════
       PEPPOL STEPS
    ═══════════════════════════════ */
    .peppol__steps {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 0;
      margin-top: 56px;
      position: relative;
    }
    .peppol__steps::before {
      content: '';
      position: absolute;
      top: 28px;
      left: 15%;
      right: 15%;
      height: 1px;
      background: linear-gradient(90deg, rgba(37,99,235,.4), rgba(37,99,235,.4));
      z-index: 0;
    }
    .peppol__step {
      text-align: center;
      padding: 0 20px;
      position: relative;
      z-index: 1;
    }
    .peppol__step-num {
      width: 56px;
      height: 56px;
      border-radius: 50%;
      background: var(--navy);
      border: 2px solid rgba(37,99,235,.5);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 18px;
      font-weight: 700;
      color: var(--white);
      margin: 0 auto 20px;
      position: relative;
    }
    .peppol__step-num::after {
      content: '';
      position: absolute;
      inset: -5px;
      border-radius: 50%;
      border: 1px solid rgba(37,99,235,.2);
    }
    .peppol__step h3 { font-size: 15px; font-weight: 600; color: var(--white); margin-bottom: 8px; }
    .peppol__step p  { font-size: 13.5px; color: rgba(255,255,255,.5); line-height: 1.55; }

    /* PEPPOL activity box */
    .peppol__activity {
      margin-top: 56px;
      background: rgba(255,255,255,.04);
      border: 1px solid rgba(255,255,255,.1);
      border-radius: var(--radius-lg);
      padding: 32px;
      max-width: 700px;
      margin-left: auto;
      margin-right: auto;
    }
    .peppol__activity-title {
      font-size: 12px;
      font-weight: 700;
      letter-spacing: .08em;
      text-transform: uppercase;
      color: rgba(255,255,255,.35);
      margin-bottom: 20px;
    }
    .peppol__row {
      display: flex;
      align-items: center;
      gap: 14px;
      padding: 10px 0;
      border-bottom: 1px solid rgba(255,255,255,.05);
    }
    .peppol__row:last-child { border-bottom: none; }
    .peppol__dir {
      font-size: 11px;
      font-weight: 700;
      padding: 3px 9px;
      border-radius: 100px;
      flex-shrink: 0;
    }
    .peppol__dir--out { background: rgba(37,99,235,.2); color: #93c5fd; }
    .peppol__dir--in  { background: rgba(74,222,128,.15); color: #4ade80; }
    .peppol__entity { flex: 1; font-size: 14px; color: rgba(255,255,255,.75); }
    .peppol__status { font-size: 12px; color: rgba(255,255,255,.4); }

    /* ═══════════════════════════════
       EXPENSE TRACKING
    ═══════════════════════════════ */
    .expense__ways {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 20px;
      margin-top: 56px;
    }
    .expense__way {
      background: var(--white);
      border: 1.5px solid var(--border);
      border-radius: var(--radius-lg);
      padding: 32px;
      transition: border-color .2s, transform .2s;
    }
    .expense__way:hover { border-color: var(--blue-light); transform: translateY(-2px); }
    .expense__way-icon {
      font-size: 32px;
      margin-bottom: 16px;
      line-height: 1;
    }
    .expense__way h3 { font-size: 16px; font-weight: 600; margin-bottom: 8px; }
    .expense__way p  { font-size: 14px; color: var(--muted); line-height: 1.6; }

    /* expense mock */
    .expense__mock {
      margin-top: 40px;
      background: var(--white);
      border: 1.5px solid var(--border);
      border-radius: var(--radius-lg);
      overflow: clip;
      box-shadow: var(--shadow);
    }
    .expense__mock-header {
      background: var(--off-white);
      border-bottom: 1px solid var(--border);
      padding: 14px 24px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    .expense__mock-title { font-size: 14px; font-weight: 600; color: var(--text); }
    .expense__mock-dl { font-size: 13px; color: var(--blue); font-weight: 500; cursor: pointer; }
    .expense__rows { padding: 8px 0; }
    .expense__row {
      display: flex;
      align-items: center;
      gap: 14px;
      padding: 12px 24px;
      border-bottom: 1px solid var(--off-white);
      transition: background .15s;
    }
    .expense__row:last-child { border-bottom: none; }
    .expense__row:hover { background: var(--off-white); }
    .expense__row-icon { font-size: 18px; flex-shrink: 0; }
    .expense__row-info { flex: 1; }
    .expense__row-name { font-size: 14px; font-weight: 500; }
    .expense__row-meta { font-size: 12px; color: var(--muted); margin-top: 2px; }
    .expense__row-amount { font-size: 14px; font-weight: 600; color: var(--text); font-variant-numeric: tabular-nums; }

    /* ═══════════════════════════════
       REMINDERS TIMELINE
    ═══════════════════════════════ */
    .reminders__timeline {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 20px;
      margin-top: 56px;
      position: relative;
    }
    .reminder__step {
      text-align: center;
    }
    .reminder__icon {
      width: 56px;
      height: 56px;
      border-radius: 50%;
      background: var(--off-white);
      border: 2px solid var(--border);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 22px;
      margin: 0 auto 16px;
      transition: border-color .2s, background .2s;
    }
    .reminder__step:hover .reminder__icon { border-color: var(--blue-light); background: #eff6ff; }
    .reminder__step h3 { font-size: 15px; font-weight: 600; margin-bottom: 6px; }
    .reminder__step p  { font-size: 13.5px; color: var(--muted); line-height: 1.55; }

    /* ═══════════════════════════════
       PAYMENTS
    ═══════════════════════════════ */
    .payments__grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 16px;
      margin-top: 56px;
    }
    .payment__card {
      background: var(--white);
      border: 1.5px solid var(--border);
      border-radius: var(--radius-lg);
      padding: 28px 30px;
      display: flex;
      flex-direction: column;
      gap: 10px;
      transition: border-color .2s, transform .2s, box-shadow .2s;
    }
    .payment__card:hover {
      border-color: var(--blue-light);
      transform: translateY(-2px);
      box-shadow: 0 8px 28px rgba(37,99,235,.08);
    }
    .payment__card h3 { font-size: 17px; font-weight: 600; }
    .payment__card p  { font-size: 14px; color: var(--muted); line-height: 1.55; }
    .payment__methods { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 4px; }

    /* ═══════════════════════════════
       STATS BAR
    ═══════════════════════════════ */
    .stats-bar {
      background: var(--navy);
      padding: 56px 0;
    }
    .stats-bar__grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 0;
    }
    .stat-item {
      text-align: center;
      padding: 0 24px;
      border-right: 1px solid rgba(255,255,255,.08);
    }
    .stat-item:last-child { border-right: none; }
    .stat-item__value {
      font-family: 'Instrument Serif', serif;
      font-size: 52px;
      color: var(--white);
      line-height: 1;
      margin-bottom: 8px;
    }
    .stat-item__label { font-size: 14px; color: rgba(255,255,255,.45); font-weight: 400; }

    /* ═══════════════════════════════
       TESTIMONIALS
    ═══════════════════════════════ */
    .testimonials__grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 20px;
      margin-top: 56px;
    }
    .testimonial {
      background: var(--white);
      border: 1.5px solid var(--border);
      border-radius: var(--radius-lg);
      padding: 32px;
      display: flex;
      flex-direction: column;
      gap: 16px;
      transition: border-color .2s, transform .2s;
    }
    .testimonial:hover { border-color: var(--blue-light); transform: translateY(-2px); }
    .testimonial__stars { color: #f59e0b; font-size: 15px; letter-spacing: 2px; }
    .testimonial__body {
      font-size: 15px;
      color: #374151;
      line-height: 1.7;
      flex: 1;
    }
    .testimonial__body::before { content: '\201C'; }
    .testimonial__body::after  { content: '\201D'; }
    .testimonial__author { display: flex; align-items: center; gap: 12px; }
    .testimonial__avatar {
      width: 38px;
      height: 38px;
      border-radius: 50%;
      background: var(--navy);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 13px;
      font-weight: 700;
      color: var(--white);
      flex-shrink: 0;
    }
    .testimonial__name { font-size: 14px; font-weight: 600; }
    .testimonial__role { font-size: 12px; color: var(--muted); }

    /* ═══════════════════════════════
       FAQ
    ═══════════════════════════════ */
    .faq__grid {
      display: grid;
      grid-template-columns: 1fr 1.6fr;
      gap: 72px;
      margin-top: 56px;
      align-items: start;
    }
    .faq__sidebar h3 {
      font-family: 'Instrument Serif', serif;
      font-size: 28px;
      margin-bottom: 14px;
      line-height: 1.2;
    }
    .faq__sidebar p {
      font-size: 15px;
      color: var(--muted);
      line-height: 1.65;
      margin-bottom: 24px;
    }
    .faq__sidebar-link {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      font-size: 14px;
      font-weight: 600;
      color: var(--blue);
      border-bottom: 1.5px solid transparent;
      transition: border-color .15s;
      padding-bottom: 2px;
    }
    .faq__sidebar-link:hover { border-color: var(--blue); }

    .faq__items { display: flex; flex-direction: column; gap: 0; }
    .faq__item {
      border-bottom: 1px solid var(--border);
    }
    .faq__item:first-child { border-top: 1px solid var(--border); }
    .faq__question {
      width: 100%;
      background: none;
      border: none;
      padding: 20px 0;
      text-align: left;
      font-family: 'DM Sans', sans-serif;
      font-size: 15.5px;
      font-weight: 600;
      color: var(--text);
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 16px;
      transition: color .15s;
    }
    .faq__question:hover { color: var(--blue); }
    .faq__item.open .faq__question { color: var(--blue); }
    .faq__chevron {
      flex-shrink: 0;
      width: 22px;
      height: 22px;
      border-radius: 50%;
      background: var(--off-white);
      border: 1px solid var(--border);
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all .2s;
      color: var(--muted);
    }
    .faq__item.open .faq__chevron {
      background: var(--blue);
      border-color: var(--blue);
      color: white;
      transform: rotate(180deg);
    }
    .faq__chevron svg { width: 12px; height: 12px; }
    .faq__answer {
      max-height: 0;
      overflow: hidden;
      transition: max-height .35s ease;
    }
    .faq__item.open .faq__answer { max-height: 300px; }
    .faq__answer-inner {
      padding: 0 0 20px;
      font-size: 15px;
      color: #4b5563;
      line-height: 1.7;
    }
    .faq__answer-inner strong { color: var(--text); }

    /* ═══════════════════════════════
       CTA FINAL
    ═══════════════════════════════ */
    .cta-final {
      background: var(--navy);
      padding: 100px 0;
      text-align: center;
      position: relative;
      /* overflow:hidden removed — breaks iOS momentum scroll */
    }
    .cta-final::before {
      content: '';
      position: absolute;
      inset: 0;
      background: radial-gradient(ellipse 700px 400px at 50% 0%, rgba(37,99,235,.2), transparent 70%);
      pointer-events: none;
    }
    .cta-final h2 {
      font-family: 'Instrument Serif', serif;
      font-size: clamp(36px, 5vw, 60px);
      color: var(--white);
      margin-bottom: 18px;
      position: relative;
    }
    .cta-final p {
      font-size: 18px;
      color: rgba(255,255,255,.55);
      max-width: 460px;
      margin: 0 auto 36px;
      font-weight: 300;
      position: relative;
    }
    .cta-final__actions {
      display: flex;
      gap: 12px;
      justify-content: center;
      flex-wrap: wrap;
      position: relative;
    }
    .cta-final__perks {
      display: flex;
      gap: 24px;
      justify-content: center;
      flex-wrap: wrap;
      margin-top: 24px;
      position: relative;
    }
    .cta-final__perk {
      font-size: 13px;
      color: rgba(255,255,255,.4);
      display: flex;
      align-items: center;
      gap: 6px;
    }
    .cta-final__perk svg { color: #4ade80; }

    /* ═══════════════════════════════
       FOOTER
    ═══════════════════════════════ */
    .footer {
      background: var(--off-white);
      border-top: 1px solid var(--border);
      padding: 56px 0 36px;
    }
    .footer__grid {
      display: grid;
      grid-template-columns: 1.8fr 1fr 1fr 1fr;
      gap: 48px;
      margin-bottom: 48px;
    }
    .footer__brand p {
      font-size: 14px;
      color: var(--muted);
      line-height: 1.65;
      margin-top: 14px;
      max-width: 260px;
    }
    .footer__logo {
      display: flex;
      align-items: center;
      gap: 9px;
      font-weight: 600;
      font-size: 16px;
      color: var(--text);
    }
    .footer__logo img { height: 28px; border-radius: 6px; }
    .footer__col h4 {
      font-size: 12px;
      font-weight: 700;
      letter-spacing: .07em;
      text-transform: uppercase;
      color: var(--muted);
      margin-bottom: 16px;
    }
    .footer__col ul { list-style: none; display: flex; flex-direction: column; gap: 10px; }
    .footer__col li a {
      font-size: 14px;
      color: var(--muted);
      transition: color .15s;
    }
    .footer__col li a:hover { color: var(--blue); }
    .footer__bottom {
      border-top: 1px solid var(--border);
      padding-top: 24px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 12px;
    }
    .footer__bottom-text { font-size: 13px; color: var(--muted); }
    .footer__langs { display: flex; gap: 8px; }
    .footer__langs a {
      font-size: 13px;
      font-weight: 600;
      color: var(--muted);
      padding: 4px 10px;
      border-radius: 6px;
      border: 1px solid var(--border);
      transition: color .15s, border-color .15s;
    }
    .footer__langs a:hover { color: var(--blue); border-color: var(--blue-light); }

    /* ═══════════════════════════════
       RESPONSIVE
    ═══════════════════════════════ */
    @media (max-width: 960px) {
      .for-who__grid,
      .features__grid,
      .expense__ways,
      .payments__grid,
      .testimonials__grid { grid-template-columns: 1fr; }
      .peppol__steps,
      .reminders__timeline { grid-template-columns: repeat(2, 1fr); }
      .peppol__steps::before { display: none; }
      .stats-bar__grid { grid-template-columns: repeat(2, 1fr); }
      .stat-item { border-right: none; border-bottom: 1px solid rgba(255,255,255,.08); padding: 24px; }
      .stat-item:nth-child(3), .stat-item:last-child { border-bottom: none; }
      .faq__grid { grid-template-columns: 1fr; gap: 36px; }
      .footer__grid { grid-template-columns: 1fr 1fr; }
      .dashboard-body { grid-template-columns: 1fr; }
    }
    @media (max-width: 600px) {
      .section { padding: 64px 0; }
      .hero { padding: 72px 0 0; }
      .nav__links .nav__link:not(.nav__lang) { display: none; }
      .peppol__steps { grid-template-columns: 1fr; }
      .reminders__timeline { grid-template-columns: 1fr; }
      .stats-bar__grid { grid-template-columns: 1fr 1fr; }
      .footer__grid { grid-template-columns: 1fr; }
      .features__grid { grid-template-columns: 1fr; background: transparent; box-shadow: none; border-radius: 0; }
      .feature-cell { border-radius: var(--radius-lg); border: 1.5px solid var(--border); margin-bottom: 2px; }
    }

    /* ═══════════════════════════════
       MOBILE-SPECIFIC FIXES
       Resolves iOS Safari scroll-lock,
       pull-to-refresh circle, and
       momentum scroll issues
    ═══════════════════════════════ */

    /* Prevent horizontal bleed without blocking vertical scroll */
    html {
      overflow-x: hidden;
      /* overscroll-behavior-y removed — causes ANR in Android WebView */
    }
    /* html can have overflow-x:hidden safely — body cannot on iOS */

    /* Touch target sizing — minimum 44px for all interactive elements */
    @media (hover: none) and (pointer: coarse) {
      .btn { min-height: 44px; padding-top: 10px; padding-bottom: 10px; }
      .btn--lg { min-height: 50px; }
      .nav__link, .nav__lang { min-height: 44px; display: flex; align-items: center; }
      .faq__question { min-height: 52px; padding: 14px 0; }
      .for-who__card:hover,
      .expense__way:hover,
      .payment__card:hover,
      .testimonial:hover { transform: none; } /* disable hover lift on touch */
      .btn--primary:hover,
      .btn--white:hover { transform: none; }
    }

    /* Prevent scroll-chaining from inner containers triggering pull-to-refresh */
    @media (max-width: 960px) {
      .comparison {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        overscroll-behavior-x: contain;
      }
      .dashboard-body {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        overscroll-behavior-x: contain;
      }
      /* Accounting layout stack on tablet */
      .accounting__layout { grid-template-columns: 1fr; gap: 36px; }
      .accounting__layout--flip { direction: ltr; }
      .comparison__table { grid-template-columns: 2fr 1fr 1fr; }
      .comparison__table .comparison__col-head:last-child,
      .comparison__table .comparison__cell:nth-child(4n) { display: none; }
    }

    @media (max-width: 600px) {
      /* Hero tweaks */
      .hero__h1 { font-size: clamp(32px, 9vw, 48px); }
      .hero__sub { font-size: 16px; }
      .hero__trust { gap: 14px; }
      .hero__trust-item { font-size: 12px; }
      .hero__badge { font-size: 12px; padding: 5px 13px; }
      .hero__actions { flex-direction: column; align-items: center; }
      .hero__actions .btn { width: 100%; max-width: 320px; justify-content: center; }

      /* Nav */
      .nav__inner { padding: 0 16px; }
      .nav__cta .btn--ghost { display: none; } /* hide Sign In on very small screens */

      /* Containers */
      .container, .container--narrow { padding: 0 16px; }

      /* Section spacing */
      .section { padding: 52px 0; }
      .stats-bar { padding: 40px 0; }
      .cta-final { padding: 72px 0; }

      /* Section text */
      .section__h2 { font-size: clamp(26px, 7vw, 38px); }
      .section__lead { font-size: 16px; }

      /* Stats */
      .stat-item__value { font-size: 40px; }

      /* Grids */
      .peppol__steps { grid-template-columns: 1fr; }
      .reminders__timeline { grid-template-columns: 1fr; }
      .stats-bar__grid { grid-template-columns: 1fr 1fr; }
      .footer__grid { grid-template-columns: 1fr; }
      .for-who__grid,
      .expense__ways,
      .payments__grid,
      .testimonials__grid { grid-template-columns: 1fr; }
      .features__grid { grid-template-columns: 1fr; background: transparent; box-shadow: none; border-radius: 0; }
      .feature-cell { border-radius: var(--radius-lg); border: 1.5px solid var(--border); margin-bottom: 2px; }

      /* Comparison table — horizontally scrollable on mobile */
      .comparison { overflow-x: auto; -webkit-overflow-scrolling: touch; overscroll-behavior-x: contain; border-radius: var(--radius); }
      .comparison__table { grid-template-columns: 1.6fr 1fr 1fr; min-width: 480px; }
      .comparison__table .comparison__col-head:last-child,
      .comparison__table .comparison__cell:nth-child(4n) { display: none; }

      /* PEPPOL steps */
      .peppol__activity { padding: 20px 16px; }

      /* Accounting panels */
      .accounting__layout { margin-top: 40px; gap: 28px; }
      .accounting__panel-header { flex-direction: column; align-items: flex-start; gap: 6px; }

      /* FAQ */
      .faq__grid { gap: 24px; }

      /* Footer */
      .footer { padding: 40px 0 24px; }
      .footer__grid { gap: 32px; }
      .footer__bottom { flex-direction: column; gap: 8px; }

      /* CTA */
      .cta-final__actions { flex-direction: column; align-items: center; }
      .cta-final__actions .btn { width: 100%; max-width: 320px; justify-content: center; }
      .cta-final__perks { gap: 12px; }

      /* Scroll hint for comparison table */
      .comparison::before {
        content: '← scroll →';
        display: block;
        text-align: center;
        font-size: 11px;
        color: rgba(255,255,255,.3);
        padding: 8px 0 0;
        letter-spacing: .06em;
      }
    }

    /* Smooth scrolling — disable on reduced-motion preference */
    @media (prefers-reduced-motion: reduce) {
      html { scroll-behavior: auto; }
      .reveal {
        opacity: 1 !important;
        transform: none !important;
        transition: none !important;
      }
      .hero__badge, .hero__h1, .hero__sub, .hero__actions, .hero__trust, .hero__dashboard {
        animation: none !important;
      }
      .hero__badge-dot { animation: none !important; }
    }
    .accounting__layout {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 64px;
      align-items: center;
      margin-top: 64px;
    }
    .accounting__layout--flip { direction: rtl; }
    .accounting__layout--flip > * { direction: ltr; }

    .accounting__text-block .section__eyebrow { margin-bottom: 10px; }
    .accounting__text-block h3 {
      font-family: 'Instrument Serif', serif;
      font-size: clamp(26px, 3vw, 38px);
      line-height: 1.15;
      color: var(--text);
      margin-bottom: 14px;
    }
    .accounting__text-block p {
      font-size: 15.5px;
      color: var(--muted);
      line-height: 1.7;
      margin-bottom: 20px;
    }
    .accounting__checklist {
      list-style: none;
      display: flex;
      flex-direction: column;
      gap: 11px;
      margin-bottom: 28px;
    }
    .accounting__checklist li {
      display: flex;
      align-items: flex-start;
      gap: 10px;
      font-size: 14.5px;
      color: var(--text);
      line-height: 1.5;
    }
    .accounting__checklist li::before {
      content: '';
      width: 18px;
      height: 18px;
      border-radius: 50%;
      background: #dcfce7;
      border: 1.5px solid #86efac;
      flex-shrink: 0;
      margin-top: 1px;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='%2316a34a'%3E%3Cpath fill-rule='evenodd' d='M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z' clip-rule='evenodd'/%3E%3C/svg%3E");
      background-size: 11px;
      background-repeat: no-repeat;
      background-position: center;
    }

    /* Tax panel mock-up */
    .accounting__panel {
      background: var(--white);
      border: 1.5px solid var(--border);
      border-radius: var(--radius-lg);
      overflow: clip;
      box-shadow: var(--shadow-lg);
    }
    .accounting__panel-header {
      background: var(--navy);
      padding: 16px 22px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    .accounting__panel-title {
      font-size: 13px;
      font-weight: 600;
      color: rgba(255,255,255,.7);
      letter-spacing: .04em;
    }
    .accounting__panel-badge {
      font-size: 11px;
      font-weight: 700;
      padding: 3px 10px;
      border-radius: 100px;
      background: rgba(74,222,128,.2);
      color: #4ade80;
      letter-spacing: .04em;
    }
    .accounting__panel-body { padding: 20px 22px; }
    .acct-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 13px 0;
      border-bottom: 1px solid var(--off-white);
      gap: 12px;
    }
    .acct-row:last-child { border-bottom: none; }
    .acct-row__label {
      display: flex;
      align-items: center;
      gap: 10px;
      font-size: 13.5px;
      color: var(--muted);
    }
    .acct-row__icon {
      width: 30px;
      height: 30px;
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 14px;
      flex-shrink: 0;
    }
    .acct-row__icon--blue   { background: #eff6ff; }
    .acct-row__icon--amber  { background: #fffbeb; }
    .acct-row__icon--green  { background: #f0fdf4; }
    .acct-row__icon--violet { background: #f5f3ff; }
    .acct-row__icon--red    { background: #fef2f2; }
    .acct-row__right { text-align: right; }
    .acct-row__value {
      font-size: 14px;
      font-weight: 600;
      color: var(--text);
      font-variant-numeric: tabular-nums;
    }
    .acct-row__sub {
      font-size: 11px;
      color: var(--muted);
      margin-top: 2px;
    }
    .acct-row__status {
      font-size: 11px;
      font-weight: 700;
      padding: 3px 9px;
      border-radius: 100px;
    }
    .acct-row__status--ready  { background: #dcfce7; color: #15803d; }
    .acct-row__status--due    { background: #fef3c7; color: #92400e; }
    .acct-row__status--done   { background: #eff6ff; color: #1d4ed8; }

    /* Provision bar */
    .acct-provision {
      margin-top: 16px;
      background: var(--off-white);
      border-radius: var(--radius-sm);
      padding: 14px 16px;
    }
    .acct-provision__label {
      font-size: 11px;
      font-weight: 700;
      letter-spacing: .06em;
      text-transform: uppercase;
      color: var(--muted);
      margin-bottom: 10px;
    }
    .acct-provision__bar-wrap {
      background: var(--border);
      border-radius: 100px;
      height: 8px;
      overflow: hidden;
      margin-bottom: 8px;
    }
    .acct-provision__bar {
      height: 100%;
      border-radius: 100px;
      background: linear-gradient(90deg, var(--blue), #60a5fa);
    }
    .acct-provision__info {
      display: flex;
      justify-content: space-between;
      font-size: 12px;
      color: var(--muted);
    }
    .acct-provision__amount { font-weight: 700; color: var(--text); }

    /* Comparison strip */
    .comparison {
      margin-top: 80px;
      background: var(--navy);
      border-radius: var(--radius-lg);
      /* overflow:hidden removed — causes iOS scroll issues inside containers */
      overflow: clip;
    }
    .comparison__header {
      padding: 28px 36px 20px;
      border-bottom: 1px solid rgba(255,255,255,.08);
    }
    .comparison__header h3 {
      font-family: 'Instrument Serif', serif;
      font-size: 26px;
      color: var(--white);
      margin-bottom: 6px;
    }
    .comparison__header p {
      font-size: 14px;
      color: rgba(255,255,255,.45);
    }
    .comparison__table {
      display: grid;
      grid-template-columns: 2fr 1fr 1fr 1fr;
      gap: 0;
    }
    .comparison__col-head {
      padding: 14px 20px;
      font-size: 12px;
      font-weight: 700;
      letter-spacing: .06em;
      text-transform: uppercase;
      color: rgba(255,255,255,.4);
      border-bottom: 1px solid rgba(255,255,255,.06);
    }
    .comparison__col-head--highlight {
      background: rgba(37,99,235,.15);
      color: #93c5fd;
      border-bottom-color: rgba(37,99,235,.3);
    }
    .comparison__feature {
      padding: 14px 20px;
      font-size: 13.5px;
      color: rgba(255,255,255,.65);
      border-bottom: 1px solid rgba(255,255,255,.05);
      display: flex;
      align-items: center;
    }
    .comparison__cell {
      padding: 14px 20px;
      border-bottom: 1px solid rgba(255,255,255,.05);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 13px;
      color: rgba(255,255,255,.4);
    }
    .comparison__cell--highlight {
      background: rgba(37,99,235,.07);
    }
    .comparison__check { color: #4ade80; font-size: 16px; }
    .comparison__cross { color: rgba(255,255,255,.2); font-size: 16px; }
    .comparison__partial { font-size: 11px; color: #fbbf24; font-weight: 600; }

    @media (max-width: 960px) {
      .accounting__layout { grid-template-columns: 1fr; gap: 36px; }
      .accounting__layout--flip { direction: ltr; }
      .comparison__table { grid-template-columns: 2fr 1fr 1fr; }
      .comparison__table .comparison__col-head:last-child,
      .comparison__table .comparison__cell:nth-child(4n) { display: none; }
    }
    @media (max-width: 600px) {
      .comparison__table { grid-template-columns: 1fr 1fr; }
      .comparison__table > *:nth-child(3),
      .comparison__table > *:nth-child(4) { display: none; }
      .comparison__table .comparison__col-head:nth-child(3),
      .comparison__table .comparison__col-head:nth-child(4) { display: none; }
    }
    
    
    :root {
      --eb-brand:       #2563eb;
      --eb-brand-dark:  #1d4ed8;
      --eb-brand-soft:  #eff6ff;
      --eb-text:        #0f172a;
      --eb-muted:       #64748b;
      --eb-border:      #e2e8f0;
      --eb-surface:     #ffffff;
      --eb-radius:      14px;
      --eb-shadow-sm:   0 1px 4px rgba(15,23,42,.07);
      --eb-shadow-md:   0 8px 28px rgba(15,23,42,.12);
      --eb-success:     #16a34a;
    }
     
    .eb-section-badge {
      display: inline-block;
      background: var(--eb-brand-soft);
      color: var(--eb-brand);
      font-size: .72rem;
      font-weight: 700;
      letter-spacing: .12em;
      text-transform: uppercase;
      padding: .3rem .9rem;
      border-radius: 999px;
      border: 1px solid #bfdbfe;
    }
    .eb-section-title {
      font-family: 'Syne', sans-serif;
      font-size: clamp(1.9rem, 3.5vw, 2.6rem);
      font-weight: 800;
      color: var(--eb-text);
      line-height: 1.18;
    }
    .eb-section-title em { color: var(--eb-brand); font-style: normal; }
    .eb-section-sub {
      font-size: 1rem;
      color: var(--eb-muted);
      max-width: 540px;
      line-height: 1.7;
    }
     
    /* ── Pricing grid ── */
    .eb-pricing-row {
      display: grid;
      grid-template-columns: repeat(5, 1fr);
      gap: 1rem;
      align-items: start;
    }
    @media (max-width: 1200px) { .eb-pricing-row { grid-template-columns: repeat(3, 1fr); } }
    @media (max-width: 860px)  { .eb-pricing-row { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 560px)  { .eb-pricing-row { grid-template-columns: 1fr; } }
     
    /* ── Plan card ── */
    .eb-pc {
      background: var(--eb-surface);
      border: 1.5px solid var(--eb-border);
      border-radius: var(--eb-radius);
      box-shadow: var(--eb-shadow-sm);
      display: flex;
      flex-direction: column;
      position: relative;
      overflow: visible;
      transition: transform .22s, box-shadow .22s;
    }
    .eb-pc:hover { transform: translateY(-4px); box-shadow: var(--eb-shadow-md); }
     
    .eb-pc--featured {
      border-color: var(--eb-brand);
      box-shadow: 0 6px 28px rgba(37,99,235,.2);
      background: linear-gradient(160deg, #f0f7ff 0%, #fff 55%);
    }
    .eb-pc--featured:hover { box-shadow: 0 14px 40px rgba(37,99,235,.28); }
     
    .eb-pc-badge {
      position: absolute;
      top: -12px; left: 50%;
      transform: translateX(-50%);
      font-size: .66rem; font-weight: 700;
      letter-spacing: .5px; text-transform: uppercase;
      padding: .22rem .8rem; border-radius: 999px;
      white-space: nowrap; z-index: 1;
    }
    .badge-popular { background: var(--eb-brand); color: #fff; }
    .badge-new     { background: #059669;         color: #fff; }
    .badge-current { background: #7c3aed;         color: #fff; }
     
    .eb-pc-head {
      padding: 1.5rem 1rem 1rem;
      text-align: center;
      border-bottom: 1px solid var(--eb-border);
    }
    .eb-pc-name {
      font-family: 'Syne', sans-serif;
      font-size: .78rem; font-weight: 700;
      text-transform: uppercase; letter-spacing: .6px;
      color: var(--eb-muted); margin-bottom: .5rem;
    }
    .eb-pc-price-wrap {
      display: flex; align-items: flex-end; justify-content: center;
      gap: 2px; line-height: 1;
    }
    .eb-pc-eur { font-size: .95rem; font-weight: 600; color: var(--eb-muted); margin-bottom: 4px; }
    .eb-pc-amt { font-family: 'Syne', sans-serif; font-size: 1.9rem; font-weight: 800; color: var(--eb-text); line-height: 1; }
    .eb-pc-per { font-size: .72rem; color: var(--eb-muted); margin-bottom: 5px; }
    .eb-pc-tagline { font-size: .78rem; color: var(--eb-muted); margin: .5rem 0 0; }
     
    .eb-pc-feats { list-style: none; padding: .85rem .9rem; margin: 0; flex: 1; }
    .eb-pc-feats li {
      display: flex; align-items: flex-start; gap: .5rem;
      font-size: .82rem; padding: .32rem 0;
      border-bottom: 1px solid #f1f5f9; color: var(--eb-text);
    }
    .eb-pc-feats li:last-child { border-bottom: none; }
    .eb-pc-feats li i { flex-shrink: 0; font-size: .72rem; margin-top: 3px; }
    .eb-pc-feats li.yes i { color: var(--eb-success); }
    .eb-pc-feats li.no  { color: #94a3b8; }
    .eb-pc-feats li.no  i { color: #cbd5e1; }
     
    .eb-tag {
      display: inline-block; background: #fef9c3; color: #92400e;
      font-size: .65rem; font-weight: 700; padding: 1px 5px;
      border-radius: 4px; vertical-align: middle;
    }
     
    .eb-pc-foot {
      padding: .85rem .9rem 1rem;
      border-top: 1px solid var(--eb-border);
    }
    .eb-pc-note { text-align: center; font-size: .73rem; color: var(--eb-muted); margin: .5rem 0 0; }
     
    /* ── Buttons ── */
    .eb-btn-primary {
      display: inline-flex; align-items: center; justify-content: center;
      background: var(--eb-brand); color: #fff !important;
      border: 1.5px solid var(--eb-brand); border-radius: 8px;
      padding: .5rem 1rem; font-size: .83rem; font-weight: 600;
      text-decoration: none; transition: background .2s, box-shadow .2s;
    }
    .eb-btn-primary:hover { background: var(--eb-brand-dark); box-shadow: 0 4px 14px rgba(37,99,235,.35); }
     
    .eb-btn-outline {
      display: inline-flex; align-items: center; justify-content: center;
      background: transparent; color: var(--eb-brand) !important;
      border: 1.5px solid var(--eb-brand); border-radius: 8px;
      padding: .5rem 1rem; font-size: .83rem; font-weight: 600;
      text-decoration: none; transition: background .2s, color .2s;
    }
    .eb-btn-outline:hover { background: var(--eb-brand); color: #fff !important; }
     
    .eb-btn-sm-primary {
      display: inline-block; background: var(--eb-brand); color: #fff !important;
      border: none; border-radius: 6px; padding: .3rem .7rem;
      font-size: .75rem; font-weight: 600; text-decoration: none;
    }
    .eb-btn-sm-outline {
      display: inline-block; background: transparent; color: var(--eb-brand) !important;
      border: 1px solid var(--eb-brand); border-radius: 6px; padding: .3rem .7rem;
      font-size: .75rem; font-weight: 600; text-decoration: none;
    }
     
    /* ── Comparison table ── */
    .eb-comp-wrap { border: 1px solid var(--eb-border); border-radius: var(--eb-radius); overflow: hidden; }
    .eb-comp-toggle {
      width: 100%; background: #f8faff; border: none;
      padding: .9rem 1.25rem;
      font-family: 'DM Sans', sans-serif; font-size: .88rem; font-weight: 600;
      color: var(--eb-text); display: flex; align-items: center; cursor: pointer;
      transition: background .15s;
    }
    .eb-comp-toggle:hover { background: #eff6ff; }
    .eb-comp-toggle[aria-expanded="true"] .eb-chev { transform: rotate(180deg); }
    .eb-chev { transition: transform .25s; }
     
    .eb-comp-table { width: 100%; border-collapse: collapse; font-size: .84rem; }
    .eb-comp-table thead th {
      background: #f9fafb; border-bottom: 2px solid var(--eb-border);
      font-weight: 700; padding: .65rem .75rem; text-align: center;
      white-space: nowrap; color: var(--eb-text);
    }
    .eb-comp-table thead th:first-child { text-align: left; }
    .eb-comp-table td {
      padding: .5rem .75rem; vertical-align: middle;
      text-align: center; border-bottom: 1px solid #f3f4f6; color: var(--eb-text);
    }
    .eb-comp-table td:first-child { text-align: left; color: var(--eb-muted); font-weight: 500; }
    .eb-comp-table tr:hover td { background: #fafbff; }
    .eb-comp-table tfoot td {
      background: #f9fafb; padding: .65rem .75rem;
      border-top: 2px solid var(--eb-border); text-align: center;
    }
    .col-featured { background: #f0f7ff; }
    .eb-yes { color: var(--eb-success); }
    .eb-no  { color: #d1d5db; }
    .eb-tiny { font-size: .72em; color: var(--eb-muted); }
     
    /* ── Trust badges ── */
    .eb-trust-row { display: flex; flex-wrap: wrap; justify-content: center; gap: .55rem; }
    .eb-trust-badge {
      font-size: .78rem; color: var(--eb-muted);
      background: #fff; border: 1px solid var(--eb-border);
      border-radius: 999px; padding: .28rem .85rem;
    }
  </style>
</head>
<body>

<!-- ═══════ NAV ═══════ -->
<nav class="nav">
  <div class="nav__inner">
    <a class="nav__logo" href="https://eurobillr.com/">
      <img src="https://eurobillr.com/images/eurobillr.com.jpg" alt="Eurobillr logo" />
      Eurobillr
    </a>
    <div class="nav__links">
      <a class="nav__link" href="#features">Features</a>
      <a class="nav__link" href="#accounting">Accounting</a>
      <a class="nav__link" href="#peppol">PEPPOL</a>
      <a class="nav__link" href="#faq">FAQ</a>
      <a class="nav__lang" href="https://eurobillr.com/peppol-lookup.php">Peppol Lookup</a>
      <a class="nav__lang" href="https://eurobillr.com/nl/">NL</a>
      <a class="nav__lang" href="https://eurobillr.com/fr/">FR</a>
    </div>
    <div class="nav__cta">
      <a class="btn btn--ghost" href="https://eurobillr.com/auth/login.php">Sign In</a>
      <a class="btn btn--primary" href="https://eurobillr.com/auth/register.php">Start Free</a>
    </div>
  </div>
</nav>

<!-- ═══════ HERO ═══════ -->
<section class="hero">
  <div class="hero__content">
    <div class="hero__badge">
      <span class="hero__badge-dot"></span>
      PEPPOL Certified · Belgium &amp; EU
    </div>
    <h1 class="hero__h1">
      Invoicing &amp; Accounting<br />for <em>Freelancers</em> &amp;<br />Small Business
    </h1>
    <p class="hero__sub">
      Send invoices, track expenses automatically, collect payments via Stripe, Mollie, PayPal or bank transfer — and stay compliant with PEPPOL e-invoicing.
    </p>
    <div class="hero__actions">
      <a class="btn btn--primary btn--lg" href="https://eurobillr.com/auth/register.php">Start for Free</a>
       <a class="btn btn--outline-white btn--lg" href="demo.php">
        <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20"><path d="M6.3 2.84A1.5 1.5 0 004 4.11v11.78a1.5 1.5 0 002.3 1.27l9.344-5.891a1.5 1.5 0 000-2.538L6.3 2.84z"/></svg>
        Watch Demo
      </a>
    </div>
    <div class="hero__trust">
      <div class="hero__trust-item">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
        No credit card
      </div>
      <div class="hero__trust-item">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
        PEPPOL included
      </div>
      <div class="hero__trust-item">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
        Auto expense tracking
      </div>
      <div class="hero__trust-item">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
        Tax reports
      </div>
    </div>
  </div>

  <!-- Dashboard mockup -->
  <div class="hero__dashboard">
    <div class="dashboard-card">
      <div class="dashboard-bar">
        <div class="dashboard-dots">
          <div class="dashboard-dot"></div>
          <div class="dashboard-dot"></div>
          <div class="dashboard-dot"></div>
        </div>
        <div class="dashboard-url">app.eurobillr.com/dashboard</div>
      </div>
      <div class="dashboard-body">
        <div class="dash-stat">
          <div class="dash-stat__label">Revenue</div>
          <div class="dash-stat__value dash-stat__value--green">€12,480</div>
        </div>
        <div class="dash-stat">
          <div class="dash-stat__label">Pending</div>
          <div class="dash-stat__value dash-stat__value--amber">€2,340</div>
        </div>
        <div class="dash-stat">
          <div class="dash-stat__label">Expenses</div>
          <div class="dash-stat__value">€3,120</div>
        </div>
        <div class="dash-invoices">
          <div class="dash-inv">
            <span class="dash-inv__name">Acme Corp BV · INV-2025-084</span>
            <span class="dash-inv__tag dash-inv__tag--peppol">PEPPOL</span>
            <span class="dash-inv__amount">€1,850.00</span>
          </div>
          <div class="dash-inv">
            <span class="dash-inv__name">Studio Gris · Payment link</span>
            <span class="dash-inv__tag dash-inv__tag--paid">Paid</span>
            <span class="dash-inv__amount">€640.00</span>
          </div>
          <div class="dash-inv">
            <span class="dash-inv__name">Tech Solutions NV · Reminder sent</span>
            <span class="dash-inv__tag dash-inv__tag--pending">Pending</span>
            <span class="dash-inv__amount">€2,340.00</span>
          </div>
          <div class="dash-inv">
            <span class="dash-inv__name">Office Supply Co · Auto-expense</span>
            <span class="dash-inv__tag dash-inv__tag--expense">Expense</span>
            <span class="dash-inv__amount">−€187.50</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ═══════ FOR WHO ═══════ -->
<section class="section" id="for-who">
  <div class="container">
    <div class="reveal">
      <p class="section__eyebrow">Built For You</p>
      <h2 class="section__h2">One platform, every type of business</h2>
      <p class="section__lead">Whether you're a solo consultant or running a small team, Eurobillr handles the business admin so you can focus on work that matters.</p>
    </div>
    <div class="for-who__grid">
      <div class="for-who__card reveal reveal-delay-1">
        <div class="for-who__icon for-who__icon--blue">💼</div>
        <h3>Freelancers</h3>
        <p>Designers, developers, consultants, writers — send professional invoices and get paid directly via Stripe, PayPal or bank transfer.</p>
        <div class="for-who__tags">
          <span class="tag tag--blue">Payment links</span>
          <span class="tag tag--blue">QR invoices</span>
          <span class="tag tag--blue">Reminders</span>
        </div>
      </div>
      <div class="for-who__card reveal reveal-delay-2">
        <div class="for-who__icon for-who__icon--green">🏢</div>
        <h3>Small Businesses</h3>
        <p>Track & manage all your business expenses, scan receipts, receive PEPPOL invoices from suppliers, and generate income &amp; tax reports in one click.</p>
        <div class="for-who__tags">
          <span class="tag tag--green">PEPPOL e-invoicing</span>
          <span class="tag tag--green">Expense reports</span>
          <span class="tag tag--green">Tax submission</span>
        </div>
      </div>
      <div class="for-who__card reveal reveal-delay-3">
        <div class="for-who__icon for-who__icon--violet">🏗️</div>
        <h3>SMEs &amp; Agencies</h3>
        <p>Connect to the EU PEPPOL network, trade with government entities and large companies, and automate your entire invoice-to-expense workflow.</p>
        <div class="for-who__tags">
          <span class="tag tag--violet">B2G invoicing</span>
          <span class="tag tag--violet">VAT validation</span>
          <span class="tag tag--violet">Auto-categorization</span>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ═══════ FEATURES ═══════ -->
<section class="section section--alt" id="features">
  <div class="container">
    <div class="reveal">
      <p class="section__eyebrow">Features</p>
      <h2 class="section__h2">Everything you need,<br />nothing you don't</h2>
      <p class="section__lead">A complete suite of business tools designed to save you hours every month.</p>
    </div>
    <div class="features__grid reveal">
      <div class="feature-cell">
        <div class="feature-cell__num">01</div>
        <div class="feature-cell__icon">🌐</div>
        <h3>PEPPOL E-Invoicing</h3>
        <p>Register once on the global PEPPOL network and exchange legally compliant invoices with any connected business, government, or public entity worldwide.</p>
        <div class="feature-cell__tags">
          <span class="tag tag--blue">Global network</span>
          <span class="tag tag--blue">Auto-compliance</span>
          <span class="tag tag--blue">B2G ready</span>
        </div>
      </div>
      <div class="feature-cell">
        <div class="feature-cell__num">02</div>
        <div class="feature-cell__icon">📸</div>
        <h3>Scan &amp; Add Expenses</h3>
        <p>Photograph a receipt and our OCR extracts the data instantly. Or add expenses manually — every category, every transaction, perfectly organised.</p>
      </div>
      <div class="feature-cell">
        <div class="feature-cell__num">03</div>
        <div class="feature-cell__icon">🔔</div>
        <h3>Automatic Reminders</h3>
        <p>Set it and forget it. Eurobillr sends polite, professional email reminders to your clients automatically before invoices fall overdue.</p>
      </div>
      <div class="feature-cell">
        <div class="feature-cell__num">04</div>
        <div class="feature-cell__icon">📊</div>
        <h3>Income Reports</h3>
        <p>Download detailed income statements, expense breakdowns, and profit &amp; loss reports — ready for your accountant or tax authority.</p>
      </div>
      <div class="feature-cell">
        <div class="feature-cell__num">05</div>
        <div class="feature-cell__icon">🧾</div>
        <h3>Tax Submission</h3>
        <p>Submit your VAT and income tax reports directly. Eurobillr pre-fills everything from your invoices and expenses — review, confirm, done.</p>
      </div>
      <div class="feature-cell">
        <div class="feature-cell__num">06</div>
        <div class="feature-cell__icon">📱</div>
        <h3>QR Code Invoices</h3>
        <p>Every invoice includes a scannable QR code that takes clients straight to your payment page. Fast, frictionless, and professional.</p>
      </div>
    </div>
  </div>
</section>

<!-- ═══════ PEPPOL ═══════ -->
<section class="section section--dark" id="peppol">
  <div class="container">
    <div class="reveal">
      <p class="section__eyebrow section__eyebrow--light">PEPPOL Network</p>
      <h2 class="section__h2 section__h2--light">The global standard<br />for e-invoicing</h2>
      <p class="section__lead section__lead--light">PEPPOL is the EU-mandated network for exchanging electronic invoices with governments and large corporations. Register once through Eurobillr and you're connected worldwide.</p>
    </div>
    <div class="peppol__steps reveal">
      <div class="peppol__step">
        <div class="peppol__step-num">1</div>
        <h3>Register Your Company</h3>
        <p>Enter your business details and get a unique PEPPOL ID that identifies you on the global network.</p>
      </div>
      <div class="peppol__step">
        <div class="peppol__step-num">2</div>
        <h3>Connect to the Network</h3>
        <p>We handle all technical setup. No access points to configure, no certificates to manage.</p>
      </div>
      <div class="peppol__step">
        <div class="peppol__step-num">3</div>
        <h3>Send &amp; Receive Instantly</h3>
        <p>Exchange invoices with any PEPPOL-connected entity worldwide — businesses, governments, public bodies.</p>
      </div>
      <div class="peppol__step">
        <div class="peppol__step-num">4</div>
        <h3>Expenses Auto-Captured</h3>
        <p>Every incoming PEPPOL invoice is automatically added to your expenses — categorised, sorted, no data entry.</p>
      </div>
    </div>
    <div class="peppol__activity reveal">
      <div class="peppol__activity-title">Live PEPPOL network activity</div>
      <div class="peppol__row">
        <span class="peppol__dir peppol__dir--out">OUT</span>
        <span class="peppol__entity">Gemeente Antwerpen</span>
        <span class="peppol__status">✓ Delivered</span>
      </div>
      <div class="peppol__row">
        <span class="peppol__dir peppol__dir--out">OUT</span>
        <span class="peppol__entity">Acme Corp Netherlands BV</span>
        <span class="peppol__status">✓ Delivered</span>
      </div>
      <div class="peppol__row">
        <span class="peppol__dir peppol__dir--in">IN</span>
        <span class="peppol__entity">Office Supply GmbH → auto-expense</span>
        <span class="peppol__status">€212.00 logged</span>
      </div>
      <div class="peppol__row">
        <span class="peppol__dir peppol__dir--in">IN</span>
        <span class="peppol__entity">Cloud Services SAS → auto-expense</span>
        <span class="peppol__status">€200.50 logged</span>
      </div>
    </div>
    <div style="text-align:center;margin-top:40px;" class="reveal">
      <a class="btn btn--primary btn--lg" href="https://eurobillr.com/auth/register.php">Join the PEPPOL Network</a>
      <p style="margin-top:14px;font-size:13px;color:rgba(255,255,255,.35);">38+ countries supported · Mandatory in Belgium since Jan 2026</p>
    </div>
  </div>
</section>

<!-- ═══════ EXPENSE TRACKING ═══════ -->
<section class="section" id="expenses">
  <div class="container">
    <div class="reveal">
      <p class="section__eyebrow">Expense Tracking</p>
      <h2 class="section__h2">Track every expense,<br />three ways</h2>
      <p class="section__lead">No more lost receipts or forgotten costs. Add expenses your way — manually, by scanning, or fully automatically via PEPPOL.</p>
    </div>
    <div class="expense__ways">
      <div class="expense__way reveal reveal-delay-1">
        <div class="expense__way-icon">✏️</div>
        <h3>Manual Entry</h3>
        <p>Add any expense in seconds — category, amount, vendor, date. Fully structured and searchable.</p>
      </div>
      <div class="expense__way reveal reveal-delay-2">
        <div class="expense__way-icon">📷</div>
        <h3>Scan a Receipt</h3>
        <p>Point your camera at any receipt. OCR extracts vendor, amount, date automatically — no typing required.</p>
      </div>
      <div class="expense__way reveal reveal-delay-3">
        <div class="expense__way-icon">⚡</div>
        <h3>Auto via PEPPOL</h3>
        <p>Incoming PEPPOL invoices land directly in your expense list. Zero effort, zero manual entry required.</p>
      </div>
    </div>
    <div class="expense__mock reveal">
      <div class="expense__mock-header">
        <span class="expense__mock-title">Expenses — March 2025</span>
        <span class="expense__mock-dl">↓ Download PDF</span>
      </div>
      <div class="expense__rows">
        <div class="expense__row">
          <span class="expense__row-icon">🏢</span>
          <div class="expense__row-info">
            <div class="expense__row-name">Office Supply GmbH</div>
            <div class="expense__row-meta">PEPPOL auto · Office</div>
          </div>
          <span class="expense__row-amount">€212.00</span>
        </div>
        <div class="expense__row">
          <span class="expense__row-icon">🎨</span>
          <div class="expense__row-info">
            <div class="expense__row-name">Adobe Creative Cloud</div>
            <div class="expense__row-meta">Scanned receipt · Software</div>
          </div>
          <span class="expense__row-amount">€89.99</span>
        </div>
        <div class="expense__row">
          <span class="expense__row-icon">🚆</span>
          <div class="expense__row-info">
            <div class="expense__row-name">Train Brussels ↔ Ghent</div>
            <div class="expense__row-meta">Manual · Travel</div>
          </div>
          <span class="expense__row-amount">€24.60</span>
        </div>
        <div class="expense__row">
          <span class="expense__row-icon">☁️</span>
          <div class="expense__row-info">
            <div class="expense__row-name">Cloud Services SAS</div>
            <div class="expense__row-meta">PEPPOL auto · Infrastructure</div>
          </div>
          <span class="expense__row-amount">€200.50</span>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ═══════ REMINDERS ═══════ -->
<section class="section section--alt">
  <div class="container">
    <div class="reveal">
      <p class="section__eyebrow">Auto Reminders</p>
      <h2 class="section__h2">Never chase an invoice again</h2>
      <p class="section__lead">Eurobillr sends friendly payment reminders automatically — before, on, and after the due date. You get paid faster without the awkward follow-up.</p>
    </div>
    <div class="reminders__timeline">
      <div class="reminder__step reveal reveal-delay-1">
        <div class="reminder__icon">📤</div>
        <h3>Invoice Sent</h3>
        <p>You create and send an invoice. A due date is set — 14, 30, or 60 days.</p>
      </div>
      <div class="reminder__step reveal reveal-delay-2">
        <div class="reminder__icon">💬</div>
        <h3>Pre-Due Reminder</h3>
        <p>A polite reminder lands in your client's inbox 3–7 days before the due date.</p>
      </div>
      <div class="reminder__step reveal reveal-delay-3">
        <div class="reminder__icon">🔁</div>
        <h3>Overdue Follow-up</h3>
        <p>If unpaid, a professional follow-up is sent — with a direct payment link attached.</p>
      </div>
      <div class="reminder__step reveal reveal-delay-4">
        <div class="reminder__icon">✅</div>
        <h3>Payment Received</h3>
        <p>The invoice is marked paid automatically when the payment clears. You're notified instantly.</p>
      </div>
    </div>
  </div>
</section>

<!-- ═══════ PAYMENTS ═══════ -->
<section class="section">
  <div class="container">
    <div class="reveal">
      <p class="section__eyebrow">Payment Methods</p>
      <h2 class="section__h2">Set your method,<br />get paid directly</h2>
      <p class="section__lead">Connect the payment gateway you already use. All payments land straight in your account — Eurobillr never touches your money.</p>
    </div>
    <div class="payments__grid">
      <div class="payment__card reveal reveal-delay-1">
        <h3>Stripe</h3>
        <p>Accept cards, bank debits, and SEPA payments globally. Instant payouts to your Stripe account.</p>
        <div class="payment__methods">
          <span class="tag tag--blue">Cards</span>
          <span class="tag tag--blue">SEPA</span>
          <span class="tag tag--blue">Apple Pay</span>
        </div>
      </div>
      <div class="payment__card reveal reveal-delay-2">
        <h3>Mollie</h3>
        <p>Popular in Belgium &amp; Netherlands. iDEAL, Bancontact, Klarna and more — all in one integration.</p>
        <div class="payment__methods">
          <span class="tag tag--green">iDEAL</span>
          <span class="tag tag--green">Bancontact</span>
          <span class="tag tag--green">Klarna</span>
        </div>
      </div>
      <div class="payment__card reveal reveal-delay-3">
        <h3>PayPal</h3>
        <p>Used by clients worldwide. Fast, familiar, and trusted — especially for international freelance work.</p>
        <div class="payment__methods">
          <span class="tag">Global</span>
          <span class="tag">Instant transfers</span>
        </div>
      </div>
      <div class="payment__card reveal reveal-delay-4">
        <h3>Bank Transfer</h3>
        <p>Classic bank transfer with your IBAN. Eurobillr automatically generates structured references for each invoice.</p>
        <div class="payment__methods">
          <span class="tag">IBAN</span>
          <span class="tag">Structured ref</span>
          <span class="tag">SEPA</span>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ═══════ BELGIAN ACCOUNTING ═══════ -->
<section class="section section--alt" id="accounting">
  <div class="container">

    <div class="reveal" style="max-width:640px;">
      <p class="section__eyebrow">Belgian Accounting</p>
      <h2 class="section__h2">Full Belgian tax &amp; accounting.<br />No accountant needed.</h2>
      <p class="section__lead">From quarterly VAT returns to your annual income tax declaration — Eurobillr handles every Belgian tax obligation for freelancers and small businesses, pre-filled from your real data.</p>
    </div>

    <!-- BLOCK 1: VAT -->
    <div class="accounting__layout reveal">
      <div class="accounting__text-block">
        <p class="section__eyebrow">VAT Returns</p>
        <h3>Your VAT return, generated automatically every quarter</h3>
        <p>Eurobillr tracks every invoice you send and every expense you log — then calculates your VAT position in real time. At the end of each quarter, your return is pre-filled and ready to review. One click to submit directly to the Belgian tax authority (FOD).</p>
        <ul class="accounting__checklist">
          <li>Belgian VAT rates (21%, 12%, 6%, 0%) applied automatically per invoice line</li>
          <li>VAT due and VAT recoverable calculated in real time on your dashboard</li>
          <li>Quarterly and monthly filing cycles both supported</li>
          <li>Annual VAT client listing (klantenlisting) generated automatically</li>
          <li>EU intra-community sales listing (IC listing) for cross-border B2B work</li>
        </ul>
        <a class="btn btn--primary" href="https://eurobillr.com/auth/register.php">Start filing VAT for free</a>
      </div>
      <div class="accounting__panel reveal reveal-delay-2">
        <div class="accounting__panel-header">
          <span class="accounting__panel-title">Q1 2025 — VAT Overview</span>
          <span class="accounting__panel-badge">READY TO FILE</span>
        </div>
        <div class="accounting__panel-body">
          <div class="acct-row">
            <div class="acct-row__label">
              <div class="acct-row__icon acct-row__icon--blue">📤</div>
              VAT collected (sales)
            </div>
            <div class="acct-row__right">
              <div class="acct-row__value">€2,618.10</div>
              <div class="acct-row__sub">on €12,467 invoiced</div>
            </div>
          </div>
          <div class="acct-row">
            <div class="acct-row__label">
              <div class="acct-row__icon acct-row__icon--green">📥</div>
              VAT recoverable (expenses)
            </div>
            <div class="acct-row__right">
              <div class="acct-row__value">−€110.69</div>
              <div class="acct-row__sub">on €527 expenses</div>
            </div>
          </div>
          <div class="acct-row">
            <div class="acct-row__label">
              <div class="acct-row__icon acct-row__icon--amber">🏛️</div>
              VAT to pay (net)
            </div>
            <div class="acct-row__right">
              <div class="acct-row__value" style="color:var(--blue);">€2,507.41</div>
              <div class="acct-row__status acct-row__status--ready">Pre-filled ✓</div>
            </div>
          </div>
          <div class="acct-provision">
            <div class="acct-provision__label">Amount set aside for VAT</div>
            <div class="acct-provision__bar-wrap">
              <div class="acct-provision__bar" style="width:78%"></div>
            </div>
            <div class="acct-provision__info">
              <span>€1,960 provisioned</span>
              <span class="acct-provision__amount">€547 still to set aside</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- BLOCK 2: Income Tax -->
    <div class="accounting__layout accounting__layout--flip reveal" style="margin-top:72px;">
      <div class="accounting__text-block">
        <p class="section__eyebrow">Income Tax</p>
        <h3>Income tax prep, from your invoices — not a spreadsheet</h3>
        <p>Every invoice and deductible expense you record in Eurobillr feeds directly into your income tax calculation. See your estimated tax due in real time throughout the year — no surprises at declaration time. When it's time to file, your annual declaration is pre-filled and export-ready for your accountant or for direct submission.</p>
        <ul class="accounting__checklist">
          <li>Real-time taxable profit calculation (revenue minus deductible expenses)</li>
          <li>Belgian progressive income tax brackets applied automatically</li>
          <li>Quarterly prepayment (voorafbetaling) reminders — Eurobillr tells you when and how much, you pay the tax authority directly</li>
          <li>Annual income tax report downloadable as PDF or Excel</li>
          <li>Share your data directly with your accountant — no re-encoding</li>
        </ul>
        <a class="btn btn--primary" href="https://eurobillr.com/auth/register.php">See your tax position</a>
      </div>
      <div class="accounting__panel reveal reveal-delay-2">
        <div class="accounting__panel-header">
          <span class="accounting__panel-title">2025 Income Tax — Live View</span>
          <span class="accounting__panel-badge">UPDATED TODAY</span>
        </div>
        <div class="accounting__panel-body">
          <div class="acct-row">
            <div class="acct-row__label">
              <div class="acct-row__icon acct-row__icon--green">💶</div>
              Total revenue (excl. VAT)
            </div>
            <div class="acct-row__right">
              <div class="acct-row__value">€49,870.00</div>
            </div>
          </div>
          <div class="acct-row">
            <div class="acct-row__label">
              <div class="acct-row__icon acct-row__icon--violet">🧾</div>
              Deductible expenses
            </div>
            <div class="acct-row__right">
              <div class="acct-row__value">−€6,324.80</div>
              <div class="acct-row__sub">incl. home office, travel, software</div>
            </div>
          </div>
          <div class="acct-row">
            <div class="acct-row__label">
              <div class="acct-row__icon acct-row__icon--amber">📊</div>
              Taxable profit
            </div>
            <div class="acct-row__right">
              <div class="acct-row__value">€43,545.20</div>
            </div>
          </div>
          <div class="acct-row">
            <div class="acct-row__label">
              <div class="acct-row__icon acct-row__icon--red">🏛️</div>
              Estimated income tax
            </div>
            <div class="acct-row__right">
              <div class="acct-row__value" style="color:#dc2626;">~€14,200</div>
              <div class="acct-row__sub">estimated · Q3 reminder scheduled</div>
            </div>
          </div>
          <div class="acct-row">
            <div class="acct-row__label">
              <div class="acct-row__icon acct-row__icon--blue">📁</div>
              Annual declaration
            </div>
            <div class="acct-row__right">
              <div class="acct-row__status acct-row__status--done">Export ready</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- COMPARISON TABLE -->
    <div class="comparison reveal" style="margin-top:80px;">
      <div class="comparison__header">
        <h3>How Eurobillr compares</h3>
        <p>Belgian accounting &amp; invoicing features across the main platforms</p>
      </div>
      <div class="comparison__table">
        <!-- Headers -->
        <div class="comparison__col-head">Feature</div>
        <div class="comparison__col-head comparison__col-head--highlight">Eurobillr</div>
        <div class="comparison__col-head">Accountable</div>
        <div class="comparison__col-head">Billit</div>
        <!-- Row 1 -->
        <div class="comparison__feature">PEPPOL e-invoicing (B2B &amp; B2G)</div>
        <div class="comparison__cell comparison__cell--highlight"><span class="comparison__check">✓</span></div>
        <div class="comparison__cell"><span class="comparison__check">✓</span></div>
        <div class="comparison__cell"><span class="comparison__check">✓</span></div>
        <!-- Row 2 -->
        <div class="comparison__feature">Automatic expense tracking</div>
        <div class="comparison__cell comparison__cell--highlight"><span class="comparison__check">✓</span></div>
        <div class="comparison__cell"><span class="comparison__check">✓</span></div>
        <div class="comparison__cell"><span class="comparison__check">✓</span></div>
        <!-- Row 3 -->
        <div class="comparison__feature">Quarterly VAT return (pre-filled)</div>
        <div class="comparison__cell comparison__cell--highlight"><span class="comparison__check">✓</span></div>
        <div class="comparison__cell"><span class="comparison__check">✓</span></div>
        <div class="comparison__cell"><span class="comparison__partial">Paid only</span></div>
        <!-- Row 4 -->
        <div class="comparison__feature">Annual income tax report</div>
        <div class="comparison__cell comparison__cell--highlight"><span class="comparison__check">✓</span></div>
        <div class="comparison__cell"><span class="comparison__check">✓</span></div>
        <div class="comparison__cell"><span class="comparison__cross">✗</span></div>
        <!-- Row 5 -->
        <div class="comparison__feature">Belgian client listing (klantenlisting)</div>
        <div class="comparison__cell comparison__cell--highlight"><span class="comparison__check">✓</span></div>
        <div class="comparison__cell"><span class="comparison__check">✓</span></div>
        <div class="comparison__cell"><span class="comparison__cross">✗</span></div>
        <!-- Row 6 -->
        <div class="comparison__feature">Payment collection (Stripe / Mollie / PayPal)</div>
        <div class="comparison__cell comparison__cell--highlight"><span class="comparison__check">✓</span></div>
        <div class="comparison__cell"><span class="comparison__cross">✗</span></div>
        <div class="comparison__cell"><span class="comparison__cross">✗</span></div>
        <!-- Row 7 -->
        <div class="comparison__feature">Auto payment reminders</div>
        <div class="comparison__cell comparison__cell--highlight"><span class="comparison__check">✓</span></div>
        <div class="comparison__cell"><span class="comparison__check">✓</span></div>
        <div class="comparison__cell"><span class="comparison__check">✓</span></div>
        <!-- Row 8 -->
        <div class="comparison__feature">QR code invoices</div>
        <div class="comparison__cell comparison__cell--highlight"><span class="comparison__check">✓</span></div>
        <div class="comparison__cell"><span class="comparison__cross">✗</span></div>
        <div class="comparison__cell"><span class="comparison__cross">✗</span></div>
        <!-- Row 9 -->
        <div class="comparison__feature">Free plan available</div>
        <div class="comparison__cell comparison__cell--highlight"><span class="comparison__check">✓</span></div>
        <div class="comparison__cell"><span class="comparison__check">✓</span></div>
        <div class="comparison__cell"><span class="comparison__check">✓</span></div>
        <!-- Row 10 -->
        <div class="comparison__feature">EN / NL / FR interface</div>
        <div class="comparison__cell comparison__cell--highlight"><span class="comparison__check">✓</span></div>
        <div class="comparison__cell"><span class="comparison__partial">EN + NL</span></div>
        <div class="comparison__cell"><span class="comparison__check">✓</span></div>
      </div>
    </div>

  </div>
</section>

<!-- ═══════ STATS ═══════ -->
<div class="stats-bar">
  <div class="container">
    <div class="stats-bar__grid">
      <div class="stat-item reveal">
        <div class="stat-item__value">90%</div>
        <div class="stat-item__label">Faster expense processing</div>
      </div>
      <div class="stat-item reveal reveal-delay-1">
        <div class="stat-item__value">70%</div>
        <div class="stat-item__label">Faster payment collection</div>
      </div>
      <div class="stat-item reveal reveal-delay-2">
        <div class="stat-item__value">38+</div>
        <div class="stat-item__label">PEPPOL countries supported</div>
      </div>
      <div class="stat-item reveal reveal-delay-3">
        <div class="stat-item__value">10h+</div>
        <div class="stat-item__label">Saved per month on average</div>
      </div>
    </div>
  </div>
</div>

<!-- ═══════ TESTIMONIALS ═══════ -->
<section class="section">
  <div class="container">
    <div class="reveal">
      <p class="section__eyebrow">Customer Stories</p>
      <h2 class="section__h2">Trusted across Europe</h2>
    </div>
    <div class="testimonials__grid">
      <div class="testimonial reveal reveal-delay-1">
        <div class="testimonial__stars">★★★★★</div>
        <p class="testimonial__body">As a freelance designer, I used to spend a Sunday each month chasing invoices. Eurobillr's automatic reminders changed that completely — I haven't had to follow up on a late payment in months.</p>
        <div class="testimonial__author">
          <div class="testimonial__avatar">SV</div>
          <div>
            <div class="testimonial__name">Sofie V.</div>
            <div class="testimonial__role">Freelance Designer · Belgium</div>
          </div>
        </div>
      </div>
      <div class="testimonial reveal reveal-delay-2">
        <div class="testimonial__stars">★★★★★</div>
        <p class="testimonial__body">PEPPOL registration was done in under 2 minutes. Now all our supplier invoices arrive straight into our expense tracker. The time savings are real — we estimate 12 hours a month saved.</p>
        <div class="testimonial__author">
          <div class="testimonial__avatar">TK</div>
          <div>
            <div class="testimonial__name">Thomas K.</div>
            <div class="testimonial__role">Data Scientist Consultant · Netherlands</div>
          </div>
        </div>
      </div>
      <div class="testimonial reveal reveal-delay-3">
        <div class="testimonial__stars">★★★★★</div>
        <p class="testimonial__body">I run a small consultancy and Eurobillr handles everything — invoices, expenses, VAT reports. The tax submission feature alone is worth it. My accountant actually thanked me.</p>
        <div class="testimonial__author">
          <div class="testimonial__avatar">MB</div>
          <div>
            <div class="testimonial__name">Marie B.</div>
            <div class="testimonial__role">Business Consultant · France</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ═══════════════════════════════════════════════════════════ PRICING ══ -->
<section id="pricing" style="padding:5rem 0 1.5rem;background:#f8faff;">
<div class="container">
 
  <!-- ── Section header ──────────────────────────────────────────────────── -->
  <div class="text-center mb-5">
    <span class="eb-section-badge">Pricing</span>
    <h2 class="eb-section-title mt-3">
      Transparent pricing,<br><em>no surprises</em>
    </h2>
    <p class="eb-section-sub mx-auto">
      Start free, scale when you need to. Every paid plan includes PEPPOL,
      payment collection, expense tracking & management, and full Belgian tax compliance.
    </p>
  </div>
 
  <!-- ── Cards row ───────────────────────────────────────────────────────── -->
  <div class="eb-pricing-row">
 
    <!-- ① FREE ──────────────────────────────────────────────────────────── -->
    <div class="eb-pc">
      <div class="eb-pc-head">
        <p class="eb-pc-name">Free</p>
        <div class="eb-pc-price-wrap">
          <span class="eb-pc-eur">€</span>
          <span class="eb-pc-amt">0</span>
          <span class="eb-pc-per">/mo</span>
        </div>
        <p class="eb-pc-tagline">Try the platform at no cost</p>
      </div>
      <ul class="eb-pc-feats">
        <li class="yes"><i class="fas fa-check-circle"></i><span><strong>5 e-invoices</strong> (lifetime)</span></li>
        <li class="yes"><i class="fas fa-check-circle"></i><span>Stripe, Mollie &amp; PayPal</span></li>
        <li class="yes"><i class="fas fa-check-circle"></i><span>QR code invoices</span></li>
        <li class="yes"><i class="fas fa-check-circle"></i><span>Expense tracking (manual)</span></li>
        <li class="yes"><i class="fas fa-check-circle"></i><span>Multi-currency</span></li>
        <li class="yes"><i class="fas fa-check-circle"></i><span>EN / NL / FR interface</span></li>
        <li class="no"> <i class="fas fa-minus"></i><span>PEPPOL e-invoicing</span></li>
        <li class="no"> <i class="fas fa-minus"></i><span>Unlimited email invoices</span></li>
        <li class="no"> <i class="fas fa-minus"></i><span>VAT returns</span></li>
        <li class="no"> <i class="fas fa-minus"></i><span>Automatic reminders</span></li>
      </ul>
      <div class="eb-pc-foot">
        <a href="/auth/register.php" class="eb-btn-outline w-100">Start for free</a>
        <p class="eb-pc-note">No credit card required</p>
      </div>
    </div>
 
    <!-- ② STARTER ───────────────────────────────────────────────────────── -->
    <div class="eb-pc">
      <div class="eb-pc-badge badge-new">New</div>
      <div class="eb-pc-head">
        <p class="eb-pc-name">Starter</p>
        <div class="eb-pc-price-wrap">
          <span class="eb-pc-eur">€</span>
          <span class="eb-pc-amt">4.99</span>
          <span class="eb-pc-per">/mo</span>
        </div>
        <p class="eb-pc-tagline">First steps with PEPPOL</p>
      </div>
      <ul class="eb-pc-feats">
        <li class="yes"><i class="fas fa-check-circle"></i><span><strong>10 e-invoices / month</strong></span></li>
        <li class="yes"><i class="fas fa-check-circle"></i><span>Unlimited email invoices</span></li>
        <li class="yes"><i class="fas fa-check-circle"></i><span>PEPPOL <span class="eb-tag">Limited</span></span></li>
        <li class="yes"><i class="fas fa-check-circle"></i><span>Stripe, Mollie &amp; PayPal</span></li>
        <li class="yes"><i class="fas fa-check-circle"></i><span>QR code invoices</span></li>
        <li class="yes"><i class="fas fa-check-circle"></i><span>Expense tracking</span></li>
        <li class="yes"><i class="fas fa-check-circle"></i><span>Quarterly VAT returns</span></li>
        <li class="yes"><i class="fas fa-check-circle"></i><span>Multi-currency &amp; analytics</span></li>
        <li class="yes"><i class="fas fa-check-circle"></i><span>1 user</span></li>
        <li class="no"> <i class="fas fa-minus"></i><span>Automatic reminders</span></li>
      </ul>
      <div class="eb-pc-foot">
        <a href="/auth/register.php?plan=starter" class="eb-btn-outline w-100">Get Starter</a>
        <p class="eb-pc-note">14-day free trial</p>
      </div>
    </div>
 
    <!-- ③ FREELANCER — FEATURED ─────────────────────────────────────────── -->
    <div class="eb-pc eb-pc--featured">
      <div class="eb-pc-badge badge-popular">Most Popular</div>
      <div class="eb-pc-head">
        <p class="eb-pc-name">Freelancer</p>
        <div class="eb-pc-price-wrap">
          <span class="eb-pc-eur">€</span>
          <span class="eb-pc-amt">9.90</span>
          <span class="eb-pc-per">/mo</span>
        </div>
        <p class="eb-pc-tagline">Ideal for sole proprietors</p>
      </div>
      <ul class="eb-pc-feats">
        <li class="yes"><i class="fas fa-check-circle"></i><span><strong>20 e-invoices / month</strong></span></li>
        <li class="yes"><i class="fas fa-check-circle"></i><span>Unlimited email invoices</span></li>
        <li class="yes"><i class="fas fa-check-circle"></i><span>PEPPOL <span class="eb-tag">Limited</span></span></li>
        <li class="yes"><i class="fas fa-check-circle"></i><span>Stripe, Mollie &amp; PayPal</span></li>
        <li class="yes"><i class="fas fa-check-circle"></i><span>QR code invoices</span></li>
        <li class="yes"><i class="fas fa-check-circle"></i><span>Expense tracking</span></li>
        <li class="yes"><i class="fas fa-check-circle"></i><span>Quarterly VAT returns</span></li>
        <li class="yes"><i class="fas fa-check-circle"></i><span>Multi-currency &amp; analytics</span></li>
        <li class="yes"><i class="fas fa-check-circle"></i><span><strong>Automatic reminders</strong></span></li>
        <li class="yes"><i class="fas fa-check-circle"></i><span>1 user</span></li>
      </ul>
      <div class="eb-pc-foot">
        <a href="/auth/register.php?plan=freelancer" class="eb-btn-primary w-100">Get Freelancer</a>
        <p class="eb-pc-note">14-day free trial · Cancel anytime</p>
      </div>
    </div>
 
    <!-- ④ SMALL COMPANY ─────────────────────────────────────────────────── -->
    <div class="eb-pc">
      <div class="eb-pc-head">
        <p class="eb-pc-name">Small Company</p>
        <div class="eb-pc-price-wrap">
          <span class="eb-pc-eur">€</span>
          <span class="eb-pc-amt">39.90</span>
          <span class="eb-pc-per">/mo</span>
        </div>
        <p class="eb-pc-tagline">For growing SMEs</p>
      </div>
      <ul class="eb-pc-feats">
        <li class="yes"><i class="fas fa-check-circle"></i><span><strong>200 e-invoices / month</strong></span></li>
        <li class="yes"><i class="fas fa-check-circle"></i><span>Unlimited email invoices</span></li>
        <li class="yes"><i class="fas fa-check-circle"></i><span>Full PEPPOL B2B &amp; B2G</span></li>
        <li class="yes"><i class="fas fa-check-circle"></i><span>Stripe, Mollie &amp; PayPal</span></li>
        <li class="yes"><i class="fas fa-check-circle"></i><span>QR code invoices</span></li>
        <li class="yes"><i class="fas fa-check-circle"></i><span>Expense tracking &amp; OCR</span></li>
        <li class="yes"><i class="fas fa-check-circle"></i><span>Quarterly VAT returns</span></li>
        <li class="yes"><i class="fas fa-check-circle"></i><span>Multi-currency &amp; analytics</span></li>
        <li class="yes"><i class="fas fa-check-circle"></i><span>Automatic reminders</span></li>
        <li class="yes"><i class="fas fa-check-circle"></i><span><strong>Up to 3 users</strong></span></li>
      </ul>
      <div class="eb-pc-foot">
        <a href="/auth/register.php?plan=small_company" class="eb-btn-outline w-100">Get Small Company</a>
        <p class="eb-pc-note">14-day free trial</p>
      </div>
    </div>
 
    <!-- ⑤ GROWING COMPANY ───────────────────────────────────────────────── -->
    <div class="eb-pc">
      <div class="eb-pc-head">
        <p class="eb-pc-name">Growing Company</p>
        <div class="eb-pc-price-wrap">
          <span class="eb-pc-eur">€</span>
          <span class="eb-pc-amt">45.90</span>
          <span class="eb-pc-per">/mo</span>
        </div>
        <p class="eb-pc-tagline">Advanced PEPPOL &amp; teams</p>
      </div>
      <ul class="eb-pc-feats">
        <li class="yes"><i class="fas fa-infinity"></i><span><strong>Unlimited</strong> e-invoices</span></li>
        <li class="yes"><i class="fas fa-check-circle"></i><span>Unlimited email invoices</span></li>
        <li class="yes"><i class="fas fa-check-circle"></i><span>Advanced PEPPOL</span></li>
        <li class="yes"><i class="fas fa-check-circle"></i><span>Stripe, Mollie &amp; PayPal</span></li>
        <li class="yes"><i class="fas fa-check-circle"></i><span>QR code invoices</span></li>
        <li class="yes"><i class="fas fa-check-circle"></i><span>Expense tracking &amp; OCR</span></li>
        <li class="yes"><i class="fas fa-check-circle"></i><span>Quarterly VAT returns</span></li>
        <li class="yes"><i class="fas fa-check-circle"></i><span>Multi-currency &amp; analytics</span></li>
        <li class="yes"><i class="fas fa-check-circle"></i><span>Automatic reminders</span></li>
        <li class="yes"><i class="fas fa-check-circle"></i><span><strong>Up to 10 users</strong> · Dedicated support</span></li>
      </ul>
      <div class="eb-pc-foot">
        <a href="/auth/register.php?plan=growing_company" class="eb-btn-outline w-100">Get Growing Company</a>
        <p class="eb-pc-note">14-day free trial</p>
      </div>
    </div>
 
  </div><!-- /eb-pricing-row -->
 
  <!-- ── Comparison table (collapsible) ──────────────────────────────── -->
  <div class="eb-comp-wrap mt-5">
    <button class="eb-comp-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#pricingCompare" aria-expanded="false">
      <i class="fas fa-table me-2"></i>Compare all features
      <i class="fas fa-chevron-down ms-auto eb-chev"></i>
    </button>
    <div class="collapse" id="pricingCompare">
      <div class="table-responsive mt-3">
        <table class="eb-comp-table">
          <thead>
            <tr>
              <th style="text-align:left;min-width:170px;">Feature</th>
              <th>Free</th>
              <th>Starter</th>
              <th class="col-featured">Freelancer</th>
              <th>Small Co.</th>
              <th>Growing Co.</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>Price / month</td>
              <td>€0</td><td>€4.99</td>
              <td class="col-featured"><strong>€9.90</strong></td>
              <td>€39.90</td><td>€45.90</td>
            </tr>
            <tr>
              <td>E-invoices</td>
              <td>5 <span class="eb-tiny">(lifetime)</span></td>
              <td>10 / mo</td>
              <td class="col-featured">20 / mo</td>
              <td>200 / mo</td>
              <td>Unlimited</td>
            </tr>
            <tr>
              <td>Email invoices</td>
              <td><i class="fas fa-times eb-no"></i></td>
              <td><i class="fas fa-check eb-yes"></i></td>
              <td class="col-featured"><i class="fas fa-check eb-yes"></i></td>
              <td><i class="fas fa-check eb-yes"></i></td>
              <td><i class="fas fa-check eb-yes"></i></td>
            </tr>
            <tr>
              <td>PEPPOL e-invoicing</td>
              <td><i class="fas fa-times eb-no"></i></td>
              <td>Limited</td>
              <td class="col-featured">Limited</td>
              <td>Full B2B &amp; B2G</td>
              <td>Advanced</td>
            </tr>
            <tr>
              <td>Payment collection</td>
              <td><i class="fas fa-check eb-yes"></i></td>
              <td><i class="fas fa-check eb-yes"></i></td>
              <td class="col-featured"><i class="fas fa-check eb-yes"></i></td>
              <td><i class="fas fa-check eb-yes"></i></td>
              <td><i class="fas fa-check eb-yes"></i></td>
            </tr>
            <tr>
              <td>Expense tracking</td>
              <td>Manual only</td>
              <td><i class="fas fa-check eb-yes"></i></td>
              <td class="col-featured"><i class="fas fa-check eb-yes"></i></td>
              <td>+ OCR scan</td>
              <td>+ OCR scan</td>
            </tr>
            <tr>
              <td>Quarterly VAT returns</td>
              <td><i class="fas fa-times eb-no"></i></td>
              <td><i class="fas fa-check eb-yes"></i></td>
              <td class="col-featured"><i class="fas fa-check eb-yes"></i></td>
              <td><i class="fas fa-check eb-yes"></i></td>
              <td><i class="fas fa-check eb-yes"></i></td>
            </tr>
            <tr>
              <td>Auto payment reminders</td>
              <td><i class="fas fa-times eb-no"></i></td>
              <td><i class="fas fa-times eb-no"></i></td>
              <td class="col-featured"><i class="fas fa-check eb-yes"></i></td>
              <td><i class="fas fa-check eb-yes"></i></td>
              <td><i class="fas fa-check eb-yes"></i></td>
            </tr>
            <tr>
              <td>Multi-currency</td>
              <td><i class="fas fa-check eb-yes"></i></td>
              <td><i class="fas fa-check eb-yes"></i></td>
              <td class="col-featured"><i class="fas fa-check eb-yes"></i></td>
              <td><i class="fas fa-check eb-yes"></i></td>
              <td><i class="fas fa-check eb-yes"></i></td>
            </tr>
            <tr>
              <td>Analytics dashboard</td>
              <td><i class="fas fa-check eb-yes"></i></td>
              <td><i class="fas fa-check eb-yes"></i></td>
              <td class="col-featured"><i class="fas fa-check eb-yes"></i></td>
              <td><i class="fas fa-check eb-yes"></i></td>
              <td><i class="fas fa-check eb-yes"></i></td>
            </tr>
            <tr>
              <td>Users</td>
              <td>1</td><td>1</td>
              <td class="col-featured">1</td>
              <td>3</td>
              <td>10</td>
            </tr>
            <tr>
              <td>Support</td>
              <td>Basic</td><td>Basic</td>
              <td class="col-featured">Basic</td>
              <td>Standard</td>
              <td>Dedicated</td>
            </tr>
          </tbody>
          <tfoot>
            <tr>
              <td></td>
              <td><a href="/auth/register.php" class="eb-btn-sm-outline">Free</a></td>
              <td><a href="/auth/register.php?plan=starter" class="eb-btn-sm-outline">Starter</a></td>
              <td class="col-featured"><a href="/auth/register.php?plan=freelancer" class="eb-btn-sm-primary">Freelancer</a></td>
              <td><a href="/auth/register.php?plan=small_company" class="eb-btn-sm-outline">Small Co.</a></td>
              <td><a href="/auth/register.php?plan=growing_company" class="eb-btn-sm-outline">Growing Co.</a></td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </div>
 
  <!-- ── Trust strip ───────────────────────────────────────────────────── -->
  <div class="eb-trust-row mt-5">
    <span class="eb-trust-badge"><i class="fas fa-lock me-1"></i>SSL secured</span>
    <span class="eb-trust-badge"><i class="fas fa-flag me-1"></i>Belgian VAT compliant</span>
    <span class="eb-trust-badge"><i class="fas fa-globe me-1"></i>PEPPOL certified</span>
    <span class="eb-trust-badge"><i class="fas fa-credit-card me-1"></i>No card for free plan</span>
    <span class="eb-trust-badge"><i class="fas fa-times me-1"></i>Cancel anytime</span>
    <span class="eb-trust-badge"><i class="fas fa-undo me-1"></i>14-day trial on paid plans</span>
  </div>
 
</div>
</section>

<!-- ═══════ FAQ ═══════ -->
<section class="section section--alt" id="faq" style="padding-top:30px;">
  <div class="container">
    <div class="faq__grid">
      <div class="faq__sidebar reveal">
        <p class="section__eyebrow">FAQ</p>
        <h3>Common questions, clear answers</h3>
        <p>Everything you need to know about PEPPOL, expenses management, payments, and tax reports. Still have questions?</p>
        <a class="" href="https://eurobillr.com/faq.php">
          View all FAQ
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </a>
      </div>
      <div class="faq__items reveal reveal-delay-1">

        <div class="faq__item">
          <button class="faq__question" onclick="toggleFaq(this)">
            What is PEPPOL and why does it matter for Belgium?
            <span class="faq__chevron">
              <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M19 9l-7 7-7-7"/></svg>
            </span>
          </button>
          <div class="faq__answer">
            <div class="faq__answer-inner">
              PEPPOL is the EU-standardised network for exchanging electronic invoices between businesses and governments. Since <strong>January 1, 2026</strong>, B2B e-invoicing via PEPPOL is mandatory for all VAT-registered companies in Belgium. Eurobillr is PEPPOL-certified — registration takes under 2 minutes and all compliance is handled automatically.
            </div>
          </div>
        </div>

        <div class="faq__item">
          <button class="faq__question" onclick="toggleFaq(this)">
            How does automatic expense tracking work?
            <span class="faq__chevron">
              <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M19 9l-7 7-7-7"/></svg>
            </span>
          </button>
          <div class="faq__answer">
            <div class="faq__answer-inner">
              Three ways: <strong>automatically</strong> from incoming PEPPOL invoices (zero manual entry), by <strong>scanning receipts</strong> with OCR that reads vendor, amount and date instantly, or by <strong>manual entry</strong> in seconds. All three feed into one unified expense overview.
            </div>
          </div>
        </div>

        <div class="faq__item">
          <button class="faq__question" onclick="toggleFaq(this)">
            Which payment providers does Eurobillr support?
            <span class="faq__chevron">
              <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M19 9l-7 7-7-7"/></svg>
            </span>
          </button>
          <div class="faq__answer">
            <div class="faq__answer-inner">
              <strong>Stripe</strong> (cards, SEPA, Apple Pay), <strong>Mollie</strong> (iDEAL, Bancontact, Klarna — popular in Belgium &amp; Netherlands), <strong>PayPal</strong> (global), and <strong>bank transfer</strong> via IBAN with auto-generated structured payment references. All payments go directly to your account — Eurobillr never holds your funds.
            </div>
          </div>
        </div>

        <div class="faq__item">
          <button class="faq__question" onclick="toggleFaq(this)">
            Can I submit VAT and tax reports directly?
            <span class="faq__chevron">
              <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M19 9l-7 7-7-7"/></svg>
            </span>
          </button>
          <div class="faq__answer">
            <div class="faq__answer-inner">
              Yes. Eurobillr <strong>pre-fills your VAT and income tax declarations</strong> automatically from your real invoices and expenses. You review the figures, confirm, and submit. You can also download full reports in PDF or Excel — ready for your accountant.
            </div>
          </div>
        </div>

        <div class="faq__item">
          <button class="faq__question" onclick="toggleFaq(this)">
            Is there a free plan? Do I need a credit card?
            <span class="faq__chevron">
              <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M19 9l-7 7-7-7"/></svg>
            </span>
          </button>
          <div class="faq__answer">
            <div class="faq__answer-inner">
              Yes — no credit card required to start. PEPPOL registration is included in all plans at no extra cost. Create your free account at <strong>eurobillr.com/auth/register.php</strong> and be set up in minutes.
            </div>
          </div>
        </div>

        <div class="faq__item">
          <button class="faq__question" onclick="toggleFaq(this)">
            How do automatic payment reminders work?
            <span class="faq__chevron">
              <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M19 9l-7 7-7-7"/></svg>
            </span>
          </button>
          <div class="faq__answer">
            <div class="faq__answer-inner">
              Set a due date (14, 30, or 60 days) when sending an invoice. Eurobillr sends a <strong>polite pre-due reminder</strong> 3–7 days before, a <strong>professional overdue follow-up</strong> with payment link if unpaid, then marks the invoice paid automatically when payment clears — notifying you instantly.
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</section>

<!-- ═══════ CTA FINAL ═══════ -->
<section class="cta-final">
  <div class="container--narrow">
    <h2 class="reveal">Ready to take control<br />of your Cashflow?</h2>
    <p class="reveal reveal-delay-1">Join thousands of freelancers and small businesses across Europe using Eurobillr every day.</p>
    <div class="cta-final__actions reveal reveal-delay-2">
      <a class="btn btn--white btn--lg" href="https://eurobillr.com/auth/register.php">Start for Free</a>
      <a class="btn btn--outline-white btn--lg" href="https://youtu.be/nXIkDUdiNRs">Watch the Demo</a>
    </div>
    <div class="cta-final__perks reveal reveal-delay-3">
      <span class="cta-final__perk">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
        PEPPOL registration included
      </span>
      <span class="cta-final__perk">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
        Automatic expense tracking
      </span>
      <span class="cta-final__perk">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
        Auto payment reminders
      </span>
      <span class="cta-final__perk">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
        Stripe · Mollie · PayPal · Bank
      </span>
    </div>
  </div>
</section>


<!-- ═══════ FOOTER ═══════ -->
<footer class="footer">
  <div class="container">
    <div class="footer__grid">
      <div class="footer__brand">
        <a class="footer__logo" href="https://eurobillr.com/">
          <img src="https://eurobillr.com/images/eurobillr.com.jpg" alt="Eurobillr logo" />
          Eurobillr
        </a>
        <p>Complete business platform for freelancers and small businesses — PEPPOL e-invoicing, expense tracking & management, payment collection, and tax reporting in one place.</p>
        <p style="margin-top:10px;font-size:13px;color:#94a3b8;">
          <a href="mailto:info@eurobillr.com" style="color:#94a3b8;">info@eurobillr.com</a><br />
          Muelesteedsesteenweg 216, 9000 Gent
        </p>
      </div>
      <div class="footer__col">
        <h4>Features</h4>
        <ul>
          <li><a href="#peppol">PEPPOL e-Invoicing</a></li>
          <li><a href="#expenses">Expense Tracking & Management</a></li>
          <li><a href="#features">Payment Reminders</a></li>
          <li><a href="#features">QR Code Payments</a></li>
          <li><a href="#features">Income Reports</a></li>
          <li><a href="#features">Tax Submission</a></li>
        </ul>
      </div>
      <div class="footer__col">
        <h4>Account</h4>
        <ul>
          <li><a href="https://eurobillr.com/auth/login.php">Sign In</a></li>
          <li><a href="https://eurobillr.com/auth/register.php">Register Free</a></li>
          <li><a href="https://eurobillr.com/auth/register.php">PEPPOL Registration</a></li>
          <li><a href="https://eurobillr.com/faq">FAQ</a></li>
          <li><a href="https://eurobillr.com/blog">Blog</a></li>
     
        </ul>
      </div>
      <div class="footer__col">
        <h4>Legal</h4>
        <ul>
          <li><a href="https://eurobillr.com/privacy-policy.php">Privacy Policy</a></li>
          <li><a href="https://eurobillr.com/terms-of-service.php">Terms of Service</a></li>
          <li><a href="https://eurobillr.com/cookies.php">Cookies</a></li>
        </ul>
      </div>
    </div>
    <div class="footer__bottom">
      <span class="footer__bottom-text">© 2025 Eurobillr. All rights reserved.</span>
      <div class="footer__langs">
        <a href="https://eurobillr.com/nl/">NL</a>
        <a href="https://eurobillr.com/fr/">FR</a>
        
      </div>
    </div>
  </div>
</footer>

<script>
  /* ── FAQ accordion ── */
  function toggleFaq(btn) {
    const item = btn.closest('.faq__item');
    const isOpen = item.classList.contains('open');
    document.querySelectorAll('.faq__item.open').forEach(i => i.classList.remove('open'));
    if (!isOpen) item.classList.add('open');
  }

  /* ── Scroll-reveal ── */
  // Use a single IntersectionObserver — do NOT use scroll event listeners
  // which can block the main thread and cause iOS scroll jank.
  const revealObserver = new IntersectionObserver((entries) => {
    entries.forEach(e => {
      if (e.isIntersecting) {
        e.target.classList.add('visible');
        revealObserver.unobserve(e.target);
      }
    });
  }, {
    // Lower threshold so elements reveal before you reach them fully —
    // prevents "content pops in after scroll stops" on mobile
    threshold: 0.05,
    rootMargin: '0px 0px -20px 0px'
  });

  document.querySelectorAll('.reveal').forEach(el => revealObserver.observe(el));

  /* Pull-to-refresh handled via CSS only (no JS touch blocking)
     overscroll-behavior is set per-element in CSS below — 
     avoids passive:false which causes ANR in Android WebView */
</script>

<?php
if (!defined('BASE_PATH')) {
    define('BASE_PATH', '/home/u544955467/domains/eurobillr.com/public_html');
}
if (!defined('BLOG_FUNCTIONS_LOADED')) {
    require_once BASE_PATH . '/blog/includes/blog_functions.php';
}
$recent_posts = blog_get_posts('en', '', 3, 0);
?>
 
<?php if (!empty($recent_posts)): ?>
<section style="background:#f8fafc; padding:60px 0; margin-top:60px;">
  <div style="max-width:1100px; margin:0 auto; padding:0 24px;">
 
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:32px;">
      <div>
        <p style="font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:.08em; color:#2563eb; margin-bottom:6px;">From the Blog</p>
        <h2 style="font-size:28px; font-weight:700; color:#0f172a; margin:0;">PEPPOL, VAT & Invoicing Guides</h2>
      </div>
      <a href="/blog/" style="font-size:14px; font-weight:600; color:#2563eb; text-decoration:none;">
        View all articles →
      </a>
    </div>
 
    <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(300px,1fr)); gap:24px;">
      <?php foreach ($recent_posts as $bp): ?>
      <a href="/blog/<?= htmlspecialchars($bp['slug']) ?>"
         style="background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:24px;
                text-decoration:none; color:inherit; display:block;
                transition:box-shadow .2s, transform .2s;"
         onmouseover="this.style.boxShadow='0 4px 20px rgba(0,0,0,.08)'; this.style.transform='translateY(-2px)'"
         onmouseout="this.style.boxShadow='none'; this.style.transform='none'">
        <span style="font-size:11px; font-weight:700; text-transform:uppercase;
                     letter-spacing:.06em; color:#2563eb;">
          <?= htmlspecialchars($bp['category']) ?>
        </span>
        <h3 style="font-size:16px; font-weight:600; color:#0f172a; margin:10px 0 8px; line-height:1.4;">
          <?= htmlspecialchars($bp['title']) ?>
        </h3>
        <p style="font-size:13px; color:#64748b; line-height:1.6; margin:0 0 16px;">
          <?= htmlspecialchars(mb_substr(strip_tags($bp['meta_desc']), 0, 110)) ?>…
        </p>
        <span style="font-size:13px; font-weight:600; color:#2563eb;">Read article →</span>
      </a>
      <?php endforeach; ?>
    </div>
 
  </div>
</section>
<?php endif; ?>


<?php
// ── GDPR Cookie Consent Banner ────────────────────────────────────────────────
// Loaded here (just before </body>) so all page scripts are already available.
// cookie-banner.php self-checks for existing consent — renders nothing if
// the user already has a valid eb_cookie_consent cookie.
// The AJAX save endpoint lives at /includes/cookies.php.
require_once __DIR__ . '/includes/cookie-banner.php';
?>

</body>
</html>