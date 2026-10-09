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

	// The Fluent Forms membership form (live ID 3, "فرم هموندی") and its weekly digest email.
	// Field names are the form's own, so stored entries and email conditions keep working.
	'membership' => array(
		'form_id'      => 3,
		'fee_field'    => 'membership_fee_commitment', // answer shown as a coloured badge (بله / خیر)
		'digest'       => array(
			'recipient' => 'office@iranianmonarchy.info',
			'weekday'   => 6, // ISO-8601: 6 = Saturday
			'days_back' => 7,
		),
	),

	// WhatsApp messages on a new membership entry (WhatsApp Business Cloud API, Meta).
	// Secrets and phone numbers are NOT here: they go in wp-config.php —
	//   IMP_WHATSAPP_TOKEN, IMP_WHATSAPP_PHONE_ID, IMP_WHATSAPP_OFFICE ("+49…,+44…").
	// Without a token the module runs in test mode: messages are only listed in
	// Tools → IMP WhatsApp. Template names must match templates approved in Meta.
	'whatsapp'   => array(
		'api_version'   => 'v21.0',
		'language'      => 'fa',
		'consent_field' => 'whatsapp_consent', // applicant messages only with this box ticked
		'templates'     => array(
			'office'    => 'imp_new_member', // {{1}} name, {{2}} country, {{3}} entry link
			'applicant' => 'imp_welcome',    // {{1}} first name
		),
	),

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
