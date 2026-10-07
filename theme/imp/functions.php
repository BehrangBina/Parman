<?php
/**
 * IMP theme (child of Neve) — bootstrap only.
 *
 * Layout of the theme:
 *   config/site.php   all URLs, slugs, socials, menus and page routes
 *   src/              PHP logic (namespace IMP): core, routing, controllers, data, services, admin
 *   templates/        HTML only (layout, components, pages, admin) — each tagged with its Figma frame
 *   css/, js/, assets/
 *
 * Below 1024px the mobile design is shown, from 1024px the desktop design (Figma
 * "IMP Website 2026": Mobile-HiFi-Farsi and Desktop-HiFi-Farsi pages).
 *
 * @package IMP
 */

defined( 'ABSPATH' ) || exit;

define( 'IMP_VERSION', '0.2.0' );
define( 'IMP_DIR', get_stylesheet_directory() );
define( 'IMP_URI', get_stylesheet_directory_uri() );

require IMP_DIR . '/src/autoload.php';
require IMP_DIR . '/src/template-tags.php';

IMP\Core\Theme::boot();
