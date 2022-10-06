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
 * @package PinkCrab\BladeOne_Provider
 */

namespace PinkCrab\BladeOne;

use eftec\bladeone\BladeOne;
use eftec\bladeonehtml\BladeOneHtml;
use PinkCrab\Perique\Application\App;
use PinkCrab\Perique\Services\View\View;
use PinkCrab\Perique\Services\View\View_Model;
use PinkCrab\Perique\Services\View\Component\Component;

class PinkCrab_BladeOne extends BladeOne {
	use BladeOneHtml;

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
