<?php

namespace Dplugins\Asura\Connector\Database\Migrations;

use Dplugins\Asura\Connector\Models\Provider;
use Dplugins\Asura\Connector\Utils\DB;

class CreateProvidersTable {

	public static function up() {
		DB::db()->create( Provider::TABLE_NAME, [
			'id'         => [ 'BIGINT(20)', 'UNSIGNED', 'NOT NULL', 'AUTO_INCREMENT', ],
			'site_title' => [ 'VARCHAR(255)', 'NOT NULL', ],
			'endpoint'   => [ 'VARCHAR(255)', 'NOT NULL', ],
			'api_key'    => [ 'VARCHAR(255)', 'NOT NULL', ],
			'api_secret' => [ 'VARCHAR(255)', 'NOT NULL', ],
			'PRIMARY KEY (<id>)',
		], [
			'ENGINE'                => 'InnoDB',
			'DEFAULT CHARACTER SET' => DB::wpdb()->charset,
			'COLLATE'               => DB::wpdb()->collate,
		] );
	}

	public static function down() {
		DB::db()->drop( Provider::TABLE_NAME );
	}
}