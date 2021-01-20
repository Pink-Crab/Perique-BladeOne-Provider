<?php

declare(strict_types=1);

/**
 * Tests the BladeOne Provider.
 *
 * @since 0.1.0
 * @author Glynn Quelch <glynn.quelch@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @package PinkCrab\Core
 */

namespace PinkCrab\Registerables\Tests\Taxonomies;

use WP_UnitTestCase;
use eftec\bladeone\BladeOne;
use PinkCrab\Core\Services\View\View;
use PinkCrab\BladeOne\BladeOne_Provider;

class Test_BladeOne_Provider extends WP_UnitTestCase {

	protected static $blade;

	public function setUp(): void {
		parent::setup();

		if ( ! static::$blade ) {
			$cache         = \dirname( __FILE__ ) . '/files/cache';
			$views         = \dirname( __FILE__ ) . '/files/views';
			static::$blade = BladeOne_Provider::init( $views, $cache, 5 );
		}
	}

	/**
	 * Test is intance of bladeone
	 *
	 * @return void
	 */
	public function test_can_construct_from_provider(): void {
		$this->assertInstanceOf( BladeOne_Provider::class, static::$blade );
	}

	/**
	 * Test can call out blade.
	 *
	 * @return void
	 */
	public function test_can_get_blade(): void {
		$this->assertInstanceOf( BladeOne::class, static::$blade->get_blade() );
	}

	/**
	 * Test can render a view (print)
	 *
	 * @return void
	 */
	public function test_can_render_view(): void {
		$this->expectOutputString( 'rendered' );
		static::$blade->render( 'testview', array( 'foo' => 'rendered' ) );
	}

    /**
     * Test the view is returned.
     *
     * @return void
     */
	public function test_can_return_a_view(): void {
		$this->assertEquals(
			'rendered',
			static::$blade->render( 'testview', array( 'foo' => 'rendered' ), View::RETURN_VIEW )
		);
	}
}

