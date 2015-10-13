<?php

namespace  User\Service;

Class FacebookLogin  {

	protected $appId = '';
	protected $redirectUri = '';

	public function getAutheticateUrl(){
			$url = 'https://www.facebook.com/dialog/oauth?client_id= ' . $this->appId . '&redirect_uri=' . $this->redirectUri;
			return $url;
		}

	public function getInfoFacebook() {
			$json = '';
			return json_decode($json);
		}



}
?>