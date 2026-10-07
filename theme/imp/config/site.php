<?php
/**
 * Site configuration — the single place for URLs, page slugs, categories, social profiles,
 * menus and page routes. Everything here can be overridden with the `imp_config` filter
 * (e.g. from a small plugin) instead of editing the theme.
 *
 * Colours, spacing and breakpoints are NOT here: they live as CSS custom properties in
 * assets/css/tokens.css, the one source of truth for design values.
 *
 * @package IMP
 */

defined( 'ABSPATH' ) || exit;

return array(

	// WordPress page slugs the theme links to (resolved to permalinks at runtime).
	'pages'      => array(
		'donate'          => 'donate',
		'about'           => 'about-us',
		'constitution'    => 'party-constitution',
		'affidavit'       => 'affidavit',
		'motto'           => 'partys-motto',
		'news'            => 'news',
		'membership_form' => 'membership-form',
		'membership_fee'  => 'membership-fee', // its content is shown in the "درباره هزینه هموندی" popup
	),

	// External links.
	'links'      => array(
		'media'               => 'https://www.youtube.com/@IranianMonarchyParty',
		'donorbox_membership' => 'https://donorbox.org/membership-930550',
		'donorbox_donation'   => 'https://donorbox.org/donation-930545',
		'contact_email'       => 'padeshahiparty@gmail.com',
	),

	// Footer / contact page icons, in display order (Figma: Instagram … Telegram, left to right).
	'socials'    => array(
		'instagram' => array( 'Instagram', 'https://www.instagram.com/iranianmonarchyparty' ),
		'facebook'  => array( 'Facebook', 'https://www.facebook.com/people/Iranian-Monarchy-Party/61582238103605/' ),
		'tiktok'    => array( 'TikTok', 'https://www.tiktok.com/@imp2584' ),
		'youtube'   => array( 'YouTube', 'https://www.youtube.com/@IranianMonarchyParty' ),
		'x'         => array( 'X', 'https://x.com/IMP2584' ),
		'telegram'  => array( 'Telegram', 'https://t.me/imp2584' ),
	),

	'categories' => array(
		'news'       => 'news-party', // "اخبار حزب" on the live site
		'statements' => 'statements',
	),

	// Statement titles start with one of these; the card shows the rest in large type.
	'statement_prefixes' => array(
		'بیانیه پارمان پادشاهی ایرانیان',
		'بیانیه حزب پادشاهی ایرانیان',
	),

	// Membership enquiries from the home/contact form go here (empty = the site admin email).
	'contact_recipient' => '',

	// Menu locations (Appearance → Menus). Both fall back to Neve's "primary" menu.
	'menus'      => array(
		'imp-mobile'  => 'Mobile menu (IMP)',
		'imp-desktop' => 'Desktop menu (IMP)',
	),

	// Pages with their own Figma design: route key → page template in templates/pages/.
	// Keys: "front", a page slug (decoded), or "category:<slug>".
	'routes'     => array(
		'front'               => 'home',
		'donate'              => 'donate',
		'bcd31-contact-us'    => 'contact', // live slug
		'contact-us'          => 'contact',
		'category:statements' => 'statements',
		'news'                => 'news',
		'نشریه-ایرانگرا'      => 'magazine', // live slug (Persian)
		'irangara'            => 'magazine',
		'partys-motto'        => 'document',
		'party-constitution'  => 'document',
		'affidavit'           => 'document',
	),

	'fonts_url'  => 'https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;500;700;800;900&family=Roboto+Slab:wght@400;700&family=Roboto:wght@400;700&display=swap',
);
