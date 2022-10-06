@use(PinkCrab\Perique\Services\View\View_Model)
{{ $this->view_model(new View_Model('testview', ['foo' => 'woo'])) }}
