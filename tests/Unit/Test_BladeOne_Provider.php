<?php

declare(strict_types=1);

/**
 * Tests the BladeOne Provider.
 *
 * @since 0.1.0
 * @author Glynn Quelch <glynn.quelch@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @package PinkCrab\BladeOne
 */

namespace PinkCrab\BladeOne\Tests\Unit;

use WP_UnitTestCase;
use BadMethodCallException;
use eftec\bladeone\BladeOne;
use Gin0115\WPUnit_Helpers\Objects;
use PinkCrab\BladeOne\BladeOne_Engine;
use PinkCrab\Perique\Services\View\View;
use PinkCrab\BladeOne\Tests\Fixtures\Input;
use PinkCrab\Perique\Services\View\View_Model;
use PinkCrab\Perique\Services\View\Component\Component_Compiler;

/**
 * @group unit
 */
class Test_BladeOne_Engine extends WP_UnitTestCase {

	protected static $blade;

	public function set_Up(): void {
		parent::set_up();
		static::$blade = $this->get_engine();
	}

	public function get_engine(): BladeOne_Engine {
		$cache = \dirname( __FILE__, 2 ) . '/files/cache';
		$views = \dirname( __FILE__, 2 ) . '/files/views';
		return BladeOne_Engine::init( $views, $cache, 5 );
	}

	/**
	 * Test is intance of bladeone
	 *
	 * @return void
	 */
	public function test_can_construct_from_provider(): void {
		$this->assertInstanceOf( BladeOne_Engine::class, static::$blade );
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

	/**
	 * Test can call an instanced method.
	 *
	 * @return void
	 */
	public function test_can_call_instanced_methods(): void {
		$this->assertStringContainsString(
			'testview.blade.php',
			static::$blade->getTemplateFile( 'testview' )
		);
	}

	/**
	 * Tests BadMethodCallException thrown is static methods called as instanced.
	 * $this->staticMethod()
	 *
	 * @return void
	 */
	public function test_throws_exception_on_static_call_as_instanced(): void {
		$this->expectException( BadMethodCallException::class );
		static::$blade->enq( '1' );
	}

	/**
	 * Tests BadMethodCallException thrown if method doesnt exist.
	 *
	 * @return void
	 */
	public function test_throws_exception_on_invalid_method_instanced(): void {
		$this->expectException( BadMethodCallException::class );
		static::$blade->FAKE( '1' );
	}

	/**
	 * Test can call an instanced method.
	 *
	 * @return void
	 */
	public function test_can_call_static_methods(): void {
		$this->assertStringContainsString(
			'testview',
			static::$blade::enq( 'testview<p>d</p>' )
		);
	}

	/**
	 * Tests BadMethodCallException thrown is static methods called as instanced.
	 * $this->staticMethod()
	 *
	 * @return void
	 */
	public function test_throws_exception_on_instanced_call_as_static(): void {
		$this->expectException( BadMethodCallException::class );
		static::$blade::getTemplateFile( '1' );
	}

	/**
	 * Tests BadMethodCallException thrown if method doesnt exist.
	 *
	 * @return void
	 */
	public function test_throws_exception_on_invalid_method_static(): void {
		$this->expectException( BadMethodCallException::class );
		static::$blade::FAKE( '1' );
	}

	public function test_can_use_html_trait(): void {
		$this->expectOutputRegex( '/<button/' );
		$this->expectOutputRegex( '/New Component/' );
		static::$blade->render( 'testhtml', array( 'foo' => 'rendered' ), View::PRINT_VIEW );
	}

	/** @testdox It should be possible to define if blade templates should be allowed to pipe values through callables. */
	public function test_allow_pipe(): void {
		$provider = $this->get_engine();

		// Inferred as true.
		$provider->allow_pipe();
		$this->assertTrue( $provider->get_blade()->pipeEnable );

		// Set as false (this is by default too)
		$provider->allow_pipe( false );
		$this->assertFalse( $provider->get_blade()->pipeEnable );

		// Verbose true
		$provider->allow_pipe( true );
		$this->assertTrue( $provider->get_blade()->pipeEnable );
	}

	/** @testdox It should be possible to define a blade directive from the provider. */
	public function test_add_directive(): void {
		$provider = $this->get_engine();
		$provider->directive(
			'foo',
			function( $expression ) {
				return "<?php echo {$expression};?>";
			}
		);

		$blade = $provider->get_blade();
		$this->assertCount( 3, Objects::get_property( $blade, 'customDirectives' ) );
		$this->assertArrayHasKey( 'foo', Objects::get_property( $blade, 'customDirectives' ) );
	}

	/** @testdox It should be possible to define a blade directive from the provider. */
	public function test_add_directive_rt(): void {
		$provider = $this->get_engine();
		$provider->directive_rt(
			'bar',
			function( $expression ) {
				return "<?php echo {$expression};?>";
			}
		);

		$blade = $provider->get_blade();
		$this->assertCount( 3, Objects::get_property( $blade, 'customDirectivesRT' ) );
		$this->assertArrayHasKey( 'bar', Objects::get_property( $blade, 'customDirectivesRT' ) );
	}

	/** @testdox It should be possible to define an include alias from the provider */
	public function test_add_include(): void {
		$provider = $this->get_engine();
		$provider->add_include( 'view.admin.bar', 'adminBar' );
		$blade      = $provider->get_blade();
		$directives = Objects::get_property( $blade, 'customDirectives' );
		$this->assertArrayHasKey( 'adminBar', $directives );

		// Use reflection to access closure
		$func = new \ReflectionFunction( $directives['adminBar'] );

		$this->assertSame( $blade, $func->getClosureThis() );
		$this->assertArrayHasKey( 'view', $func->getStaticVariables() );
		$this->assertEquals( 'view.admin.bar', $func->getStaticVariables()['view'] );
	}

	/** @testdox It should be possible to set a class alias from the provider */
	public function test_add_alias_class(): void {
		$provider = $this->get_engine();
		$provider->add_alias_classes( 'self', BladeOne_Engine::class );
		$blade = $provider->get_blade();
		$this->assertEquals( BladeOne_Engine::class, $blade->aliasClasses['self'] );
		$this->assertArrayHasKey( 'self', $blade->aliasClasses );
	}

	/** @testdox It should be possible to set the mode blade renders using. */
	public function test_set_mode(): void {
		$provider = $this->get_engine();
		$provider->set_mode( BladeOne::MODE_AUTO );
		$this->assertEquals( 0, $provider->get_blade()->getMode() );

		$provider->set_mode( BladeOne::MODE_DEBUG );
		$this->assertEquals( 5, $provider->get_blade()->getMode() );

		$provider->set_mode( BladeOne::MODE_FAST );
		$this->assertEquals( 2, $provider->get_blade()->getMode() );

		$provider->set_mode( BladeOne::MODE_SLOW );
		$this->assertEquals( 1, $provider->get_blade()->getMode() );
	}

	/** @testdox It should be possible to share a value globally between al templates. */
	public function test_share(): void {
		$provider = $this->get_engine();
		$provider->share( 'foo', 'bar' );
		$blade = $provider->get_blade();

		$this->assertArrayHasKey( 'foo', Objects::get_property( $blade, 'variablesGlobal' ) );
		$this->assertEquals( 'bar', Objects::get_property( $blade, 'variablesGlobal' )['foo'] );
	}

	/** @testdox It should be possible to set a resolver for injecting into bladeone */
	public function test_set_inject_resolver(): void {
		$provider = $this->get_engine();
		$provider->set_inject_resolver( '__return_false' );
		$blade = $provider->get_blade();
		$this->assertEquals( '__return_false', Objects::get_property( $blade, 'injectResolver' ) );
	}

	/** @testdox It should be possible to set a custom file extension for templates from the provider */
	public function test_set_file_extension(): void {
		$provider = $this->get_engine();
		$provider->set_file_extension( '.tree' );
		$this->assertEquals( '.tree', $provider->get_blade()->getFileExtension() );
	}

	/** @testdox It should be possible to set a custom file extension for compiled views from the provider */
	public function test_set_compiled_extension(): void {
		$provider = $this->get_engine();
		$provider->set_compiled_extension( '.bar' );
		$this->assertEquals( '.bar', $provider->get_blade()->getCompiledExtension() );
	}

	/** @testdox It should be possible to render a Component */
	public function test_can_render_component(): void {
		$compiler = new Component_Compiler( 'components' );
		$provider = $this->get_engine();
		$provider->set_component_compiler( $compiler );

		$input = $provider->component(
			new Input(
				'input_name',
				'input_id',
				'input_value',
				'text'
			),
			false
		);

		$this->assertEquals(
			'<input name="input_name" id="input_id" value="input_value" type="text" />',
			$input
		);
	}

	/** @testdox It should be possible to render a View Model */
	public function test_can_render_view_model(): void {
		$provider = $this->get_engine();

		$input = $provider->view_model(
			new View_Model(
				'components.input',
				array(
					'name'  => 'input_name',
					'id'    => 'input_id',
					'value' => 'input_value',
					'type'  => 'text',
				)
			),
			false
		);

		$this->assertEquals(
			'<input name="input_name" id="input_id" value="input_value" type="text" />',
			$input
		);
	}

	/** @testdox An exception should be thrown attempting to render a component with the compiler being set to the provider. */
	public function test_exception_rendering_component_with_compiler_set(): void {
		$provider = $this->get_engine();

		$this->expectException( \Exception::class );
		$this->expectExceptionMessage( 'No component compiler passed to BladeOne' );

		$provider->component(
			new Input(
				'input_name',
				'input_id',
				'input_value',
				'text'
			),
			false
		);
	}

	/** @testdox When calling BladeOne::e() the custom esc function should be called. */
	public function test_esc_function_called_when_calling_bladeone_e(): void {
		$provider = $this->get_engine();
		$provider->set_esc_function( 'foo_esc' );
		$blade = $provider->get_blade();

		$this->assertEquals( 'foo', $blade::e( 'fff' ) );
		$this->assertEquals( 'foo', $blade::e( 11 ) );
		$this->assertEquals( 'foo', $blade::e( 1.45 ) );
		$this->assertEquals( 'foo', $blade::e( array( 'fff', 'bar' ) ) );
		$this->assertEquals( 'foo', $blade::e( (object) array( 'fff', 'bar' ) ) );

		// Null should not be escaped.
		$this->assertEquals( '', $blade::e( null ) );
	}

	/** @testdox Attempting to set a none callable string as the esc function an exception should be thrown */
	public function test_exception_setting_non_callable_esc_function(): void {
		$this->expectException( \Exception::class );
		$this->expectExceptionMessage( 'Invalid esc function provided' );

		$this->get_engine()->set_esc_function( 'foo' );
	}

	/** @testdox By default the viewModel and component directives should be included. */
	public function test_view_model_and_component_directives_included_by_default(): void {
		$provider = $this->get_engine();

		$blade = $provider->get_blade();
		$this->assertArrayHasKey( 'component', Objects::get_property( $blade, 'customDirectivesRT' ) );
		$this->assertArrayHasKey( 'viewModel', Objects::get_property( $blade, 'customDirectivesRT' ) );
	}

	/** @testdox It should be possible to get the view base path if defined as a single value or first of an array */
	public function test_get_view_base_path(): void {
		// Single path
		$this->assertEquals( 'foo.bar', BladeOne_Engine::init('foo.bar')->base_view_path() );

		// Array of paths
		$this->assertEquals( 'bar.foo', BladeOne_Engine::init( array( 'bar.foo', 'foo.bar' ) )->base_view_path() );
	}

	/** @testdox It should be possible to access all paths used for templates. */
	public function test_get_view_paths(): void {
		$blade = BladeOne_Engine::init( array( 'bar.foo', 'foo.bar' ) );
		$paths = $blade->get_blade()->get_template_paths();

		$this->assertIsArray( $paths );
		$this->assertCount( 2, $paths );
		$this->assertContains( 'bar.foo', $paths );
		$this->assertContains( 'foo.bar', $paths );
	}
}
