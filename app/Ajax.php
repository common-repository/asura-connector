<?php

namespace Dplugins\Asura\Connector;

use Dplugins\Asura\Connector\Ajax\Admin;
use Dplugins\Asura\Connector\Ajax\Frontend;
use Dplugins\Asura\Connector\Utils\OxygenBuilder;
use WP_Error;

class Ajax
{
	public function __construct()
	{
		if (OxygenBuilder::can()) {
			new Frontend();
		}

		if (OxygenBuilder::can(true)) {
			new Admin();
		}
	}

	public static function send_json_error($code, $message, $http_status = 400)
	{
		wp_send_json_error(
			new WP_Error($code, $message),
			$http_status
		);
	}
}
