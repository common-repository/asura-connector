<?php

namespace Dplugins\Asura\Connector\Database\Migrations;

use Dplugins\Asura\Connector\Models\License;
use Dplugins\Asura\Connector\Utils\DB;

class CreateLicensesTable {

	public static function up() {
		DB::db()->create( License::TABLE_NAME, [
			'id'          => [ 'BIGINT(20)', 'UNSIGNED', 'NOT NULL', 'AUTO_INCREMENT', ],
			'provider_id' => [ 'BIGINT(20)', 'UNSIGNED', 'NOT NULL', ],
			'license'     => [ 'VARCHAR(255)', 'NOT NULL', ],
			'hash'        => [ 'VARCHAR(255)', 'NOT NULL', ],
			'PRIMARY KEY (<id>)',
		], [
			'ENGINE'                => 'InnoDB',
			'DEFAULT CHARACTER SET' => DB::wpdb()->charset,
			'COLLATE'               => DB::wpdb()->collate,
		] );
	}

	public static function down() {
		DB::db()->drop( License::TABLE_NAME );
	}
}