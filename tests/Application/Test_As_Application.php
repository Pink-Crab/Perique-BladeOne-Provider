<?php

declare(strict_types=1);

/**
 * Application test
 *
 * @since 0.1.0
 * @author Glynn Quelch <glynn.quelch@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @package PinkCrab\BladeOne
 */

namespace PinkCrab\BladeOne\Tests;

use WP_UnitTestCase;
use eftec\bladeone\BladeOne;
use Gin0115\WPUnit_Helpers\Output;
use Gin0115\WPUnit_Helpers\Objects;
use PinkCrab\Perique\Application\App;
use PinkCrab\BladeOne\BladeOne_Engine;
use PinkCrab\Perique\Application\Hooks;
use PinkCrab\BladeOne\PinkCrab_BladeOne;
use PinkCrab\Perique\Services\View\View;
use PinkCrab\BladeOne\BladeOne_Bootstrap;
use PinkCrab\BladeOne\Tests\Fixtures\Input;
use PinkCrab\Perique\Interfaces\Renderable;
use PinkCrab\Perique\Application\App_Factory;
use PinkCrab\Perique\Services\View\PHP_Engine;
use PinkCrab\BladeOne\Abstract_BladeOne_Config;
use PinkCrab\BladeOne\BladeOne as BladeOne_Module;
use PinkCrab\BladeOne\Tests\Fixtures\Mock_Controller;
use PinkCrab\BladeOne\Tests\Fixtures\Mock_Blade_Config;
use PinkCrab\BladeOne\Tests\Fixtures\Mock_Custom_Blade_One_Instance;

class Test_As_Application extends WP_UnitTestCase {

	use App_Helper_Trait;

	/**
	 * On tear down, unset app instance.
	 *
	 * @return void
	 */
	public function tear_down(): void {
		parent::tear_down();
		$this->unset_app_instance();
	}

	/** @testdox It should be possible to render a template using only its filename and pass values to the view to be rendered */
	public function test_render_template_using_only_file_name() {
		$app = $this->pre_populated_app_provider();

		$output = $app::view()->render( 'testview', array( 'foo' => 'bar' ), false );
		$this->assertEquals( 'bar', $output );
	}

	/** @testdox It should be possible to set a custom template path when adding BladeOne as a module */
	public function test_set_custom_template_path() {
		$app = ( new App_Factory( \FIXTURES_PATH ) )
			->default_setup()
			->module(
				BladeOne_Module::class,
				function( BladeOne_Module $e ) {
					$e->template_path( \FIXTURES_PATH . 'views/custom-path' );
					$e->compiled_path( \FIXTURES_PATH . 'cache' );
					return $e;
				}
			)
			->boot();
		do_action( 'init' );

		$output = $app::view()->render( 'template', array( 'custom_path' => 'bar' ), false );
		$this->assertEquals( 'bar', $output );
	}

	/** @testdox It should be possible to configure the template and compiled paths, mode and access the BladeOne_Engine from the Modules config callback. */
	public function test_configure_bladeone_module() {
		$app = ( new App_Factory( \FIXTURES_PATH ) )
			->default_setup()
			->module(
				BladeOne_Module::class,
				function( BladeOne_Module $e ) {
					$e->template_path( \FIXTURES_PATH . 'views/custom-path' );
					$e->compiled_path( \FIXTURES_PATH . 'cache' );
					$e->mode( BladeOne::MODE_DEBUG );
					$e->config(
						function( BladeOne_Engine $engine ) {
							$engine->directive(
								'bar',
								function( $expression ) {
									return "<?php echo 'barf'; ?>";
								}
							);
							return $engine;
						}
					);
					return $e;
				}
			)
			->boot();
		do_action( 'init' );

		$blade = $app::make( Renderable::class );

		// Check mode is debug.
		$this->assertEquals( BladeOne::MODE_DEBUG, $blade->getMode() );

		// Check the custom directive is added.
		$this->assertEquals(
			"<?php echo 'barf'; ?>",
			$blade->compileString( '@bar()' )
		);

		// Check that both paths are set.
		$health_check = Output::buffer(
			function() use ( $blade ) {
				$result = $blade->checkHealthPath();
				$this->assertTrue( $result );
			}
		);

		$this->assertStringContainsString( \sprintf( 'Compile-path [%s] is a folder ', \FIXTURES_PATH . 'cache' ), $health_check );
		$this->assertStringContainsString( \sprintf( 'Template-path (view) [%s] is a folder ', \FIXTURES_PATH . 'views/custom-path' ), $health_check );
	}

	/** @testdox It should be possible to define a compiled path and have it created if it doesnt exist. */
	public function test_create_compiled_path_if_not_exists() {
		$this->unset_app_instance();

		$cached_path = \FIXTURES_PATH . 'cache/' . time();

		$app = ( new App_Factory( \FIXTURES_PATH ) )
			->default_setup()
			->module(
				BladeOne_Module::class,
				function( BladeOne_Module $e ) use ( $cached_path ) {
					return $e
						->template_path( \FIXTURES_PATH . 'views/custom-path' )
						->compiled_path( $cached_path );
				}
			)
			->boot();
		do_action( 'init' );

		$this->assertTrue( \file_exists( $cached_path ) );
	}

	/** @testdox It should be possible to render a component nested inside another component */
	public function test_can_render_nested_component(): void {
		$app = $this->pre_populated_app_provider();

		$value = $app::view()->render( 'testnestedcomponents', array(), false );

		$this->assertStringContainsString(
			'<input name="a" id="b" value="c" type="d" />',
			$value
		);
	}

	/** @testdox It should be possible to render an nested view model using $this->view_model($instance) */
	public function test_can_render_nested_view_model(): void {
		$app = $this->pre_populated_app_provider();

		$value = $app::view()->render( 'testrendersviewmodel', array(), false );

		$this->assertStringContainsString( 'woo', $value );
	}

	/** @testdox When a string is escaped, it should use the default WP esc_html */
	public function test_can_escape_string(): void {
		$app = $this->pre_populated_app_provider();

		$called_esc_html = false;
		add_filter(
			'esc_html',
			function( $value ) use ( &$called_esc_html ) {
				$called_esc_html = true;
				return $value;
			}
		);

		$app::view()->render( 'testview', array( 'foo' => 'woo' ), false );
		$this->assertTrue( $called_esc_html );
	}

	/** @testdox It should be possible to set any function as the esc function */
	public function test_set_custom_esc_function(): void {
		$app = $this->pre_populated_app_provider();

		$called_esc_html = false;
		add_filter(
			'attribute_escape',
			function( $value ) use ( &$called_esc_html ) {
				$called_esc_html = true;
				return $value;
			}
		);

		$app::view()->engine()->get_blade()->set_esc_function( 'esc_attr' );
		$app::view()->render( 'testview', array( 'foo' => 'woo' ), false );
		$this->assertTrue( $called_esc_html );
	}

	/** @testdox It should be possible to render an nested view model using @viewModel($instance) */
	public function test_can_render_nested_view_model_directive(): void {
		$app = $this->pre_populated_app_provider();

		$value = $app::view()->render( 'testrendersviewmodeldirective', array(), false );

		$this->assertStringContainsString( 'woo', $value );
	}

	/** @testdox It should be possible to render a component nested inside another component using @component($instance) */
	public function test_can_render_nested_component_using_directive(): void {
		$app = $this->pre_populated_app_provider();

		$value = $app::view()->render( 'testnestedcomponentsdirective', array(), false );

		$this->assertStringContainsString(
			'<input name="a" id="b" value="c" type="d" />',
			$value
		);
	}

	/**
	 * @testdox By not passing the view or compliled path to the use method on boot strap these should be implied.
	 */
	public function test_can_use_default_paths(): void {

		$app = ( new App_Factory( \FIXTURES_PATH ) )
			->default_setup()
			->module( BladeOne_Module::class )
			->app_config( array() )
			->boot();

		do_action( 'init' ); // Boots Perique
		do_action( 'wp_loaded' ); // Triggers the blade one config once all is loaded (see issue 13)

		// Get blade instance
		$blade = $app::view()->engine()->get_blade();
		// Assert the template path is correct.
		$this->assertEquals( \FIXTURES_PATH . 'views/', $blade->get_template_paths()[0] );

		// Assert the compiled path is correct.
		$path = \wp_upload_dir()['basedir'] . '/blade-cache';
		$this->assertEquals( $path, Objects::get_property( $blade, 'compiledPath' ) );
	}

}
