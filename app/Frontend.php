<?php

namespace Dplugins\Asura\Connector;

use Dplugins\Asura\Connector\Models\License;
use Dplugins\Asura\Connector\Utils\DB;
use Dplugins\Asura\Connector\Utils\OxygenBuilder;
use Dplugins\Asura\Connector\Utils\Transient;

class Frontend {
	public function __construct() {
		if ( OxygenBuilder::is_oxygen_editor() || OxygenBuilder::is_oxygen_iframe() ) {
			$this->inject_library_component();
		}
	}

	public function inject_library_component() {
		global $ct_source_sites;
		$injects = [];

		foreach ( self::get_terms() as $term ) {
			$injects["{$term->slug}"] = [
				'label'     => "{$term->name}",
				'url'       => 'asura-connector',
				'accesskey' => "",
				'system'    => true
			];
		}

		$ct_source_sites = array_merge( $injects, $ct_source_sites );
	}

	public static function get_terms() {
		$licenses = DB::db()->select( License::TABLE_NAME, [
			'id [Int]',
			'provider_id [Int]',
			'hash',
		] );

		$terms = [];

		foreach ( $licenses as $license ) {
			$licenseObj = (object) $license;
			$termsCache = Transient::get( Connector::$module_id . "_terms_{$licenseObj->provider_id}_{$licenseObj->id}", [] );

			foreach ( $termsCache as $term ) {
				$term['license_id']  = $licenseObj->id;
				$term['provider_id'] = $licenseObj->provider_id;
				array_push( $terms, (object) $term );
			}
		}

		return $terms;
	}
}
