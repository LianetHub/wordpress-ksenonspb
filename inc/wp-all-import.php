<?php

/**
 * WP All Import helpers for CPT service / brand.
 *
 * Важно: не мапить ACF-repeaters price_* / faq / features через Variable Mode + PHP.
 * Хук пишет repeaters сам из колонок CSV (или из meta ksenon_raw_*).
 *
 * @package ksenonspb
 */

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Scalar / nested value → plain string (без склейки массива строк-рядов).
 *
 * @param mixed $value Value.
 * @return string
 */
function ksenon_wpai_scalar_string($value)
{
	if ($value instanceof SimpleXMLElement) {
		return trim((string) $value);
	}
	if (is_array($value)) {
		$flat = array();
		array_walk_recursive(
			$value,
			static function ($item) use (&$flat) {
				if (is_scalar($item) || $item === null) {
					$item = trim((string) $item);
					if ($item !== '') {
						$flat[] = $item;
					}
				}
			}
		);
		return implode('', $flat);
	}
	return trim((string) $value);
}

/**
 * Нормализует сырое значение колонки в строку с рядами через |.
 *
 * WPAI / SimpleXML иногда уже отдаёт массив сегментов по `|`.
 * Старый код брал только [0] → одна строка в ACF.
 *
 * @param mixed $value Node / meta value.
 * @return string
 */
function ksenon_wpai_normalize_encoded_rows($value)
{
	if ($value instanceof SimpleXMLElement) {
		$as_array = json_decode(wp_json_encode($value), true);
		if (is_array($as_array)) {
			$value = $as_array;
		} else {
			return trim((string) $value);
		}
	}

	if (! is_array($value)) {
		return trim((string) $value);
	}

	// Список скаляров / вложенных узлов.
	$list = array();
	$is_list = array_keys($value) === range(0, count($value) - 1);
	if ($is_list) {
		foreach ($value as $item) {
			$str = ksenon_wpai_scalar_string($item);
			if ($str !== '') {
				$list[] = $str;
			}
		}
	} else {
		// Ассоциативный узел — берём текстовое содержимое.
		return ksenon_wpai_scalar_string($value);
	}

	if (! $list) {
		return '';
	}

	// Уже нарезанные ряды: "A::B::C::D", "E::F::G::H"
	if (strpos($list[0], '::') !== false) {
		return implode('|', $list);
	}

	// Один ряд, разбитый ошибочно — склеиваем обратно через ::
	if (count($list) <= 4 && strpos(implode('', $list), '|') === false) {
		return implode('::', $list);
	}

	return implode('|', $list);
}

/**
 * @param string $raw Pipe/colon encoded price rows.
 * @return array<int, array{name:string,price:string,duration:string,warranty:string}>
 */
function ksenon_wpai_parse_price_rows($raw)
{
	$rows = array();
	$raw = ksenon_wpai_normalize_encoded_rows($raw);
	if ($raw === '') {
		return $rows;
	}

	foreach (explode('|', $raw) as $chunk) {
		$chunk = trim((string) $chunk);
		if ($chunk === '') {
			continue;
		}
		$parts = array_map('trim', explode('::', $chunk));
		$warranty = isset($parts[3]) ? $parts[3] : '';
		$rows[] = array(
			'name'     => isset($parts[0]) ? $parts[0] : '',
			'price'    => isset($parts[1]) ? $parts[1] : '',
			'duration' => isset($parts[2]) ? $parts[2] : '',
			'warranty' => ($warranty === '-') ? '' : $warranty,
		);
	}

	return $rows;
}

/**
 * @param string $raw Pipe/colon encoded FAQ rows.
 * @return array<int, array{question:string,answer:string}>
 */
function ksenon_wpai_parse_faq_rows($raw)
{
	$rows = array();
	$raw = ksenon_wpai_normalize_encoded_rows($raw);
	if ($raw === '') {
		return $rows;
	}

	foreach (explode('|', $raw) as $chunk) {
		$chunk = trim((string) $chunk);
		if ($chunk === '') {
			continue;
		}
		$parts = array_map('trim', explode('::', $chunk, 2));
		$rows[] = array(
			'question' => isset($parts[0]) ? $parts[0] : '',
			'answer'   => isset($parts[1]) ? $parts[1] : '',
		);
	}

	return $rows;
}

/**
 * @param array<string,mixed> $record Record.
 * @param string              $key    Column.
 * @return mixed Raw value or null.
 */
function ksenon_wpai_record_raw(array $record, $key)
{
	if (array_key_exists($key, $record)) {
		return $record[$key];
	}
	$key_lower = strtolower($key);
	foreach ($record as $name => $value) {
		if (strtolower((string) $name) === $key_lower) {
			return $value;
		}
	}
	return null;
}

/**
 * @param int                 $post_id  Post ID.
 * @param array<string,mixed> $record   Import record.
 * @param string              $column   CSV column.
 * @param string              $meta_key Temp meta.
 * @return string
 */
function ksenon_wpai_get_raw($post_id, array $record, $column, $meta_key)
{
	// Meta first: надёжный путь, если в WPAI замапили {price_main[1]} → ksenon_raw_*.
	$from_meta = get_post_meta($post_id, $meta_key, true);
	if (is_string($from_meta) && trim($from_meta) !== '') {
		return ksenon_wpai_normalize_encoded_rows($from_meta);
	}

	$from_node = ksenon_wpai_record_raw($record, $column);
	if ($from_node !== null && $from_node !== '') {
		return ksenon_wpai_normalize_encoded_rows($from_node);
	}

	return '';
}

/**
 * Записать ACF repeater по field key (с предварительной очисткой).
 *
 * @param string               $field_key Field key.
 * @param array<int,array>     $rows      Rows.
 * @param int                  $post_id   Post ID.
 */
function ksenon_wpai_set_repeater($field_key, array $rows, $post_id)
{
	if (! function_exists('update_field') || ! function_exists('delete_field')) {
		return;
	}
	delete_field($field_key, $post_id);
	if ($rows) {
		update_field($field_key, $rows, $post_id);
	}
}

/**
 * @param int                    $post_id   Post ID.
 * @param SimpleXMLElement|mixed $xml_node  Record.
 * @param mixed                  $is_update Update flag.
 */
function ksenon_wpai_service_saved_post($post_id, $xml_node = null, $is_update = null)
{
	$post_id = (int) $post_id;
	if ($post_id <= 0 || get_post_type($post_id) !== 'service') {
		return;
	}
	if (! function_exists('update_field')) {
		return;
	}

	$record = array();
	if ($xml_node !== null) {
		$decoded = json_decode(wp_json_encode($xml_node), true);
		if (is_array($decoded)) {
			$record = $decoded;
		}
	}

	$map = array(
		'price_main'         => array(
			'meta'  => 'ksenon_raw_price_main',
			'field' => 'field_ksenon_service_price_main',
			'parse' => 'ksenon_wpai_parse_price_rows',
		),
		'price_extra'        => array(
			'meta'  => 'ksenon_raw_price_extra',
			'field' => 'field_ksenon_service_price_extra',
			'parse' => 'ksenon_wpai_parse_price_rows',
		),
		'price_diagnostics'  => array(
			'meta'  => 'ksenon_raw_price_diagnostics',
			'field' => 'field_ksenon_service_price_diagnostics',
			'parse' => 'ksenon_wpai_parse_price_rows',
		),
		'faq'                => array(
			'meta'  => 'ksenon_raw_faq',
			'field' => 'field_ksenon_service_faq',
			'parse' => 'ksenon_wpai_parse_faq_rows',
		),
	);

	foreach ($map as $column => $cfg) {
		$raw = ksenon_wpai_get_raw($post_id, $record, $column, $cfg['meta']);
		if ($raw === '') {
			continue;
		}
		$rows = call_user_func($cfg['parse'], $raw);
		ksenon_wpai_set_repeater($cfg['field'], $rows, $post_id);
		delete_post_meta($post_id, $cfg['meta']);
	}
}

/**
 * @param string $raw Pipe/colon encoded feature rows (title::text).
 * @return array<int, array{feature_title:string,feature_text:string}>
 */
function ksenon_wpai_parse_feature_rows($raw)
{
	$rows = array();
	$raw = ksenon_wpai_normalize_encoded_rows($raw);
	if ($raw === '') {
		return $rows;
	}

	foreach (explode('|', $raw) as $chunk) {
		$chunk = trim((string) $chunk);
		if ($chunk === '') {
			continue;
		}
		$parts = array_map('trim', explode('::', $chunk, 2));
		$rows[] = array(
			'feature_title' => isset($parts[0]) ? $parts[0] : '',
			'feature_text'  => isset($parts[1]) ? $parts[1] : '',
		);
	}

	return $rows;
}

/**
 * @param int                    $post_id   Post ID.
 * @param SimpleXMLElement|mixed $xml_node  Record.
 * @param mixed                  $is_update Update flag.
 */
function ksenon_wpai_brand_saved_post($post_id, $xml_node = null, $is_update = null)
{
	$post_id = (int) $post_id;
	if ($post_id <= 0 || get_post_type($post_id) !== 'brand') {
		return;
	}
	if (! function_exists('update_field')) {
		return;
	}

	$record = array();
	if ($xml_node !== null) {
		$decoded = json_decode(wp_json_encode($xml_node), true);
		if (is_array($decoded)) {
			$record = $decoded;
		}
	}

	$raw = ksenon_wpai_get_raw($post_id, $record, 'features', 'ksenon_raw_features');
	if ($raw === '') {
		return;
	}

	$rows = ksenon_wpai_parse_feature_rows($raw);
	ksenon_wpai_set_repeater('field_ksenon_brand_features', $rows, $post_id);
	delete_post_meta($post_id, 'ksenon_raw_features');
}

// Поздний приоритет — после ACF Add-On у WP All Import.
if (function_exists('add_action')) {
	add_action('pmxi_saved_post', 'ksenon_wpai_service_saved_post', 9999, 3);
	add_action('pmxi_saved_post', 'ksenon_wpai_brand_saved_post', 9999, 3);
}

