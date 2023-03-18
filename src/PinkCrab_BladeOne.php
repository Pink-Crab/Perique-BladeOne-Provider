<?php

declare( strict_types=1 );

/**
 * Wrapper for BladeOne with HTML enabled
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

use php_user_filter;
use eftec\bladeone\BladeOne;
use eftec\bladeonehtml\BladeOneHtml;
use PinkCrab\Perique\Application\App;
use PinkCrab\Perique\Services\View\View;
use PinkCrab\Perique\Services\View\View_Model;
use PinkCrab\Perique\Services\View\Component\Component;

class PinkCrab_BladeOne extends BladeOne {
	use BladeOneHtml;

	/**
	 * Bob the constructor.
	 * The folder at $compiled_path is created in case it doesn't exist.
	 *
	 * @param string|string[] $template_path If null then it uses (caller_folder)/views
	 * @param string          $compiled_path If null then it uses (caller_folder)/compiles
	 * @param int             $mode         =[BladeOne::MODE_AUTO,BladeOne::MODE_DEBUG,BladeOne::MODE_FAST,BladeOne::MODE_SLOW][$i]
	 */
	public function __construct( $template_path = null, $compiled_path = null, $mode = 0 ) {
		parent::__construct( $template_path, $compiled_path, $mode );

		// Add the viewModel directive.
		$this->directiveRT(
			'viewModel',
			function( $expression ) {
				return $this->view_model( $expression, true );
			}
		);

		// Add the component directive.
		$this->directiveRT(
			'component',
			function( $expression ) {
				return $this->component( $expression, true );
			}
		);
	}

	/**
	 * The esc function to use
	 *
	 * @var callable(mixed):string
	 */
	protected static $esc_function = 'esc_html';

	/**
	 * The default echo format
	 *
	 * @var string
	 */
	protected $echoFormat = '\esc_html(%s)'; //phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase


	/**
	 * Sets the esc function to use
	 *
	 * @param string $esc_function
	 * @return void
	 */
	public function set_esc_function( string $esc_function ): void {
		// Throw exception if not a valid callable.
		if ( ! \is_callable( $esc_function ) ) {
			throw new \InvalidArgumentException( 'Invalid esc function provided.' );
		}

		static::$esc_function = $esc_function;
		$this->echoFormat     = sprintf( '\\%s(%%s)', $esc_function ); //phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
	}

	/**
	 * Returns the template paths
	 *
	 * @return string[]
	 */
	public function get_template_paths(): array {
		return $this->templatePath; //phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
	}

	/**
	 * Escape HTML entities in a string.
	 *
	 * @param int|float|string|null|mixed[]|object $value
	 * @return string
	 */
	public static function e( $value ): string {
		if ( \is_null( $value ) ) {
			return '';
		}
		if ( \is_array( $value ) || \is_object( $value ) ) {
			return \call_user_func( static::$esc_function, \print_r( $value, true ) );//phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
		}
		if ( \is_numeric( $value ) ) {
			$value = (string) $value;
		}
		return \call_user_func( static::$esc_function, $value );
	}

	/**
	 * Renders  component
	 *
	 * @param Component $component
	 * @param bool $print
	 * @return string|void
	 */
	public function component( Component $component, bool $print = true ) {
		/** @var View */
		$view = App::view();

		return $view->component( $component, $print );
	}

	/**
	 * Renders a view model
	 *
	 * @param View_Model $view_model
	 * @param bool $print Print or Return the HTML
	 * @return string|void
	 */
	public function view_model( View_Model $view_model, bool $print = true ) {
		/** @var View */
		$view = App::view();

		return $view->view_model( $view_model, $print );
	}


}
