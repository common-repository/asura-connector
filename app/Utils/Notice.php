<?php

namespace Dplugins\Asura\Connector\Utils;

/**
 * @package Dplugins\Asura\Connector
 * @since 1.0.0
 * @author dplugins <mail@dplugins.com>
 * @copyright 2021 dplugins
 */
class Notice
{
    public const ERROR = 'error';
    public const SUCCESS = 'success';
    public const WARNING = 'warning';
    public const INFO = 'info';

    public static function lists()
    {
        $notices = Utils::get_option('_notices', []);

        Utils::update_option('_notices', []);

        return $notices;
    }

    public static function add($status, $message, $key = false)
    {
        $notices = Utils::get_option('_notices', []);

        $payload = [
            'status' => $status,
            'message' => $message,
        ];

        if ($key) {
            $notices[$key] = $payload;
        } else {
            $notices[] = $payload;
        }

        Utils::update_option('_notices', $notices);
    }

    public static function adds( $status, $messages ) {
		if ( ! is_array( $messages ) ) {
			$messages = [ $messages ];
		}

		foreach ( $messages as $message ) {
            if (!is_array($message)) {
                self::add($status, $message);
            } else {
                self::add($status, $message[0], $message[1], $message[2]);
            }
		}
	}

	public static function success( $message, $key = false ) {
		self::add( self::SUCCESS, $message, $key );
	}

	public static function warning( $message, $key = false ) {
		self::add( self::WARNING, $message, $key );
	}

	public static function info( $message, $key = false ) {
		self::add( self::INFO, $message, $key );
	}

	public static function error( $message, $key = false ) {
		self::add( self::ERROR, $message, $key );
	}
}
