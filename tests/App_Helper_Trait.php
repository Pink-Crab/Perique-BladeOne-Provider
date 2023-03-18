<?php

declare(strict_types=1);

/**
 * Helper trait for all App tests
 * Includes clearing the internal state of an existing instance.
 *
 * @since 0.4.0
 * @author Glynn Quelch <glynn.quelch@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @package PinkCrab\Perique
 */

namespace PinkCrab\BladeOne\Tests;

use PinkCrab\Perique\Application\App_Factory;
use PinkCrab\Perique\Services\View\Component\Component_Compiler;
use PinkCrab\BladeOne\BladeOne;
use PinkCrab\Perique\Application\Hooks;
use PinkCrab\Perique\Application\App;
use Gin0115\WPUnit_Helpers\Objects;
use PinkCrab\BladeOne\PinkCrab_BladeOne;

trait App_Helper_Trait {

	/**
	 * Resets the any existing App instance with default properties.
	 *
	 * @return void
	 */
	protected static function unset_app_instance(): void {
		\remove_all_filters( Hooks::APP_INIT_SET_DI_RULES );

		$app = new App( FIXTURES_PATH );
		Objects::set_property( $app, 'app_config', null );
		Objects::set_property( $app, 'container', null );
		Objects::set_property( $app, 'module_manager', null );
		Objects::set_property( $app, 'loader', null );
		Objects::set_property( $app, 'booted', false );
		$app = null;
	}

	/**
	 * Returns an instance of app (not booted) populated with actual
	 * service objects.
	 *
	 * No registration classes are added, di has no rules, loader is empty
	 * but there is the settings from the Fixtures/Application added so we can
	 * use template paths in the App:view() tests.
	 *
	 * Is a plain and basic instance.
	 *
	 * @return App
	 */
	protected function pre_populated_app_provider(): App {
		$cache = \FIXTURES_PATH . 'cache';

		// Build and populate the app.
		$app = ( new App_Factory( \FIXTURES_PATH ) )
			->default_setup()
			->module(
				BladeOne::class,
				function( BladeOne $blade ) use ( $cache ) {
					$blade->template_path( \FIXTURES_PATH . 'views' );
					$blade->compiled_path( $cache );
					$blade->mode( PinkCrab_BladeOne::MODE_SLOW );
					return $blade;
				}
			)
			->app_config( array() )
			->boot();

		do_action( 'init' ); // Boots Perique
		do_action( 'wp_loaded' ); // Triggers the blade one config once all is loaded (see issue 13)
		return $app;
	}

}
