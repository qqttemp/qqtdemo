<?php
class ModelExtensionPaymentquanqiupay extends Model {
	public function getMethod($address, $total) {
		$this->load->language('extension/payment/quanqiupay');
		if ($this->config->get('quanqiupay_status')) {
      		$status = TRUE;
      	} else {
			$status = FALSE;
		}
		$method_data = array();

		if ($status) {
			$applicationid =  $this->config->get('quanqiupay_applicationid');
			$secretkey =  $this->config->get('quanqiupay_secretkey');
			@$gateway_new = $this->config->get('quanqiupay_gateway');
			@$tp_url = "image/quanqiupay.png";
		 	$tp="<div><img src='$tp_url'>" . $this->language->get('text_title') . "</div>";
			$method_data = array(
				'code'       => 'quanqiupay',
				'title'      => $tp,//$this->language->get('text_title'),
				'terms'      => '',
				'sort_order' => $this->config->get('quanqiupay_sort_order')
			);
		}

		return $method_data;
	}
}