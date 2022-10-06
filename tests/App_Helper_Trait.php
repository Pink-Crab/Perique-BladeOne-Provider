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
use PinkCrab\Perique\Application\App;
use eftec\bladeone\BladeOne;
use Dice\Dice;
use PinkCrab\Loader\Hook_Loader;
use PinkCrab\Perique\Services\Dice\PinkCrab_Dice;
use PinkCrab\Perique\Services\Registration\Registration_Service;
use Gin0115\WPUnit_Helpers\Objects;
use PinkCrab\BladeOne\BladeOne_Bootstrap;


trait App_Helper_Trait {

	/**
	 * Resets the any existing App isn'tance with default properties.
	 *
	 * @return void
	 */
	protected static function unset_app_instance(): void {
		$app = new App();
		Objects::set_property( $app, 'app_config', null );
		Objects::set_property( $app, 'container', null );
		Objects::set_property( $app, 'registration', null );
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
		$cache = \dirname( __FILE__ ) . '/files/cache';
		$views = \dirname( __FILE__ ) . '/files/views';

		// Setup BladeOne.
		BladeOne_Bootstrap::use( $views, $cache, BladeOne::MODE_DEBUG );

		// Build and populate the app.
		$app = ( new App_Factory( __DIR__ ) )->with_wp_dice( true )
		->di_rules(
			array( Component_Compiler::class => array( 'constructParams' => array( 'components' ) ) )
		)
		->app_config( array() )
		->boot();

		do_action( 'init' ); // Boots Perique
		do_action( 'wp_loaded' ); // Triggers the blade one config once all is loaded (see issue 13)
		return $app;
	}

}
