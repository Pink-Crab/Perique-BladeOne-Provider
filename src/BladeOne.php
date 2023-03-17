<?php

declare( strict_types=1 );

/**
 * The BladeOne Module for Perique.
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
 * @package PinkCrab\BladeOne_Provider
 */

namespace PinkCrab\BladeOne;

use Dice\Dice;
use PinkCrab\Loader\Hook_Loader;
use eftec\bladeone\BladeOne as Blade;
use PinkCrab\Perique\Application\Hooks;
use PinkCrab\Perique\Interfaces\Module;
use PinkCrab\BladeOne\BladeOne_Provider;
use PinkCrab\BladeOne\PinkCrab_BladeOne;
use PinkCrab\Perique\Interfaces\Renderable;
use PinkCrab\Perique\Application\App_Config;
use PinkCrab\Perique\Interfaces\DI_Container;
use PinkCrab\Perique\Services\View\PHP_Engine;
use PinkCrab\BladeOne\Abstract_BladeOne_Config;

class BladeOne implements Module {

	private ?string $template_path = null;
	private ?string $compiled_path = null;
	private int $mode              = PinkCrab_BladeOne::MODE_AUTO;
	private string $blade          = PinkCrab_BladeOne::class;
	/** @var ?\Closure(BladeOne_Provider):BladeOne_Provider */
	private $config = null;

	/**
	 * Set the template path.
	 *
	 * @param string $template_path
	 * @return self
	 */
	public function template_path( string $template_path ): self {
		$this->template_path = $template_path;
		return $this;
	}

	/**
	 * Set the compiled path.
	 *
	 * @param string $compiled_path
	 * @return self
	 */
	public function compiled_path( string $compiled_path ): self {
		$this->compiled_path = $compiled_path;
		return $this;
	}

	/**
	 * Set the mode.
	 *
	 * @param int $mode
	 * @return self
	 */
	public function mode( int $mode ): self {
		$this->mode = $mode;
		return $this;
	}

	/**
	 * Provider config.
	 *
	 * @param \Closure(BladeOne_Provider):BladeOne_Provider $config
	 * @return self
	 */
	public function config( \Closure $config ): self {
		$this->config = $config;
		return $this;
	}

	/**
	 * Set the blade class.
	 *
	 * @param class-string<Blade> $blade
	 * @return self
	 */
	public function blade( string $blade ): self {
		// Must be an instance of Blade or a child of Blade.
		if ( ! is_subclass_of( $blade, Blade::class ) ) {
			throw new \InvalidArgumentException( 'BladeOne must be an instance of eftec\bladeone\BladeOne or a child of eftec\bladeone\BladeOne' );
		}

		$this->blade = $blade;
		return $this;
	}

	/**
	 * Callback fired before the Application is booted.
	 *
	 * @pram App_Config $config
	 * @pram Hook_Loader $loader
	 * @pram DI_Container $di_container
	 * @return void
	 */
	public function pre_boot( App_Config $config, Hook_Loader $loader, DI_Container $di_container ): void {
		add_filter(
			Hooks::APP_INIT_SET_DI_RULES,
			function( $rules ) use ( $config ) {

				// Unset the global PHP_Engine useage.
				if ( array_key_exists( '*', $rules )
				&& array_key_exists( 'substitutions', $rules['*'] )
				&& array_key_exists( Renderable::class, $rules['*']['substitutions'] )
				&& is_a( $rules['*']['substitutions'][ Renderable::class ], PHP_Engine::class ) ) {

					// If template path is not set, get from renderable.
					if ( is_null( $this->template_path ) ) {
						$this->template_path = $rules['*']['substitutions'][ Renderable::class ]->base_view_path();
					}
					unset( $rules['*']['substitutions'][ Renderable::class ] );
				}

				// If there is no compiled path, set to to uploads.
				if ( is_null( $this->compiled_path ) ) {
					$this->compiled_path = sprintf(
						'%1$s%2$scompiled%2$sblade',
						$config->path( 'upload_root' ),
						\DIRECTORY_SEPARATOR
					);
				}

				// Get the version of Blade to start.
				$blade = $this->blade;

				$rules[ BladeOne_Provider::class ] = array(
					'substitutions' => array(
						PinkCrab_BladeOne::class => new $blade( $this->template_path, $this->compiled_path, $this->mode ),
					),
					'call'          => array(
						array( 'allow_pipe', array() ),
					),
				);

				$rules[ Renderable::class ] = array(
					'instanceOf' => BladeOne_Provider::class,
					'shared'     => true,
				);

				$rules[ Abstract_BladeOne_Config::class ] = array(
					'call' => array(
						array( 'set_renderable', array( array( Dice::INSTANCE => Renderable::class ) ) ),
					),
				);

				return $rules;
			}
		);
	}

	## Unused methods

	/** @inheritDoc */
	public function pre_register( App_Config $config, Hook_Loader $loader, DI_Container $di_container ): void {

		// Pass the config to the provider, if set.
		if ( ! is_null( $this->config ) ) {
			$provider = $di_container->create( Renderable::class );

			// if we have an instance of BladeOne_Provider, pass the config.
			if ( $provider instanceof BladeOne_Provider ) {
				\call_user_func( $this->config, $provider );
			}
		}
	}

	/** @inheritDoc */
	public function post_register( App_Config $config, Hook_Loader $loader, DI_Container $di_container ): void {}

	/** @inheritDoc */
	public function get_middleware(): ?string {
		return null;
	}
}
