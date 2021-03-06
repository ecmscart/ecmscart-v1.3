<?php
class ControllerReportAffiliateActivity extends Controller {
	private $url_data = array(
				'filter_affiliate' ,
				'filter_ip' ,
				'filter_date_start',
				'filter_date_end',
				'page'
			);
	public function index() {
		$this->data = $this->load->language('report/affiliate_activity');

		$this->document->setTitle($this->data['heading_title']);
		
		$filter_affiliate = $this->request->get('filter_affiliate', null);
		
		$filter_ip = $this->request->get('filter_ip', null);
		
		$filter_date_start = $this->request->get('filter_date_start', '');
		
		$filter_date_end = $this->request->get('filter_date_end', '');
		
		$page = $this->request->get('page', 1);
		
		$url = $this->request->getUrl($this->url_data);
		
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['heading_title'],	// Text to display link
							$this->url->link('report/affiliate_activity', 'token=' . $this->session->data['token'] . $url, 'SSL')	// Link URL
						));
		
		$this->load->model('report/affiliate');

		$this->data['activities'] = array();

		$filter_data = array(
			'filter_affiliate'   => $filter_affiliate,
			'filter_ip'         => $filter_ip,
			'filter_date_start'	=> $filter_date_start,
			'filter_date_end'	=> $filter_date_end,
			'start'             => ($page - 1) * 20,
			'limit'             => 20
		);

		$activity_total = $this->model_report_affiliate->getTotalAffiliateActivities($filter_data);

		$results = $this->model_report_affiliate->getAffiliateActivities($filter_data);

		foreach ($results as $result) {
			$comment = vsprintf($this->data['text_' . $result['key']], unserialize($result['data']));

			$this->data['activities'][] = array(
				'comment'    => str_replace('affiliate_id=', $this->url->link('marketing/affiliate/save', 'token=' . $this->session->data['token'] . '&affiliate_id=', 'SSL'), $comment),
				'ip'         => $result['ip'],
				'date_added' => date($this->data['datetime_format'], strtotime($result['date_added']))
			);
		}

		$this->data['token'] = $this->session->data['token'];
		
		$url_data = array(
				'filter_affiliate' ,
				'filter_ip' ,
				'filter_date_start',
				'filter_date_end',
			);
		$url = $this->request->getUrl($url_data);

		$pagination = new Pagination();
		$pagination->total = $activity_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('report/affiliate_activity', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->data['results'] = sprintf($this->data['text_pagination'], ($activity_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($activity_total - $this->config->get('config_limit_admin'))) ? $activity_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $activity_total, ceil($activity_total / $this->config->get('config_limit_admin')));

		$this->data['filter_affiliate'] = $filter_affiliate;
		$this->data['filter_ip'] = $filter_ip;
		$this->data['filter_date_start'] = $filter_date_start;
		$this->data['filter_date_end'] = $filter_date_end;

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('report/affiliate_activity.tpl', $this->data));
	}
}