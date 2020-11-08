<?php
	
	namespace ABSystem\Google;
	
	use GuzzleHttp\Client;
	
	class FCM {
		private $clients;
		private $headers;
		private $server_key;
		private $token_device;
		private $authorization;
		private $base_url;
		private $data_payload;
		private $notification;
		
		public $response;
		public $debug;
		
		
		/**
		 * FCM constructor.
		 * @param $configurations
		 */
		public function __construct ($configurations) {
			
			$this->token_device = [];
			
			$this->clients = new Client([
				'verify' => FALSE
			]);
			
			foreach ($configurations as $key => $val) {
				if (property_exists($this, $key)) {
					$this->$key = $val;
				}
			}
			
			$this->setAuthorization()->setHeaders();
			
		}
		
		
		/**
		 * @return $this
		 */
		protected function setHeaders () {
			$this->headers = [
				'Authorization' => 'key=' . $this->authorization,
			];
			return $this;
		}
		
		
		/**
		 * @param array $tokendevice
		 * @return $this
		 */
		public function setTokenDevice ($tokendevice = []) {
			if (is_array($tokendevice)) {
				foreach ($tokendevice as $item) {
					$this->token_device[] = $item;
				}
			} else {
				$this->token_device[] = $tokendevice;
			}
			return $this;
		}
		
		
		/**
		 * @param $title
		 * @param $message
		 * @return $this
		 */
		public function setPesan ($title, $message) {
			$this->notification = $notification = [
				'title' => $title,
				'body'  => $message
			];
			
			if (!empty($this->data_payload)) {
				$this->data_payload["notification_foreground"] = "true";
			}
			$this->data_payload["message"] = $notification;
			
			return $this;
		}
		
		/**
		 * @throws \GuzzleHttp\Exception\GuzzleException
		 */
		public function kirim () {
			$fcm            = [
				'registration_ids' => $this->token_device,
				'notification'     => $this->notification,
				'data'             => $this->data_payload
			];
			$this->response = $this->post('', json_encode($fcm));
			if ($this->debug) {
				echo '<pre>' . json_encode(['request' => $fcm], JSON_PRETTY_PRINT) . '</pre>';
				echo '<pre>';
				print_r(json_encode(['response' => json_decode($this->response, TRUE)], JSON_PRETTY_PRINT));
				echo '</pre>';
			}
		}
		
		
		/**
		 * @return $this
		 */
		protected function setAuthorization () {
			$this->authorization = $this->server_key;
			return $this;
		}
		
		/**
		 * @param $feature
		 * @param array $data
		 * @param array $headers
		 * @return string
		 * @throws \GuzzleHttp\Exception\GuzzleException
		 */
		protected function post ($feature, $data = [], $headers = []) {
			$this->headers['Content-Type'] = 'application/json';
			if (!empty($headers)) {
				$this->headers = array_merge($this->headers, $headers);
			}
			try {
				$response = $this->clients->request(
					'POST',
					$this->base_url,
					[
						'headers' => $this->headers,
						'body'    => $data
					]
				)->getBody()->getContents();
			} catch (\Exception $e) {
				$response = $e->getResponse()->getBody();
			}
			return $response;
		}
		
		/**
		 * @param $data_payload
		 * @return $this
		 */
		public function setDataPayload ($data_payload) {
			foreach ($data_payload as $kdp => $vdp) {
				$this->data_payload[$kdp] = $vdp;
			}
			return $this;
		}
		
	}