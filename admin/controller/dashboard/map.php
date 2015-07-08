<?php
class ControllerDashboardMap extends Controller {
	public function index() {
		$this->data = $this->load->language('dashboard/map');

		$this->data['token'] = $this->session->data['token'];
				
		return $this->load->view('dashboard/map.tpl', $this->data);
	}
	
	public function map() {
		$json = array();
		
		$this->load->model('report/sale');
		
		$results = $this->model_report_sale->getTotalOrdersByCountry();
		
		foreach ($results as $result) {
			$json[strtolower($result['iso_code_2'])] = array(
				'total'  => $result['total'],
				'amount' => $this->currency->format($result['amount'], $this->config->get('currency_code'))
			);
		}
	
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}