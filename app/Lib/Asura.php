<?php

namespace Dplugins\Asura\Connector\Lib;

use Dplugins\Asura\Connector\Utils\Http;
use GuzzleHttp\Exception\RequestException;

/**
 * @version Asura 4.1.x
 */
class Asura {

	public static function remoteRequest( $method, $query = [], $path, $provider ) {
		$response = null;
		try {
			$query_or_body = $method === 'get' ? 'query' : 'form_params';

			$response = Http::http()->{$method}(
				"{$provider->endpoint}/{$path}",
				[
					$query_or_body => array_merge(
						$query,
						[
							'api_key'    => $provider->api_key,
							'api_secret' => $provider->api_secret,
						]
					)
				]
			);
		} catch ( RequestException $e ) {
			$response = $e->getResponse();
		}

		return $response;
	}

	/**
	 *
	 * @param object $provider
	 * @param string $license_key
	 *
	 * @return mixed
	 */
	public static function license_domains_register( $provider, $license_key ) {
		return self::remoteRequest( 'post', [
			'key'    => $license_key,
			'domain' => site_url(),
		], "licenses/domains/register", $provider );
	}

	/**
	 *
	 * @param object $provider
	 * @param string $license_key
	 *
	 * @return mixed
	 */
	public static function license_domains_deregister( $provider, $license_key ) {
		return self::remoteRequest( 'post', [
			'key'    => $license_key,
			'domain' => home_url(),
		], "licenses/domains/deregister", $provider );
	}

	/**
	 *
	 * @param object $provider
	 * @param string $hash
	 *
	 * @return mixed
	 */
	public static function license_terms_index( $provider, $hash ) {
		return self::remoteRequest( 'get', [
			'hash' => $hash,
		], "licenses/terms", $provider );
	}

	/**
	 *
	 * @param object $provider
	 * @param string $hash
	 * @param string $term_slug
	 *
	 * @return mixed
	 */
	public static function oxygenbuilder_items( $provider, $license, $term_slug ) {
		return self::remoteRequest( 'get', [
			'hash'           => $license->hash,
			'term_slug'      => $term_slug,
			'embeded_source' => 'asura::' . base64_encode( json_encode( [
					'provider'  => $provider,
					'license'   => $license,
					'term_slug' => $term_slug,
				] ) ),
		], "oxygenbuilder/items", $provider );
	}

	/**
	 *
	 * @param object $provider
	 * @param string $hash
	 * @param string $term_slug
	 * @param string|int $post_id
	 *
	 * @return mixed
	 */
	public static function oxygenbuilder_pageclasses( $embeded_config, $post_id ) {
		return self::remoteRequest( 'get', [
			'hash'      => $embeded_config->license->hash,
			'term_slug' => $embeded_config->term_slug,
			'post_id'   => $post_id,
		], "oxygenbuilder/pageclasses", $embeded_config->provider );
	}

	/**
	 *
	 * @param object $provider
	 * @param string $hash
	 * @param string $term_slug
	 * @param string|int $post_id
	 * @param string|int $component_id
	 *
	 * @return mixed
	 */
	public static function oxygenbuilder_componentclasses( $embeded_config, $post_id, $component_id ) {
		return self::remoteRequest( 'get', [
			'hash'         => $embeded_config->license->hash,
			'term_slug'    => $embeded_config->term_slug,
			'post_id'      => $post_id,
			'component_id' => $component_id,
		], "oxygenbuilder/componentclasses", $embeded_config->provider );
	}

	/**
	 *
	 * @param object $provider
	 * @param string $hash
	 * @param string $term_slug
	 *
	 * @return mixed
	 */
	public static function oxygenbuilder_colors( $provider, $hash, $term_slug ) {
		return self::remoteRequest( 'get', [
			'hash'      => $hash,
			'term_slug' => $term_slug,
		], "oxygenbuilder/colors", $provider );
	}

	/**
	 *
	 * @param object $provider
	 * @param string $hash
	 * @param string $term_slug
	 *
	 * @return mixed
	 */
	public static function oxygenbuilder_stylesheets( $provider, $hash, $term_slug ) {
		return self::remoteRequest( 'get', [
			'hash'      => $hash,
			'term_slug' => $term_slug,
		], "oxygenbuilder/stylesheets", $provider );
	}

	/**
	 *
	 * @param object $provider
	 * @param string $hash
	 * @param string $term_slug
	 *
	 * @return mixed
	 */
	public static function oxygenbuilder_settings( $provider, $hash, $term_slug ) {
		return self::remoteRequest( 'get', [
			'hash'      => $hash,
			'term_slug' => $term_slug,
		], "oxygenbuilder/settings", $provider );
	}

	/**
	 *
	 * @param object $provider
	 * @param string $hash
	 * @param string $term_slug
	 *
	 * @return mixed
	 */
	public static function oxygenbuilder_stylesets( $provider, $hash, $term_slug ) {
		return self::remoteRequest( 'get', [
			'hash'      => $hash,
			'term_slug' => $term_slug,
		], "oxygenbuilder/stylesets", $provider );
	}

	/**
	 *
	 * @param object $provider
	 * @param string $hash
	 * @param string $term_slug
	 *
	 * @return mixed
	 */
	public static function oxygenbuilder_selectors( $provider, $hash, $term_slug ) {
		return self::remoteRequest( 'get', [
			'hash'      => $hash,
			'term_slug' => $term_slug,
		], "oxygenbuilder/selectors", $provider );
	}

	/**
	 *
	 * @param object $provider
	 * @param string $hash
	 * @param string $term_slug
	 *
	 * @return mixed
	 */
	public static function oxygenbuilder_templates( $provider, $hash, $term_slug ) {
		return self::remoteRequest( 'get', [
			'hash'      => $hash,
			'term_slug' => $term_slug,
		], "oxygenbuilder/templates", $provider );
	}

	/**
	 *
	 * @param object $provider
	 * @param string $hash
	 * @param string $term_slug
	 *
	 * @return mixed
	 */
	public static function oxygenbuilder_pages( $provider, $hash, $term_slug ) {
		return self::remoteRequest( 'get', [
			'hash'      => $hash,
			'term_slug' => $term_slug,
		], "oxygenbuilder/pages", $provider );
	}

	/**
	 *
	 * @param object $provider
	 * @param string $hash
	 * @param string $term_slug
	 *
	 * @return mixed
	 */
	public static function oxygenbuilder_classes( $provider, $hash, $term_slug ) {
		return self::remoteRequest( 'get', [
			'hash'      => $hash,
			'term_slug' => $term_slug,
		], "oxygenbuilder/classes", $provider );
	}

}