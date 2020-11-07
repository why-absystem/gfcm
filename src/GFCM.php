<?php
	/**
	 * Programmer: WHY
	 * Date: 07/11/20
	 * Time: 16.58
	 */
	
	namespace ABSystem\GFCM;
	
	use GuzzleHttp\Client;
	
	class GFCM {
		private $clients;
		private $headers;
		private $server_key;
		private $token_device;
		private $authorization;
		private $base_url;
		private $data_payload;
		
		public $response;
		
		/**
		 * FirebaseGCM constructor.
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
			foreach ($tokendevice as $item) {
				$this->token_device[] = $item;
			}
			return $this;
		}
		
		
		/**
		 * @param $title
		 * @param $message
		 * @param null $id
		 * @param null $action
		 * @return $this
		 * @throws \GuzzleHttp\Exception\GuzzleException
		 */
		public function setPesan ($title, $message) {
			$notification = [
				'title' => $title,
				'body'  => $message
			];
			
			$data_payload = ["message" => $notification];
			
			if (!empty($this->data_payload)) {
				foreach ($this->data_payload as $kdp => $dp) {
					$data_payload["notification_foreground"] = "true";
					$data_payload[$kdp]                      = $dp;
				}
			}
			
			$response = [];
			foreach ($this->token_device as $token) {
				$fcm = [
					'to'           => $token,
					'notification' => $notification,
					'data'         => $data_payload
				];
				
				$response[] = $this->post('', json_encode($fcm));
			}
			return $this;
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
			$this->data_payload = $data_payload;
			return $this;
		}
		
	}