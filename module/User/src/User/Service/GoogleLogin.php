<?php
namespace  User\Service;

include_once '_PhpLibs/google/Google_Client.php';
include_once '_PhpLibs/google/contrib/Google_Oauth2Service.php';

Class GoogleLogin {

	protected $config = array(
			'oauth2_client_id' => '1053489732661-0m461gftsm2hp7rsee1ofarluns77ket.apps.googleusercontent.com',
			'oauth2_client_secret' => 'TR0uh3M-N2DHY4w5FrPRU6aV',
			'oauth2_redirect_uri' => 'http://localhost:9999/signin/google'
			);
	public function getAuthenticationUrl()
	{

		$client = new \Google_Client($this->config);
		$oauth2 = new \Google_Oauth2Service($client);
		$authUrl = $client->createAuthUrl();
		return $authUrl;
	}

	/**
	 *
	 * @param \Google_Client $client
	 */
	public function getGoogleInfo(\Google_Client $client)
	{
		$oauth2 = new Google_Oauth2Service($client);

		if ($client->getAccessToken()) {
			$user = $oauth2->userinfo->get();
			return $user;
		}
		return null;
	}

	public function getGoogleClient(){
		return new \Google_Client($this->config);
	}

	public function getOauth2() {
	return new  \Google_Oauth2Service($this->getGoogleClient());
	}

}
?>