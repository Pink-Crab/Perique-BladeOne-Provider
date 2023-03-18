<?php

declare( strict_types=1 );

/**
 * Implementation of BladeOne for the PinkCrab Perique frameworks Renderable interface
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @author Glynn Quelch <glynn.quelch@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @package PinkCrab\BladeOne_Engine
 */

namespace PinkCrab\BladeOne;

use Exception;
use ReflectionClass;
use BadMethodCallException;
use eftec\bladeone\BladeOne;
use eftec\bladeonehtml\BladeOneHtml;
use PinkCrab\BladeOne\PinkCrab_BladeOne;
use PinkCrab\Perique\Interfaces\Renderable;
use PinkCrab\Perique\Services\View\View_Model;
use PinkCrab\Perique\Services\View\Component\Component;
use PinkCrab\Perique\Services\View\Component\Component_Compiler;

class BladeOne_Engine implements Renderable {

	/**
	 * BladeOne Instance
	 *
	 * @var PinkCrab_BladeOne
	 */
	protected static $blade;

	/**
	 * Access to the component compiler.
	 *
	 * @var Component_Compiler
	 */
	protected $component_compiler;

	/**
	 * Creates an instance with blade one.
	 *
	 * @param PinkCrab_BladeOne $blade
	 */
	final public function __construct( PinkCrab_BladeOne $blade ) {
		static::$blade = $blade;
	}

	/**
	 * Static constructor with BladeOne initialisation details
	 *
	 * @param string|array<mixed> $template_path If null then it uses (caller_folder)/views
	 * @param string $compiled_path If null then it uses (caller_folder)/compiles
	 * @param int $mode =[BladeOne::MODE_AUTO,BladeOne::MODE_DEBUG,BladeOne::MODE_FAST,BladeOne::MODE_SLOW][$i]
	 * @return self
	 */
	public static function init(
		$template_path = null,
		?string $compiled_path = null,
		int $mode = 0
	): self {
		return new static( new PinkCrab_BladeOne( $template_path, $compiled_path, $mode ) );
	}

	/**
	 * Returns the current BladeOne instance.
	 *
	 * @return BladeOne
	 */
	public function get_blade(): BladeOne {
		return static::$blade;
	}

	/**
	 * Returns the base view path.
	 *
	 * @return string
	 * @since 1.4.0
	 */
	public function base_view_path(): string {
		$paths = static::$blade->get_template_paths();
		return ! empty( $paths ) ? reset( $paths ) : '';
	}

	/**
	 * Sets the esc function.
	 *
	 * @param string $esc
	 * @return self
	 */
	public function set_esc_function( string $esc ): self {
		static::$blade->set_esc_function( $esc );
		return $this;
	}

	/**
	 * Sets the component compiler.
	 *
	 * @param Component_Compiler $compiler
	 * @return void
	 */
	public function set_component_compiler( Component_Compiler $compiler ): void {
		$this->component_compiler = $compiler;
	}

	/**
	 * Display a view and its context.
	 *
	 * @param string $view
	 * @param iterable<string, mixed> $data
	 * @param bool $print
	 * @return void|string
	 */
	public function render( string $view, iterable $data, bool $print = true ) {
		if ( $print ) {
			print static::$blade->run( $view, (array) $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} else {
			return static::$blade->run( $view, (array) $data );
		}
	}

	/**
	 * Renders a view Model
	 *
	 * @param View_Model $view_model
	 * @return string|void
	 */
	public function view_model( View_Model $view_model, bool $print = true ) {
		return $this->render( str_replace( array( '/', '\\' ), '.', $view_model->template() ), $view_model->data(), $print );
	}

		/**
	 * Renders a component.
	 *
	 * @param Component $component
	 * @return string|void
	 */
	public function component( Component $component, bool $print = true ) {

		// Throw exception of no compiler passed.
		if ( ! is_a( $this->component_compiler, Component_Compiler::class ) ) {
			throw new Exception( 'No component compiler passed to BladeOne' );
		}

		// Compile the component.
		$compiled = $this->component_compiler->compile( $component );
		return $this->render( str_replace( array( '/', '\\' ), '.', $compiled->template() ), $compiled->data(), $print );
	}

	/**
	 * magic instanced method caller.
	 *
	 * @param string $method
	 * @param array<mixed> $args
	 * @return mixed
	 * @throws BadMethodCallException
	 */
	public function __call( string $method, array $args = array() ) {
		if ( ! $this->is_method( $method ) ) {
			throw new BadMethodCallException( "{$method} is not a valid method on the BladeOne instance." );
		}

		return static::$blade->{$method}( ...$args );
	}

	/**
	 * Magic static method caller.
	 *
	 * @param string $method
	 * @param array<mixed> $args
	 * @return mixed
	 * @throws BadMethodCallException
	 */
	public static function __callStatic( string $method, array $args = array() ) {
		if ( ! static::is_static_method( $method ) ) {
			throw new BadMethodCallException( "{$method} is not a valid method on the BladeOne instance." );
		}

		return static::$blade::{$method}( ...$args );
	}

	/**
	 * Checks if the passed method exists, is public and isnt static.
	 *
	 * @param string $method
	 * @return bool
	 */
	protected function is_method( string $method ): bool {
		$class_reflection = new ReflectionClass( static::$blade );

		// Check method exists.
		if ( ! $class_reflection->hasMethod( $method ) ) {
			return false;
		}

		$method_reflection = $class_reflection->getMethod( $method );

		return $method_reflection->isPublic() && ! $method_reflection->isStatic();
	}

	/**
	 * Checks if the passed method exists, is public and IS static.
	 *
	 * @param string $method
	 * @return bool
	 */
	protected static function is_static_method( string $method ): bool {
		$class_reflection = new ReflectionClass( static::$blade );

		// Check method exists.
		if ( ! $class_reflection->hasMethod( $method ) ) {
			return false;
		}

		$method_reflection = $class_reflection->getMethod( $method );
		return $method_reflection->isPublic() && $method_reflection->isStatic();
	}

	/**
	 * Sets if piping is enabled in templates.
	 *
	 * @param bool $bool
	 * @return self
	 */
	public function allow_pipe( bool $bool = true ): self {
		static::$blade->pipeEnable = $bool; //phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		return $this;
	}

	/**
	 * Register a handler for custom directives.
	 *
	 * @param string   $name
	 * @param callable $handler
	 * @return self
	 */
	public function directive( string $name, callable $handler ): self {
		static::$blade->directive( $name, $handler );
		return $this;
	}

	/**
	 * Register a handler for custom directives for run at runtime
	 *
	 * @param string   $name
	 * @param callable $handler
	 * @return self
	 */
	public function directive_rt( $name, callable $handler ): self {
		static::$blade->directiveRT( $name, $handler );
		return $this;
	}

	/**
	 * Define a template alias
	 *
	 * @param string      $view  example "folder.template"
	 * @param string|null $alias example "mynewop". If null then it uses the name of the template.
	 * @return self
	 */
	public function add_include( $view, $alias = null ): self {
		static::$blade->addInclude( $view, $alias );
		return $this;
	}

	/**
	 * Define a class with a namespace
	 *
	 * @param string $alias_name
	 * @param string $class_with_namespace
	 * @return self
	 */
	public function add_alias_classes( $alias_name, $class_with_namespace ): self {
		static::$blade->addAliasClasses( $alias_name, $class_with_namespace );
		return $this;
	}

	/**
	 * Set the compile mode
	 *
	 * @param int $mode BladeOne::MODE_AUTO, BladeOne::MODE_DEBUG, BladeOne::MODE_FAST, BladeOne::MODE_SLOW
	 * @return self
	 */
	public function set_mode( int $mode ): self {
		static::$blade->setMode( $mode );
		return $this;
	}

	/**
	 * Adds a global variable. If <b>$var_name</b> is an array then it merges all the values.
	 * <b>Example:</b>
	 * <pre>
	 * $this->share('variable',10.5);
	 * $this->share('variable2','hello');
	 * // or we could add the two variables as:
	 * $this->share(['variable'=>10.5,'variable2'=>'hello']);
	 * </pre>
	 *
	 * @param string|array<string, mixed> $var_name It is the name of the variable or it is an associative array
	 * @param mixed        $value
	 * @return $this
	 */
	public function share( $var_name, $value = null ): self {
		static::$blade->share( $var_name, $value );
		return $this;
	}

	/**
	 * Sets the function used for resolving classes with inject.
	 *
	 * @param callable $function
	 * @return $this
	 */
	public function set_inject_resolver( callable $function ): self {
		static::$blade->setInjectResolver( $function );
		return $this;
	}

	/**
	 * Set the file extension for the template files.
	 * It must includes the leading dot e.g. .blade.php
	 *
	 * @param string $file_extension Example: .prefix.ext
	 * @return $this
	 */
	public function set_file_extension( string $file_extension ): self {
		static::$blade->setFileExtension( $file_extension );
		return $this;
	}

	/**
	 * Set the file extension for the compiled files.
	 * Including the leading dot for the extension is required, e.g. .bladec
	 *
	 * @param string $file_extension
	 * @return $this
	 */
	public function set_compiled_extension( string $file_extension ): self {
		static::$blade->setCompiledExtension( $file_extension );
		return $this;
	}

}

