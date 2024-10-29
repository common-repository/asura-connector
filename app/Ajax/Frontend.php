<?php

namespace Dplugins\Asura\Connector\Ajax;

use Dplugins\Asura\Connector\Connector;
use Dplugins\Asura\Connector\Frontend as ConnectorFrontend;
use Dplugins\Asura\Connector\Lib\Asura as AsuraSDK;
use Dplugins\Asura\Connector\Utils\Transient;
use WP_Error;

/**
 * @version Asura 4.1.x
 * @version Oxygen Builder 3.8.1.rc.1
 */
class Frontend {
	public function __construct() {
		$this->tamper();
	}

	public function tamper() {
		add_action( 'wp_loaded', function () {
			remove_all_actions( 'wp_ajax_ct_new_style_api_call' );
			add_action( "wp_ajax_ct_new_style_api_call", [ $this, 'ct_new_style_api_call' ], 10, 1 );
		} );
	}

	public function ct_new_style_api_call() {
		$call_type = isset( $_REQUEST['call_type'] ) ? sanitize_text_field( $_REQUEST['call_type'] ) : false;
		ct_new_style_api_call_security_check( $call_type );

		switch ( $call_type ) {
			case 'setup_default_data':
				ct_setup_default_data();
				break;
			case 'get_component_from_source':
				$filtered = base64_decode( $_REQUEST['source'] );

				if ( strpos( $filtered, 'asura::' ) === 0 ) {
					$embeded_config = json_decode( base64_decode( substr( $filtered, strlen( 'asura::' ) ) ) );
					$this->get_component_from_source( $embeded_config );
				} else {
					ct_get_component_from_source();
				}
				break;
			case 'get_page_from_source':
				$filtered = base64_decode( $_REQUEST['source'] );

				if ( strpos( $filtered, 'asura::' ) === 0 ) {
					$embeded_config = json_decode( base64_decode( substr( $filtered, strlen( 'asura::' ) ) ) );
					$this->get_page_from_source( $embeded_config );
				} else {
					ct_get_page_from_source();
				}
				break;
			case 'get_items_from_source':
				$terms    = ConnectorFrontend::get_terms();
				$filtered = array_filter( $terms, function ( $term ) {
					return $term->slug === $_REQUEST['name'];
				} );

				if ( ! empty( $filtered ) ) {
					$this->get_items_from_source( array_shift( $filtered ) );
				} else {
					ct_get_items_from_source();
				}
				break;
			case 'get_stuff_from_source':
				ct_get_stuff_from_source();
				break;
		}
	}

	public function get_items_from_source( $filtered ) {
		$provider_id = $filtered->provider_id;
		$provider    = Admin::getProviderFromDB( $provider_id );
		Admin::validateProviderExist( $provider );

		$license_id = $filtered->license_id;
		$license    = Admin::getLicenseFromDB( $provider_id, $license_id );
		Admin::validateLicenseExist( $license );

		$term_slug = $filtered->slug;

		$designSetsCache = Transient::remember( Connector::$module_id."_items_{$license->provider_id}_{$license->id}_{$term_slug}", HOUR_IN_SECONDS, function () use ( $provider, $license, $term_slug ) {
			$response = AsuraSDK::oxygenbuilder_items( $provider, $license, $term_slug );
			if ( $response->getStatusCode() !== 200 ) {
				error_log( "asura-connector [error]: couldn't retrieve design sets for license id {$license->id} and term slug {$term_slug}. http error code: {$response->getStatusCode()}" );

				return null;
			}

			return json_decode( $response->getBody()->getContents(), true );
		} );

		if ( ! $designSetsCache ) {
			wp_send_json_error(
				new WP_Error(
					'asura_connection_error',
					__( "Couldn't retrieve design sets, please contact design set provider or plugin developer", 'asura-connector' )
				),
				500
			);
		}
		wp_send_json( $designSetsCache );
	}


	public function get_page_from_source( $embeded_config ) {
		$post_id   = $_REQUEST['id'];
		$pageCache = Transient::remember( Connector::$module_id."_page_{$embeded_config->provider->id}_{$embeded_config->license->id}_{$embeded_config->term_slug}_{$post_id}", HOUR_IN_SECONDS, function () use ( $embeded_config, $post_id ) {
			$response = AsuraSDK::oxygenbuilder_pageclasses( $embeded_config, $post_id );

			if ( $response->getStatusCode() !== 200 ) {
				error_log( "asura-connector [error]: couldn't retrieve design sets page for license id {$embeded_config->license->id} and term slug {$embeded_config->term_slug}. http error code: {$response->getStatusCode()}" );

				return null;
			}

			$components  = [];
			$classes     = [];
			$colors      = [];
			$lookupTable = [];

			$content = json_decode( $response->getBody()->getContents(), true );

			if ( isset( $content['components'] ) ) {
				$components = $content['components'];
			}
			if ( isset( $content['classes'] ) ) {
				$classes = $content['classes'];
			}
			if ( isset( $content['colors'] ) ) {
				$colors = $content['colors'];
			}
			if ( isset( $content['lookuptable'] ) ) {
				$lookupTable = $content['lookuptable'];
			}

			foreach ( $components as $key => $component ) {

				if ( $component['name'] === 'ct_reusable' ) {
					unset( $components[ $key ] );
				}

				if ( ! isset( $components[ $key ] ) ) {
					continue;
				}

				$component[ $key ] = ct_base64_encode_decode_tree( [ $component ], true )[0];

				if ( isset( $component['children'] ) ) {
					if ( is_array( $components[ $key ]['children'] ) ) {
						$components[ $key ]['children'] = ct_recursively_manage_reusables( $components[ $key ]['children'], null, null );
					}
				}
			}

			$output = [
				'components' => $components
			];

			if ( sizeof( $classes ) > 0 ) {
				$output['classes'] = $classes;
			}

			if ( sizeof( $colors ) > 0 ) {
				$output['colors'] = $colors;
			}

			if ( sizeof( $lookupTable ) > 0 ) {
				$output['lookuptable'] = $lookupTable;
			}

			return $output;
		} );


		if ( ! $pageCache ) {
			wp_send_json_error(
				new WP_Error(
					'asura_connection_error',
					__( "Couldn't retrieve design sets page, please contact design set provider or plugin developer", 'asura-connector' )
				),
				500
			);
		}

		wp_send_json( $pageCache );
	}

	public function get_component_from_source( $embeded_config ) {
		$component_id = $_REQUEST['id'];
		$post_id      = $_REQUEST['page'];

		$componentCache = Transient::remember( Connector::$module_id."_component_{$embeded_config->provider->id}_{$embeded_config->license->id}_{$embeded_config->term_slug}_{$post_id}_{$component_id}", HOUR_IN_SECONDS, function () use ( $embeded_config, $post_id, $component_id ) {

			$response = AsuraSDK::oxygenbuilder_componentclasses( $embeded_config, $post_id, $component_id );

			if ( $response->getStatusCode() !== 200 ) {
				error_log( "asura-connector [error]: couldn't retrieve design sets component for license id {$embeded_config->license->id} and term slug {$embeded_config->term_slug}. http error code: {$response->getStatusCode()}" );

				return null;
			}

			$component   = [];
			$classes     = [];
			$colors      = [];
			$lookupTable = [];

			$content = json_decode( $response->getBody()->getContents(), true );

			if ( isset( $content['component'] ) ) {
				$component = $content['component'];
			}
			if ( isset( $content['classes'] ) ) {
				$classes = $content['classes'];
			}
			if ( isset( $content['colors'] ) ) {
				$colors = $content['colors'];
			}
			if ( isset( $content['lookuptable'] ) ) {
				$lookupTable = $content['lookuptable'];
			}

			$component = ct_base64_encode_decode_tree( array( $component ), true )[0];

			if ( isset( $component['children'] ) ) {
				// global $wpdb;
				// $data = $wpdb->get_results("SELECT * FROM `" . $wpdb->postmeta . "` WHERE meta_key='ct_source_site' AND meta_value='" . $wpdb->prepare(base64_decode($source)) . "'");
				// $source_info = array();

				// if (is_array($data) && !empty($data)) {
				// 	foreach ($data as $meta) {
				// 		// if post exists and is not in trash
				// 		$post = get_post($meta->post_id);

				// 		if ($post && $post->post_status != 'trash') {
				// 			$source_info[] = $meta->post_id;
				// 		}
				// 	}
				// }

				if ( is_array( $component['children'] ) ) {
					$component['children'] = ct_recursively_manage_reusables( $component['children'], null, null );
				}
			}

			$output = array( 'component' => $component );

			if ( sizeof( $classes ) > 0 ) {
				$output['classes'] = $classes;
			}

			if ( sizeof( $colors ) > 0 ) {
				$output['colors'] = $colors;
			}

			if ( sizeof( $lookupTable ) > 0 ) {
				$output['lookuptable'] = $lookupTable;
			}

			return $output;
		} );

		if ( ! $componentCache ) {
			wp_send_json_error(
				new WP_Error(
					'asura_connection_error',
					__( "Couldn't retrieve design sets component, please contact design set provider or plugin developer", 'asura-connector' )
				),
				500
			);
		}

		wp_send_json( $componentCache );
	}

}
