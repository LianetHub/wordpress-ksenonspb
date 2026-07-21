<?php

/**
 * Ksenonspb theme functions
 *
 * @package ksenonspb
 */

define('KSENON_VERSION', '2.0.5');
define('KSENON_DIR', get_template_directory());
define('KSENON_URI', get_template_directory_uri());
define('KSENON_ASSETS_URI', KSENON_URI . '/assets');

define('KSENON_CSS_BUNDLE', false);

$ksenon_inc = KSENON_DIR . '/inc/';

require_once $ksenon_inc . 'template-tags.php';
require_once $ksenon_inc . 'template-functions.php';
require_once $ksenon_inc . 'legal-pages.php';
require_once $ksenon_inc . 'format-helpers.php';
require_once $ksenon_inc . 'class-ksenon-walker-header-nav.php';
require_once $ksenon_inc . 'class-ksenon-walker-footer-nav.php';
require_once $ksenon_inc . 'setup.php';
require_once $ksenon_inc . 'cleanup.php';
require_once $ksenon_inc . 'enqueue.php';
require_once $ksenon_inc . 'acf.php';
require_once $ksenon_inc . 'cpt.php';
require_once $ksenon_inc . 'cf7.php';
require_once $ksenon_inc . 'wp-all-import.php';
