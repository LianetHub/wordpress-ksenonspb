<?php
/**
 * Ksenonspb theme functions
 *
 * @package ksenonspb
 */

define( 'KSENON_VERSION', '2.0.0' );
define( 'KSENON_DIR', get_template_directory() );
define( 'KSENON_URI', get_template_directory_uri() );
define( 'KSENON_ASSETS_URI', KSENON_URI . '/assets' );

// DEV: единый CSS-бандл вместо code-splitting. Вернуть false при возврате к сплиттингу.
define( 'KSENON_CSS_BUNDLE', true );

$ksenon_inc = KSENON_DIR . '/inc/';

require_once $ksenon_inc . 'template-tags.php';
require_once $ksenon_inc . 'template-functions.php';
require_once $ksenon_inc . 'setup.php';
require_once $ksenon_inc . 'cleanup.php';
require_once $ksenon_inc . 'enqueue.php';
require_once $ksenon_inc . 'acf.php';
require_once $ksenon_inc . 'cpt.php';
require_once $ksenon_inc . 'cf7.php';
