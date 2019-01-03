<?php
class ControllerExtensionPaymentQuanqiuPay extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/payment/quanqiupay');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('quanqiupay', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=payment', true));
		}

		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_edit'] = $this->language->get('text_edit');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');

		$data['entry_applicationid'] = $this->language->get('entry_applicationid');
		$data['entry_secretkey'] = $this->language->get('entry_secretkey');
		$data['entry_gateway'] = $this->language->get('entry_gateway');
		$data['entry_order_prefix'] = $this->language->get('entry_order_prefix');
		$data['entry_mode'] = $this->language->get('entry_mode');
		$data['entry_order_status'] = $this->language->get('entry_order_status');
		$data['entry_order_notifystatus'] = $this->language->get('entry_order_notifystatus');
		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_sort_order'] = $this->language->get('entry_sort_order');

		$data['help_total'] = $this->language->get('help_total');

		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['applicationid'])) {
			$data['error_applicationid'] = $this->error['applicationid'];
		} else {
			$data['error_applicationid'] = '';
		}

		if (isset($this->error['secretkey'])) {
			$data['error_secretkey'] = $this->error['secretkey'];
		} else {
			$data['error_secretkey'] = '';
		}

		if (isset($this->error['gateway'])) {
			$data['error_gateway'] = $this->error['gateway'];
		} else {
			$data['error_gateway'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_payment'),
            'href' => $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=payment', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/payment/quanqiupay', 'token=' . $this->session->data['token'], true)
		);

		$data['action'] = $this->url->link('extension/payment/quanqiupay', 'token=' . $this->session->data['token'], true);
        $data['cancel'] = $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=payment', true);

		if (isset($this->request->post['quanqiupay_applicationid'])) {
			$data['quanqiupay_applicationid'] = $this->request->post['quanqiupay_applicationid'];
		} else {
			$data['quanqiupay_applicationid'] = $this->config->get('quanqiupay_applicationid');
		}

		if (isset($this->request->post['quanqiupay_secretkey'])) {
			$data['quanqiupay_secretkey'] = $this->request->post['quanqiupay_secretkey'];
		} else {
			$data['quanqiupay_secretkey'] = $this->config->get('quanqiupay_secretkey');
		}

		if (isset($this->request->post['quanqiupay_gateway'])) {
			$data['quanqiupay_gateway'] = $this->request->post['quanqiupay_gateway'];
		} else {
			$data['quanqiupay_gateway'] = $this->config->get('quanqiupay_gateway');
		}

		if (isset($this->request->post['quanqiupay_order_prefix'])) {
			$data['quanqiupay_order_prefix'] = $this->request->post['quanqiupay_order_prefix'];
		} else {
			$data['quanqiupay_order_prefix'] = $this->config->get('quanqiupay_order_prefix');
		}

		if (isset($this->request->post['quanqiupay_mode'])) {
			$data['quanqiupay_mode'] = $this->request->post['quanqiupay_mode'];
		} else {
			$data['quanqiupay_mode'] = $this->config->get('quanqiupay_mode');
		}

		if (isset($this->request->post['quanqiupay_order_status_id'])) {
			$data['quanqiupay_order_status_id'] = $this->request->post['quanqiupay_order_status_id'];
		} else {
			$data['quanqiupay_order_status_id'] = $this->config->get('quanqiupay_order_status_id');
		}

		if (isset($this->request->post['quanqiupay_order_notify_status_id'])) {
			$data['quanqiupay_order_notify_status_id'] = $this->request->post['quanqiupay_order_notify_status_id'];
		} else {
			$data['quanqiupay_order_notify_status_id'] = $this->config->get('quanqiupay_order_notify_status_id');
		}

		$this->load->model('localisation/order_status');

		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		if (isset($this->request->post['quanqiupay_status'])) {
			$data['quanqiupay_status'] = $this->request->post['quanqiupay_status'];
		} else {
			$data['quanqiupay_status'] = $this->config->get('quanqiupay_status');
		}

		if (isset($this->request->post['quanqiupay_sort_order'])) {
			$data['quanqiupay_sort_order'] = $this->request->post['quanqiupay_sort_order'];
		} else {
			$data['quanqiupay_sort_order'] = $this->config->get('quanqiupay_sort_order');
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/payment/quanqiupay', $data));
	}

    public function install() {
        $this->load->model('extension/payment/quanqiupay');
        $this->model_extension_payment_quanqiupay->install();
    }

    public function uninstall() {
        $this->load->model('extension/payment/quanqiupay');
        $this->model_extension_payment_quanqiupay->uninstall();
    }

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/payment/quanqiupay')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->request->post['quanqiupay_applicationid']) {
			$this->error['applicationid'] = $this->language->get('error_applicationid');
		}

		if (!$this->request->post['quanqiupay_secretkey']) {
			$this->error['secretkey'] = $this->language->get('error_secretkey');
		}

		if (!$this->request->post['quanqiupay_gateway']) {
			$this->error['gateway'] = $this->language->get('error_gateway');
		}

		return !$this->error;
	}
}