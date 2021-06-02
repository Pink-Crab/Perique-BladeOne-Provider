<?php

declare( strict_types=1 );

/**
 * A simple wrapper for getting and sanitizing all http requests.
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
 * @package PinkCrab\Registerables
 */

namespace PinkCrab\BladeOne;

use ReflectionClass;
use BadMethodCallException;
use eftec\bladeone\BladeOne;
use eftec\bladeonehtml\BladeOneHtml;
use PinkCrab\Perique\Interfaces\Renderable;

class BladeOne_Provider implements Renderable {

	/**
	 * BladeOne Instance
	 *
	 * @var BladeOne
	 */
	protected static $blade;

	/**
	 * Creates an instance with blade one.
	 *
	 * @param BladeOne $blade
	 */
	final protected function __construct( BladeOne $blade ) {
		static::$blade = $blade;
	}

	/**
	 * Static constructor with BladeOne initalsation details
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
		$blade = new class() extends BladeOne{
			use BladeOneHtml;
		};

		return new static( new $blade( $template_path, $compiled_path, $mode ) );
	}

	/**
	 * Returns the current BladeOne isntance.
	 *
	 * @return BladeOne
	 */
	public function get_blade(): BladeOne {
		return static::$blade;
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

}

