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
 * @package PinkCrab\BladeOne_Engine
 */

namespace PinkCrab\BladeOne;

use Dice\Dice;
use PinkCrab\Loader\Hook_Loader;
use eftec\bladeone\BladeOne as Blade;
use PinkCrab\BladeOne\BladeOne_Engine;
use PinkCrab\Perique\Application\Hooks;
use PinkCrab\Perique\Interfaces\Module;
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
	/** @var ?\Closure(BladeOne_Engine):BladeOne_Engine */
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
	 * @param \Closure(BladeOne_Engine):BladeOne_Engine $config
	 * @return self
	 */
	public function config( \Closure $config ): self {
		$this->config = $config;
		return $this;
	}

	/**
	 * Creates the shared instance of the module and defines the
	 * DI Rules to use the BladeOne_Engine.
	 *
	 * @pram App_Config $config
	 * @pram Hook_Loader $loader
	 * @pram DI_Container $di_container
	 * @return void
	 */
	public function pre_boot( App_Config $config, Hook_Loader $loader, DI_Container $di_container ): void {

		$wp_upload_dir = wp_upload_dir();
		$compiled_path = $this->compiled_path ?? sprintf( '%1$s%2$sblade-cache', $wp_upload_dir['basedir'], \DIRECTORY_SEPARATOR );
		$instance = new PinkCrab_BladeOne(
			$this->template_path ?? $config->path( 'view' ),
			$compiled_path,
			$this->mode
		);

		// Create the compilled path if it does not exist.
		if ( ! \file_exists( $compiled_path ) ) {
			mkdir( $compiled_path );
		}

		$di_container->addRule(
			BladeOne_Engine::class,
			array(
				'constructParams' => array(
					$instance,
				),
				'call'            => array(
					array( 'allow_pipe', array() ),
				),
			)
		);

		$di_container->addRule(
			Renderable::class,
			array(
				'instanceOf' => BladeOne_Engine::class,
				'shared'     => true,
			)
		);

		$di_container->addRule(
			View::class,
			array(
				'substitutions' => array(
					Renderable::class => BladeOne_Engine::class,
				),
			)
		);

	}

	/**
	 * Allows for the config to be passed to the provider, before its used.
	 *
	 * @pram App_Config $config
	 * @pram Hook_Loader $loader
	 * @pram DI_Container $di_container
	 * @return void
	 */
	public function pre_register( App_Config $config, Hook_Loader $loader, DI_Container $di_container ): void {

		// Pass the config to the provider, if set.
		if ( ! is_null( $this->config ) ) {
			$provider = $di_container->create( Renderable::class );

			// if we have an instance of BladeOne_Engine, pass the config.
			if ( $provider instanceof BladeOne_Engine ) {
				\call_user_func( $this->config, $provider );
			}
		}
	}

	## Unused methods


	/** @inheritDoc */
	public function post_register( App_Config $config, Hook_Loader $loader, DI_Container $di_container ): void {}

	/** @inheritDoc */
	public function get_middleware(): ?string {
		return null;
	}
}
