<?php

	/*
	 * INTEP Companion
	 * by thescs
	*/
	
	/*
		Including required DOM parser library.
	*/
	require("parser/simple_html_dom.php");
	
	/*
		Required parameters:
			@host - remote host with Intep LLC webserver;
			@storage - folder name on that server which used for temporarily saving images from telegram bot Заявки ХГЛ;
	*/
	
	$config = [
		"host"		=>	"195.3.196.246",
		"storage"	=>	"_datastorage",
		"debug"		=>  false
		];
		
	/*
		@ function getCsrf
		Return an CSRF token from full plain HTML
		Params (REQUIRED):
			$arg - HTML
	*/
	
	function getCsrf($arg){
		$doc = new DOMDocument();
		$doc->loadHTML($arg);
		$tokens = $doc->getElementsByTagName("meta");
		for ($i = 0; $i < $tokens->length; $i++)
		{
			$meta = $tokens->item($i);
			if($meta->getAttribute('name') == 'csrf-token')
			$token = $meta->getAttribute('content');
		}
		return $token;
	}
	
	/*
		@ function authWithIntep
		Return only HTML table with opened tickets
		Params (REQUIRED):
			@username - User login;
			@password - User password;
		Return FALSE if some required param is missing.
	*/
	
	function authWithIntep($username, $password, $makeworking)
	{
		global $config;
		if (empty($username) || empty($password) || empty($config['host'])) return false;
		$url = "http://$config[host]/index.php?r=site/login";
		unlink("cookies/$username.txt");							// removing old cookies storage
		$cookie= "$username.txt";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookies/'.$cookie);
		curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookies/'.$cookie);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);									// get CSRF token for future request with a payload
		if (curl_errno($ch)) die(curl_error($ch));
		
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, true);
		$params = array(
			'LoginForm[username]' => $username,
			'LoginForm[password]' => $password,
			'_csrf-frontend' => getCsrf($response)
		);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
		curl_exec($ch);												// signing in into system
		curl_setopt($ch, CURLOPT_URL, "http://$config[host]/index.php?r=tickets/index");
		$html = curl_exec($ch);										// get a raw HTML from server
		if (curl_errno($ch)) print curl_error($ch);
		$html = str_get_html($html);								// parsing only the table with tickets
		//curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array('_csrf-frontend' => getCsrf($html))));
		foreach($html->find('table') as $e)
			$table = $e->outertext;									// got table
		foreach(str_get_html($table)->find('a[title=Переглянути]') as $e){
			curl_setopt($ch, CURLOPT_URL, "http://".$config['host'] . htmlspecialchars_decode(urldecode($e->href)));
			$result = curl_exec($ch);
			//curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array('_csrf-frontend' => getCsrf($html))));
			if($config['debug']) echo $result;
			//echo $config['host'] . htmlspecialchars_decode(urldecode($e->href));
			if($makeworking){
				curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(getParams($result)));
				curl_setopt($ch, CURLOPT_URL, "http://$config[host]/index.php?r=tickets%2Fappoint&ticketId=942995");
				curl_exec($ch);
			}
		}
		curl_close($ch);
	}
	
	function readTicketsFromIntep()
	{
		/* TODO */
	}
	
	function readTicketsFromItera()
	{
		/* TODO */
	}
	
	/************* Ops with files *****************/
	
	function storeFileFromTelegram()
	{
		global $config;
		
		/*TODO */
	}
	
	function uploadFileToIntep()
	{
		/* TODO */
	}
	
	/************** Other ops *********************/
	
	function httpRequest($type)
	{
		/* TODO */
	}