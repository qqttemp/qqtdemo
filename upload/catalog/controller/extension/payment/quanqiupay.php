<?php
	class ControllerExtensionPaymentQuanqiuPay extends Controller
	{
		public function index()
		{
			$this->load->language('extension/payment/quanqiupay');

			if ($this->config->get('quanqiupay_mode') == "Api") {
				$data['text_credit_card'] = $this->language->get('text_credit_card');
				$data['text_wait'] = $this->language->get('text_wait');

				$data['entry_cc_owner'] = $this->language->get('entry_cc_owner');
				$data['entry_cc_number'] = $this->language->get('entry_cc_number');
				$data['entry_cc_expire_date'] = $this->language->get('entry_cc_expire_date');
				$data['entry_cc_cvv2'] = $this->language->get('entry_cc_cvv2');

				$data['button_confirm'] = $this->language->get('button_confirm');
				

				$data['months'] = array();

				for ($i = 1; $i <= 12; $i++) {
					$data['months'][] = array(
						'text' => sprintf('%02d' ,$i) ,
						'value' => sprintf('%02d' ,$i)
					);
				}
				$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
				if ($order_info["order_status_id"] < 1) {
					//$this->model_checkout_order->confirm($this->session->data['order_id'], $this->config->get('quanqiupay_order_status_id'));
					$this->model_checkout_order->addOrderHistory($this->session->data['order_id'] ,$this->config->get('quanqiupay_order_status_id'));
				}

				$today = getdate();

				$data['year_expire'] = array();

				for ($i = $today['year']; $i < $today['year'] + 11; $i++) {
					$data['year_expire'][] = array(
						'text' => strftime('%Y' ,mktime(0 ,0 ,0 ,1 ,1 ,$i)) ,
						'value' => strftime('%Y' ,mktime(0 ,0 ,0 ,1 ,1 ,$i))
					);
				}

				if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/extension/payment/quanqiupay_api.tpl')) {
					return $this->load->view($this->config->get('config_template') . '/extension/payment/quanqiupay_api.tpl' ,$data);
				} else {
					return $this->load->view('extension/payment/quanqiupay_api.tpl' ,$data);
				}
			}

			if ($this->config->get('quanqiupay_mode') == "Redirect") {
				$data['button_confirm'] = $this->language->get('button_confirm');
				//
				$this->load->model('checkout/order');

				$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
				//
				if ($order_info["order_status_id"] < 1) {
					//$this->model_checkout_order->confirm($this->session->data['order_id'], $this->config->get('quanqiupay_order_status_id'));
					$this->model_checkout_order->addOrderHistory($this->session->data['order_id'] ,$this->config->get('quanqiupay_order_status_id'));
				}
				$amount = 0;
				if (isset($order_info['shipping_iso_code_2']) && $order_info['shipping_iso_code_2'] != null) {
					$shippingcountry = $order_info['shipping_iso_code_2'];
				} else {
					$shippingcountry = $order_info['payment_iso_code_2']; //
				}

				if ($order_info['payment_iso_code_2'] == 'US' || $order_info['payment_iso_code_2'] == 'CA') {
					if (isset($order_info['payment_zone_code']) && $order_info['payment_zone_code'] != null) {
						$billingstate = $order_info['payment_zone_code'];
					}
				} else {
					if (isset($order_info['payment_zone']) && trim($order_info['payment_zone']) != null) {
						$billingstate = $order_info['payment_zone'];
					}
				}
				if ($order_info['shipping_iso_code_2'] == 'US' || $order_info['shipping_iso_code_2'] == 'CA') {
					if (isset($order_info['shipping_zone_code']) && trim($order_info['shipping_zone_code']) != null)
						$shippingstate = $order_info['shipping_zone_code'];
					else
						$shippingstate = $billingstate;

				} else {
					if (isset($order_info['shipping_zone']) && trim($order_info['shipping_zone']) != null)
						$shippingstate = $order_info['shipping_zone'];
					else
						$shippingstate = $billingstate;
				}
				//

				if (empty($billingstate)) {
					$billingstate = "-";
				}
				if (empty($shippingstate)) {
					$shippingstate = "-";
				}
				$data['action'] = $this->config->get('quanqiupay_gateway') . '/v1/gateway';
                $data['version'] = '20160815';
				$data['mode'] = $this->config->get('quanqiupay_mode');
				$data['applicationid'] = $this->config->get('quanqiupay_applicationid');
				$data['domain'] = $_SERVER['HTTP_HOST'];
				//$data['domain'] = 'example.com';
				$data['orderid'] = $this->config->get('quanqiupay_order_prefix').$this->session->data['order_id'];
				$data['email'] = $order_info['email'];
				$data['currency'] = $order_info['currency_code'];
				$data['ipaddress'] = $this->get_ip();
				$data['amount'] = number_format($order_info['total'] ,2 ,'.' ,'');
				$data['freight'] = '0.00';
				$data['discount'] = '0.00';
				$data['tax'] = '0.00';

				$i = 1;
				foreach ($this->cart->getProducts() as $product) {
					if (isset($product['sku']) && $product['sku'] != null)
						$data['productsku' . $i] = $product['sku'];
					else
						$data['productsku' . $i] = '-';
					$data['productname' . $i] = $product['name'];
					$data['productprice' . $i] = number_format($this->currency->format($product['price'] ,$order_info['currency_code'] ,$order_info['currency_value'] ,FALSE) ,2 ,'.' ,'');
					$data['productquantity' . $i] = $product['quantity'];
					$i++;
				}
				$data['i'] = $i;

				$data['billingfirstname'] = $order_info['payment_firstname'];
				$data['billinglastname'] = $order_info['payment_lastname'];
				$data['billingtelephone'] = $order_info['telephone'];
				$data['billingzipcode'] = $order_info['payment_postcode'];
				$data['billingaddress1'] = $order_info['payment_address_1'];
				$data['billingaddress2'] = $order_info['payment_address_2'];
				$data['billingcity'] = $order_info['payment_city'];
				$data['billingstate'] = $billingstate;
				$data['billingcountry'] = $order_info['payment_iso_code_2'];
				//$data['Language']=$language;

				if ($order_info['shipping_firstname'] == null && $order_info['shipping_lastname'] == null) {
					$data['shippingfirstname'] = $data['billingfirstname'];
					$data['shippinglastname'] = $data['billinglastname'];
					$data['shippingtelephone'] = $data['billingtelephone'];
					$data['shippingzipcode'] = $data['billingzipcode'];
					$data['shippingaddress1'] = $data['billingaddress1'];
					$data['shippingaddress2'] = $data['billingaddress2'];
					$data['shippingcity'] = $data['billingcity'];
					$data['shippingstate'] = $data['billingstate'];
					$data['shippingcountry'] = $data['billingcountry'];
				} else {
					$data['shippingfirstname'] = $order_info['shipping_firstname'];
					$data['shippinglastname'] = $order_info['shipping_lastname'];
					$data['shippingtelephone'] = $order_info['telephone'];
					$data['shippingzipcode'] = $order_info['shipping_postcode'];
					$data['shippingaddress1'] = $order_info['shipping_address_1'];
					$data['shippingaddress2'] = $order_info['shipping_address_2'];
					$data['shippingcity'] = $order_info['shipping_city'];
					$data['shippingstate'] = $shippingstate;
					$data['shippingcountry'] = $order_info['shipping_iso_code_2'];
				}

				$data['signature'] = md5($data['applicationid'] . $data['orderid'] . $data['email'] . $data['currency'] . $data['amount'] . $data['discount'] . $data['tax'] . $data['freight'] . $this->config->get('quanqiupay_secretkey'));

				if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/extension/payment/quanqiupay_redirect.tpl')) {
					return $this->load->view($this->config->get('config_template') . '/extension/payment/quanqiupay_redirect.tpl' ,$data);
				} else {
					return $this->load->view('extension/payment/quanqiupay_redirect.tpl' ,$data);
				}
			}
		}

		public function send()
		{
			$url = $this->config->get("quanqiupay_gateway") . "/v1/gateway";

			$this->load->model('checkout/order');

			$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

			$data = array();
			if (isset($order_info['shipping_iso_code_2']) && $order_info['shipping_iso_code_2'] != null) {
				$shippingcountry = $order_info['shipping_iso_code_2'];
			} else {
				$shippingcountry = $order_info['payment_iso_code_2']; //
			}

			if ($order_info['payment_iso_code_2'] == 'US' || $order_info['payment_iso_code_2'] == 'CA') {
				if (isset($order_info['payment_zone_code']) && $order_info['payment_zone_code'] != null) {
					$billingstate = $order_info['payment_zone_code'];
				}
			} else {
				if (isset($order_info['payment_zone']) && trim($order_info['payment_zone']) != null) {
					$billingstate = $order_info['payment_zone'];
				}
			}

			if ($order_info['shipping_iso_code_2'] == 'US' || $order_info['shipping_iso_code_2'] == 'CA') {
				if (isset($order_info['shipping_zone_code']) && trim($order_info['shipping_zone_code']) != null)
					$shippingstate = $order_info['shipping_zone_code'];
				else
					$shippingstate = $billingstate;

			} else {
				if (isset($order_info['shipping_zone']) && trim($order_info['shipping_zone']) != null)
					$shippingstate = $order_info['shipping_zone'];
				else
					$shippingstate = $billingstate;
			}

            $data['version'] = '20160815';
			$data['mode'] = $this->config->get('quanqiupay_mode');
			$data['applicationid'] = $this->config->get('quanqiupay_applicationid');
			$data['domain'] = $_SERVER['HTTP_HOST'];
			//$data['domain'] = 'example.com';
			$data['orderid'] = $this->config->get('quanqiupay_order_prefix').$this->session->data['order_id'];
			$data['email'] = $order_info['email'];
			$data['currency'] = $order_info['currency_code'];
			$data['ipaddress'] = $this->get_ip();
			$data['amount'] = number_format(($order_info['total'] * $order_info['currency_value']) ,2 ,'.' ,'');
			$data['freight'] = '0.00';
			$data['discount'] = '0.00';
			$data['tax'] = '0.00';

			$i = 1;
			foreach ($this->cart->getProducts() as $product) {
				if (isset($product['sku']) && $product['sku'] != null)
					$data['productsku' . $i] = $product['sku'];
				else
					$data['productsku' . $i] = '-';
				$data['productname' . $i] = $product['name'];
				$data['productprice' . $i] = number_format($this->currency->format($product['price'] ,$order_info['currency_code'] ,$order_info['currency_value'] ,FALSE) ,2 ,'.' ,'');
				$data['productquantity' . $i] = $product['quantity'];
				$i++;
			}

			$data['billingfirstname'] = $order_info['payment_firstname'];
			$data['billinglastname'] = $order_info['payment_lastname'];
			$data['billingtelephone'] = $order_info['telephone'];
			$data['billingzipcode'] = $order_info['payment_postcode'];
			$data['billingaddress1'] = $order_info['payment_address_1'];
			$data['billingaddress2'] = $order_info['payment_address_2'];
			$data['billingcity'] = $order_info['payment_city'];
			$data['billingstate'] = $billingstate;
			$data['billingcountry'] = $order_info['payment_iso_code_2'];

			if ($order_info['shipping_firstname'] == null && $order_info['shipping_lastname'] == null) {
				$data['shippingfirstname'] = $data['billingfirstname'];
				$data['shippinglastname'] = $data['billinglastname'];
				$data['shippingtelephone'] = $data['billingtelephone'];
				$data['shippingzipcode'] = $data['billingzipcode'];
				$data['shippingaddress1'] = $data['billingaddress1'];
				$data['shippingaddress2'] = $data['billingaddress2'];
				$data['shippingcity'] = $data['billingcity'];
				$data['shippingstate'] = $data['billingstate'];
				$data['shippingcountry'] = $data['billingcountry'];
			} else {
				$data['shippingfirstname'] = $order_info['shipping_firstname'];
				$data['shippinglastname'] = $order_info['shipping_lastname'];
				$data['shippingtelephone'] = $order_info['telephone'];
				$data['shippingzipcode'] = $order_info['shipping_postcode'];
				$data['shippingaddress1'] = $order_info['shipping_address_1'];
				$data['shippingaddress2'] = $order_info['shipping_address_2'];
				$data['shippingcity'] = $order_info['shipping_city'];
				$data['shippingstate'] = $shippingstate;
				$data['shippingcountry'] = $order_info['shipping_iso_code_2'];
			}

			$data['creditcardexpire'] = $this->request->post['cc_expire_date_year'] . $this->request->post['cc_expire_date_month'];
			$data['creditcardnumber'] = str_replace(' ' ,'' ,$this->request->post['cc_number']);
			$data['creditcardname'] = $this->request->post['cc_owner'];
			$data['creditcardcsc2'] = $this->request->post['cc_cvv2'];
            
			$data['signature'] = md5($data['applicationid'] . $data['orderid'] . $data['email'] . $data['currency'] . $data['amount'] . $data['discount'] . $data['tax'] . $data['freight'] . $this->config->get('quanqiupay_secretkey'));

			$curl = curl_init($url);
			curl_setopt($curl ,CURLOPT_URL ,$url);
			curl_setopt($curl ,CURLOPT_SSL_VERIFYPEER ,false);
			curl_setopt($curl ,CURLOPT_POST ,1);
			curl_setopt($curl ,CURLOPT_POSTFIELDS ,$data);
			curl_setopt($curl ,CURLOPT_FOLLOWLOCATION ,1);
			curl_setopt($curl ,CURLOPT_HEADER ,0);
			curl_setopt($curl ,CURLOPT_RETURNTRANSFER ,1);
			curl_setopt($curl ,CURLOPT_CONNECTTIMEOUT ,120);
            curl_setopt($curl, CURLOPT_HTTPHEADER,array(
                'Accept-Language:'.$_SERVER['HTTP_ACCEPT_LANGUAGE'],
                'User-Agent:'.$_SERVER['HTTP_USER_AGENT']
			));
			curl_setopt ($curl, CURLOPT_SSLVERSION, 6);

			$response = curl_exec($curl);

			$json = array();

			if (curl_error($curl)) {
				$json['error'] = 'CURL ERROR: ' . curl_errno($curl) . '::' . curl_error($curl);

				$this->add_log('AUTHNET AIM CURL ERROR: ' . curl_errno($curl) . '::' . curl_error($curl));
			} elseif ($response) {
				$response_data = $this->explode_return_str($response);

				$return_transactionid = isset($response_data['transactionid']) ? $response_data['transactionid'] : '';
				$return_orderid = isset($response_data['orderid']) ? $response_data['orderid'] : null;
				$return_status = isset($response_data['status']) ? $response_data['status'] : null;
				$return_reason = isset($response_data['reason']) ? $response_data['reason'] : null;
				$return_extradata = isset($response_data['extradata']) ? $response_data['extradata'] : null;
				$return_url = isset($response_data['url']) ? $response_data['url'] : null;
				$return_message = isset($response_data['message']) ? $response_data['message'] : null;
				$return_originalcurrency = isset($response_data['requestcurrency']) ? $response_data['requestcurrency'] : null;
				$return_originalamount = isset($response_data['requestamount']) ? $response_data['requestamount'] : null;
				$return_transactioncurrency = isset($response_data['transactioncurrency']) ? $response_data['transactioncurrency'] : null;
				$return_transactionamount = isset($response_data['transactionamount']) ? $response_data['transactionamount'] : null;
				$return_signature = isset($response_data['signature']) ? $response_data['signature'] : null;
				# $signature_local = MD5($return_transactionid . $return_orderid . $return_status . $return_originalcurrency . $return_originalamount . $return_transactioncurrency . $return_transactionamount . $this->config->get('quanqiupay_secretkey'));

				if ($return_status == 'Error') 
				{
					#$this->add_log('return from payment gateway: transactionid=' . $return_transactionid . '&orderid=' . $return_orderid . '&status=' . $return_status . 
					#	'&reason=' . $return_reason . '&extradata=' .$return_extradata .'&message=' . $return_message) ;
					$json['error'] = 'System exception, unable to process the request now. Please contact customer service for help.';
				} else {
					if ($response_data['status'] == 'Processing' || $response_data['status'] == 'Success') {
						$signature_local = MD5($return_transactionid . $return_orderid . $return_status . $return_originalcurrency . $return_originalamount . $return_transactioncurrency . $return_transactionamount . $this->config->get('quanqiupay_secretkey'));
						if($signature_local != $response_data['signature']){
							$this->add_log('return from payment gateway: transactionid=' . $return_transactionid . '&orderid=' . $return_orderid . '&status=' . $return_status . 
							'&reason=invalid signature') ;
							$json['error'] = 'System exception, unable to process the request now. Please contact customer service for help.';
						}
						else{
							$json['success'] = $this->url->link('checkout/success' ,'' ,'SSL');
						}
					} 
					else if($return_status === 'Redirect')
					{
						$signature_local = MD5($return_transactionid . $return_orderid . $return_status . $return_url . $this->config->get('quanqiupay_secretkey'));
						if($signature_local != $response_data['signature']){
							$this->add_log('return from payment gateway: transactionid=' . $return_transactionid . '&orderid=' . $return_orderid . '&status=' . $return_status . 
							'&url=' . $return_url . '&reason=invalid signature') ;
							$json['error'] = 'System exception, unable to process the request now. Please contact customer service for help.';
						}
						else{
							$json['3d'] = true;
							if(!empty($return_url)) $json['url'] = $return_url;
							if(!empty($return_message)) $json['content'] = urldecode($return_message);
						}
					}
					else if ($return_status == 'Failure') {						
						$signature_local = MD5($return_transactionid . $return_orderid . $return_status . $return_reason . $this->config->get('quanqiupay_secretkey'));
						if($signature_local != $response_data['signature']){
							$this->add_log('return from payment gateway: transactionid=' . $return_transactionid . '&orderid=' . $return_orderid . '&status=' . $return_status . 
							'&reason=' . $return_reason . '&reason=invalid signature') ;
							$json['error'] = 'System exception, unable to process the request now. Please contact customer service for help.';
						}
						else {
							$financialinsufficientmessage = null;
							if ($return_reason == 'ExpiredCard') {
								$financialinsufficientmessage = 'Expired card';
							}
							if ($return_reason == 'DoNotHonor') {
								$financialinsufficientmessage = 'Do not honor';
							}
							if ($return_reason== 'InsufficientFunds') {
								$financialinsufficientmessage = 'Insufficient funds in the account';
							}
							if ($return_reason == 'StolenOrLostCard') {
								$financialinsufficientmessage = 'Stolen or lost card';
							}
							if ($return_reason == 'InactiveCard') {
								$financialinsufficientmessage = 'Inactive card';
							}
							if ($return_reason == 'InvalidAccountNumber') {
								$financialinsufficientmessage = 'Invalid account number';
							}
							if ($return_reason == 'InvalidCsc2') {
								$financialinsufficientmessage = 'Invalid CVV2';
							}
							if ($return_reason == 'RestrictedCard') {
								$financialinsufficientmessage = 'Restricted card';
							}
							if ($return_reason == 'DuplicateOrder') {
								$financialinsufficientmessage = 'Duplicate order';
							}	
							if ($return_reason == 'TestCard') {
								$financialinsufficientmessage = 'Test Card';
							}	
							
							if ($financialinsufficientmessage != null) {
								switch ($financialinsufficientmessage) {
									case 'Expired card':
										$errorCode = 'Very Sorry. Your issuing bank or credit card company said Expired card Please try to contact with your issuing bank or use a different card and try again.';
										break;
									case 'Do not honor':
										$errorCode = 'Very Sorry. Your issuing bank or credit card company said Do not honor Please try to contact with your issuing bank or use a different card and try again.';
										break;
									case 'Insufficient funds in the account':
										$errorCode = 'Very Sorry. Your issuing bank or credit card company said Insufficient funds in the account Please try to contact with your issuing bank or use a different card and try again.';
										break;
									case 'Stolen or lost card':
										$errorCode = 'Very Sorry. Your issuing bank or credit card company said Stolen or lost card Please try to contact with your issuing bank or use a different card and try again.';
										break;
									case 'Inactive card':
										$errorCode = 'Very Sorry. Your issuing bank or credit card company said Inactive card Please try to contact with your issuing bank or use a different card and try again.';
										break;
									case 'Invalid account number':
										$errorCode = 'Very Sorry. Your issuing bank or credit card company said Invalid account number Please try to contact with your issuing bank or use a different card and try again.';
										break;
									case 'Invalid CVV2':
										$errorCode = 'Very Sorry. Your issuing bank or credit card company said Invalid CVV2 Please try to contact with your issuing bank or use a different card and try again.';
										break;
									case 'Restricted card':
										$errorCode = 'Very Sorry. Your issuing bank or credit card company said Restricted card Please try to contact with your issuing bank or use a different card and try again.';
										break;
									case 'Duplicate order':
										$errorCode = 'Very Sorry. Your issuing bank or credit card company said Duplicate order Please do not try again with same information.';
										break;
									case 'Test Card':
										$errorCode = 'Very Sorry. Your issuing bank or credit card company said Test Card Please do not try again with same information.';
										break;
								}
								$json['error'] = $errorCode;
							}
						}
					} else {
						$this->add_log('order status error: transactionid=' . $return_transactionid . '&orderid=' . $return_orderid . '&status=' . $return_status . '&reason=' . $return_reason . '&extradata=' .
							$return_extradata . '&url=' . $return_url . '&currency=' . $return_currency . '&amount=' . $return_amount . '&originalcurrency=' . $return_originalcurrency . '&originalamount=' .
							$return_originalamount . '&signature=' . $return_signature);
						$json['error'] = 'System exception, unable to process the request now. Please contact customer service for help.';
					}
				}
			} else {
				$json['error'] = 'Empty Gateway Response';

				$this->add_log('AUTHNET AIM CURL ERROR: Empty Gateway Response');
			}

			curl_close($curl);

			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode($json));
		}

		public function  callback()
		{
			try {
				$this->load->model('checkout/order');
				$transactionid = $_POST['transactionid'];
				$orderid = $_POST['orderid'];
				$status = $_POST['status'];
				$currency = $_POST['transactioncurrency'];
				//if(!empty($_POST['amount']))//*********
				$amount = $_POST['transactionamount'];
				//if(!empty($_POST['currencytype']))//*********
				$originalcurrency = $_POST['requestcurrency'];
				$originalamount = $_POST['requestamount'];
				$signature = $_POST['signature'];
				$secretkey = $this->config->get('quanqiupay_secretkey');

				//if(empty($Amount))$Signature_local=md5($TransactionId.$OrderId.$Status.$SecretKey);//*********
				//else
				$signature_local = md5($transactionid . $orderid . $status . $originalcurrency . $originalamount . $currency . $amount  . $secretkey);
				if ($status != 'error' && !strcasecmp($signature_local ,$signature)) {
				
					//if(empty($Amount))$note='quanqiupay notification-'. "transactionid: " . $TransactionId .", " . "orderid: " . $OrderId . ", " . "status: " . $Status ."";
					//else
					$note = 'qqtpay notification-' . "transactionid: " . $transactionid . ", " . "orderid: " . $orderid . ", " . "status: " . $status . ", " . "amount: " . $currency . $amount . "";
					//TODO:记录日志
					$this->add_log('receive order notify: transactionid=' . $transactionid . '&orderid=' . $orderid . '&status=' . $status . '&currency=' . $currency . '&amount=' . $amount . '&originalcurrency=' . $originalcurrency . '&originalamount=' . $originalamount . '&signature=' . $signature . "\r\n");
					{
						// TODO: Add your code...
						//$this->model_checkout_order->update($orderid,1,$note);//Pending
						//$array= $this->model_checkout_order->getOrder($orderid);
						//if(!$array['order_status_id']==1)throw new Exception('The SQL statement is not executed successfully');
					}

					if ($status === 'Failure') {
						// TODO: Add your code...
						//$this->model_checkout_order->update($orderid,10,$note);//Failed
						//$array= $this->model_checkout_order->getOrder($orderId);
						//if(!$array['order_status_id']==10)throw new Exception('The SQL statement is not executed successfully');
					}

					if ($status === 'Processing') {
						// TODO: Add your code...
						//$this->model_checkout_order->update($orderid,2,$note);//Failed
						//$array= $this->model_checkout_order->getOrder($orderid);
						//if(!$array['order_status_id']==2)throw new Exception('The SQL statement is not executed successfully');
					}
					if ($status == 'Success') {
						$orderidWithoutprifx = str_replace($this->config->get('quanqiupay_order_prefix'),'',$orderid);
						//$this->model_checkout_order->update($orderidWithoutprifx,$this->config->get('quanqiupay_order_notify_status_id'),$note);//Complete
						$this->model_checkout_order->addOrderHistory($orderidWithoutprifx ,$this->config->get('quanqiupay_order_notify_status_id') ,$note);
						//$array= $this->model_checkout_order->getOrder($orderid);
						//if($array['order_status_id']!=5)throw new Exception('The SQL statement is not executed successfully');
					}
				} else {
					throw new Exception('The verification of the data is invalid!');
				}
			} catch (Exception $e) {
				$this->add_log('signature validate error: transactionid=' . $transactionid . '&orderid=' . $orderid . '&status=' . $status . '&currency=' . $currency . '&amount=' . $amount . '&originalcurrency=' . $originalcurrency . '&originalamount=' . $originalamount . '&signature=' . $signature);
				echo $e->getMessage();
				header('HTTP/1.1 404 Not Found');
			}
		}

		function get_ip()
		{
			if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
				$online_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
			} elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
				$online_ip = $_SERVER['HTTP_CLIENT_IP'];
			} else {
				$online_ip = $_SERVER['REMOTE_ADDR'];
			}
			return $online_ip;
		}

		function explode_return_str($original_str)
		{
			$original_str = explode('&' ,$original_str);
			$middle_str = array();
			$last_str = array();
			for ($i = 0; $i < count($original_str); $i++) {
				$middle_str[$i] = explode('=' ,$original_str[$i]);
			}
			for ($i = 0; $i < count($middle_str); $i++) {
				$last_str[$middle_str[$i][0]] = $middle_str[$i][1];
			}
			return $last_str;
		}

		function add_log($log)
		{
			$fp = fopen("system/logs/qqtpay-log-" . date("Y-m-d") . ".txt" ,"a");
			flock($fp ,LOCK_EX);
			fwrite($fp ,"[" . date("Y-m-d h:i:s") . "]" . $log . "\r\n");
			flock($fp ,LOCK_UN);
			fclose($fp);
		}
	}