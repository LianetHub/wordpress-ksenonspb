<?php

/**
 * Template Name: Спасибо
 *
 * Страница после успешной отправки заявки (/thanks/).
 * Создайте страницу со slug `thanks` и этим шаблоном.
 *
 * @package ksenonspb
 */

add_filter(
	'wp_robots',
	static function ($robots) {
		$robots['noindex']  = true;
		$robots['nofollow'] = true;

		return $robots;
	}
);

get_header();

get_template_part('template-parts/pages/thanks');

get_footer();
