<?php
/*
	Code dumbed down from the great work at vlc-shares: http://code.google.com/p/vlc-shares/source/browse/trunk/plugins/hulu/library/X/VlcShares/Plugins/Helper/Hulu.php?r=700
	
	With help from https://github.com/svnpenn/
	
	You need rtmpdump 2.4 and php5-mcrypt
	
	By Jake Cattrall
	https://github.com/krazyjakee
*/   

	if(isset($_GET['u']) == false){
		die('http://github.com/krazyjakee');
	}
	
    function decodeSmil($encrypted_smil){
    	$KEYS_SMIL = array(
	        array('4878B22E76379B55C962B18DDBC188D82299F8F52E3E698D0FAF29A40ED64B21', 'WA7hap7AGUkevuth'),
	        array('246DB3463FC56FDBAD60148057CB9055A647C13C02C64A5ED4A68F81AE991BF5', 'vyf8PvpfXZPjc7B1'),
	        array('8CE8829F908C2DFAB8B3407A551CB58EBC19B07F535651A37EBC30DEC33F76A2', 'O3r9EAcyEeWlm5yV'),
	        array('852AEA267B737642F4AE37F5ADDF7BD93921B65FE0209E47217987468602F337', 'qZRiIfTjIGi3MuJA'),
	        array('76A9FDA209D4C9DCDFDDD909623D1937F665D0270F4D3F5CA81AD2731996792F', 'd9af949851afde8c'),
	        array('1F0FF021B7A04B96B4AB84CCFD7480DFA7A972C120554A25970F49B6BADD2F4F', 'tqo8cxuvpqc7irjw'),
	        array('3484509D6B0B4816A6CFACB117A7F3C842268DF89FCC414F821B291B84B0CA71', 'SUxSFjNUavzKIWSh'),
	        array('B7F67F4B985240FAB70FF1911FCBB48170F2C86645C0491F9B45DACFC188113F', 'uBFEvpZ00HobdcEo'),
	        array('40A757F83B2348A7B5F7F41790FDFFA02F72FC8FFD844BA6B28FD5DFD8CFC82F', 'NnemTiVU0UA5jVl0'),
	        array('d6dac049cc944519806ab9a1b5e29ccfe3e74dabb4fa42598a45c35d20abdd28', '27b9bedf75ccA2eC')           
	    );
	    
	    $encrypted_data = pack("H*", $encrypted_smil);
		foreach ($KEYS_SMIL as $couple ) {
			list($key, $iv) = $couple;
			$smil = '';
			$uneas = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, @pack("H*", $key), $encrypted_data, MCRYPT_MODE_ECB);
			$xorkey = @pack("a*", $iv);
			
			$xorkey = substr($xorkey, 0, 16);
			
			for ( $i = 0; $i < ceil( strlen($encrypted_smil) / 32 ); $i++ ) {
			        $res = $xorkey ^ substr($uneas, $i * 16, 16);
			        $xorkey = substr($encrypted_data, $i * 16, 16);
			        $smil .= $res;
			}
			
			$lastchar = ord(substr($smil, -1));
			
			if ( substr($smil, -$lastchar) == str_repeat(chr($lastchar), $lastchar) ) {
			        $smil = substr($smil, 0, -$lastchar);
			}
			if ( preg_match('/^(?:<smil|\s*<.+?>.*<\/.+?>)/i', $smil) ) {
			        return $smil;
			}
		}
    }
    
    function getInfo($url){
	    $source = file_get_contents("http://www.hulu.com/api/oembed.xml?url=".urlencode($url));
	    return $source;
    }
    
    function getHuluStream($url,$choice){
    	$swfplayer = 'http://download.hulu.com/huludesktop.swf';
		$KEYS_PID = array(
	        '6fe8131ca9b01ba011e9b0f5bc08c1c9ebaf65f039e1592d53a30def7fced26c',
	        'd3802c10649503a60619b709d1278ffff84c1856dfd4097541d55c6740442d8b',
	        'c402fb2f70c89a0df112c5e38583f9202a96c6de3fa1aa3da6849bb317a983b3',
	        'e1a28374f5562768c061f22394a556a75860f132432415d67768e0c112c31495',
	        'd3802c10649503a60619b709d1278efef84c1856dfd4097541d55c6740442d8b'
	    );
	    
	    $KEYS_PLAYER = 'yumUsWUfrAPraRaNe2ru2exAXEfaP6Nugubepreb68REt7daS79fase9haqar9sa';
	        
	    $KEYS_V = '888324234';
	        
	    $HMAC = 'f6daaa397d51f568dd068709b0ce8e93293e078f7dfc3b40dd8c32d36d2b3ce1';
	        
	    $KEYS_FP = 'Genuine Adobe Flash Player 001';
	    
	    $CDN = "limelight";
	    
	    $PLUS = false;
		
		$source = getInfo($url);
		
		$xml = new SimpleXMLElement($source);
		$EID = str_replace("http://www.hulu.com/embed/","",$xml->embed_url);
		
		$source = file_get_contents("http://r.hulu.com/videos?eid=".$EID);
		$xml = new SimpleXMLElement($source);
		$PID = str_replace("NO_MORE_RELEASES_PLEASE_","",$xml->video->pid);
		
		$title = (string) $xml->video->title[0];
                        
	    if($xml->video->{'media-type'} == "TV"){
	        $show_name      = $xml->video->show->name[0];
	        $season         = $xml->video->{"season-number"}[0];
	        $episode_number = $xml->video->{"episode-number"}[0];
	    
	        $title = sprintf('%s - S%02dE%02d - %s', $show_name, $season, $episode_number, $title);
	    }
	    
	    $description = (string) $xml->video->description[0];
	    
	    $length = $xml->video->duration[0];
	    
	    $thumbnail = (string) $xml->video->{"thumbnail-url"}[0];
		
		$now = (int) time();
		$parameters = array(
		    'video_id' => $PID,
		    'v' => $KEYS_V,
		    'ts' => ((string)$now),
		    'np' => '1',
		    'vp' =>'1',
		    'device_id' => '',      
		    'pp' => 'Desktop',
		    'dp_id' => 'Hulu',
		    'region' => 'US',
		    'ep' => '1',
		    'language' => 'en'
		);
		$paramKeys = array_keys($parameters);
		$sortedParams = $parameters;
	    ksort($sortedParams);
	    $bcsl = '';
	    foreach ($sortedParams as $key => $value) {
	            $bcsl .= $key.$value;
	    }
	    $bcs = hash_hmac('md5', $bcsl, $HMAC);
	    
	    $xmlraw = decodeSmil(file_get_contents('http://s.hulu.com/select?'.http_build_query($sortedParams)."&bcs={$bcs}"));
	    $xml = new SimpleXMLElement($xmlraw);
	    $choices = $xml->body->switch[1]->video;
	    if($choice != "false"){
	    	if($choice == "rawxml"){
		    	die($xmlraw);
	    	}
	    }else{
	    	$q = "";
		    foreach($choices as $c){
		   		$q .= $c['profile'].",";
		    }
		    return substr($q,0,strlen($q)-1);
	    }
	    $choice = (int)$choice;
	    $vid = $choices[$choice];
	    
	    $stream = (string) $vid['stream'];
	    $server = (string) $vid['server'];
	    $token = (string) $vid['token'];
	    $cdn = (string) $vid['cdn'];
	    
	    $hostname = "";
	    $appName = "";
	    $protocol = "";
	    
	    $pattern = '/^(?P<protocol>[a-zA-z]+:\/\/)(?P<hostname>[^\/]+)\/(?P<appname>.*)$/';
	    
	    $matches = array();
	    if ( preg_match($pattern, $server, $matches ) ) {
	            $protocol = $matches['protocol'];
	            $hostname = $matches['hostname'];
	            $appName = $matches['appname'];
	    }
	    
	    switch ($cdn) {
	        case 'level3': 
	                $appName .= "?sessionid=sessionId&$token";
	                $stream = substr($stream, 0, -4);
	                $server = "$server?sessionid=sessionId&$token";
	                break;
	
	        case 'limelight': 
	                $appName .= "?sessionid=sessionId&$token";
	                $stream = substr($stream, 0, -4);
	                $server = "$server?sessionid=sessionId&$token";
	                break;
	                
	        case 'akamai': 
	                $appName .= "?sessionid=sessionId&$token";
	                $server = "$server?sessionid=sessionId&$token";
	                break;        
	    }
	    $fetched = array(
	        'title' => $title,
	        'description' => $description,
	        'length' => $length,
	        'thumbnail' => $thumbnail,
	
	        'eid' => $EID,
	        'pid' => $PID,
	
	        'stream' => $stream,
	        'server' => substr($server,0,strpos($server,"?")),
	        'token' => $token,
	        'cdn' => $cdn,
	
	        'hostname' => $hostname,
	        'protocol' => $protocol,
	        'appName' => $appName,
	        'playpath' => $stream,
	        'swfUrl' => $swfplayer,
	        'contentUrl' => $url
		);
		return $fetched;
	}
	
	function downloadVideo($url,$choice){
		$hulu = getHuluStream($url,$choice);
		if($choice == "false"){
			echo $hulu;
		}else{		    
			$command = "rtmpdump -r '".$hulu['server']."' -a '".$hulu['appName']."' -W '".$hulu['swfUrl']."' -p '".$hulu['contentUrl']."' -y '".$hulu['stream']."'";
			if($choice == "data"){ die(print_r($hulu)); }
			if($choice == "command"){ die($command); }
			header("Content-Description: File Transfer");
			header("Accept-Ranges: bytes");
		    header('Content-Type: video/x-flv');
		    header("Content-Disposition: attachment; filename= " . $hulu['title'].".flv");
		    header("Content-Transfer-Encoding: binary");
			echo passthru($command);
		}
	}
	
	if(isset($_GET['u'])){
		$choice = "false";
		if(isset($_GET['choice'])){ $choice = $_GET['choice']; }
		downloadVideo($_GET['u'],$choice);
	}
?>