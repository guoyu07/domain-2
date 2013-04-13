<?php
/**
 * 判断域名是否已经注册 (会返回 coderbolg.com/coderbolg.net的注册情况)

 * @param $domain

 * @param $ext

 * @return ARRAY

 * @author tongxuefen

 * @version (2011-09-09)

 *

 */
define("DB_PREFIX","sdfsddeeddddddddddd");
function isRegister($domain, $ext = array('com')) {
	
	if (empty ( $domain )) {
		
		return false;
	
	}
	
	$post_data = $curl = $text = array ();
	$return = false;
	foreach ( $ext as $v ) {
		
		$post_data [] = array ('domain' => $domain . '.' . $v );
	
	}
	
	$urls = array_fill ( 0, count ( $post_data ), 'http://pandavip.www.net.cn/check/check_ac1.cgi' );
	
	$handle = curl_multi_init ();
	
	foreach ( $urls as $k => $v ) {
		
			$curl [$k] = curl_init ( $v );
			
			curl_setopt ( $curl [$k], CURLOPT_HTTPHEADER, array (
	
									"User-Agent: Mozilla/5.0 (Windows NT 5.1; rv:6.0.2) Gecko/20100101 Firefox/6.0.2", 
							
									"Accept:text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8", 
							
									"Accept-Language: zh-cn,zh;q=0.5" )
	
									 );
			
			curl_setopt ( $curl [$k], CURLOPT_REFERER, 'http://www.net.cn/' );
			
			curl_setopt ( $curl [$k], CURLOPT_RETURNTRANSFER, 1 );
			
			curl_setopt ( $curl [$k], CURLOPT_POST, 1 );
			
			curl_setopt ( $curl [$k], CURLOPT_POSTFIELDS, $post_data [$k] );
			
			curl_multi_add_handle ( $handle, $curl [$k] );
	
		}
		
		$active = null;
		
		do {
			
			$mrc = curl_multi_exec ( $handle, $active );
		
		} while ( $mrc == CURLM_CALL_MULTI_PERFORM );
		
		while ( $active && $mrc == CURLM_OK ) {
			
			if (curl_multi_select ( $handle ) != - 1) {
				
				do {
					
					$mrc = curl_multi_exec ( $handle, $active );
				
				} while ( $mrc == CURLM_CALL_MULTI_PERFORM );
			
			}
		
		}
	
		foreach ( $curl as $k => $v ) {
			
			if (curl_error ( $curl [$k] ) == "") {
				
				$text [$k] = ( string ) curl_multi_getcontent ( $curl [$k] );
			
			}
			
			curl_multi_remove_handle ( $handle, $curl [$k] );
			
			curl_close ( $curl [$k] );
		
		}
			
		curl_multi_close ( $handle );
		
		foreach ( $text as $key => $value ) {
			
			if (false === $pos = strrpos ( $value, '|' )) {
				
				$return = false;
			
			} else {
				
				if (false === strrpos ( substr ( $value, $pos ), 'not' )) {
					
					$return [$key] = true;
				
				} else {
					
					$return [$key] = false;
				
				}
			
			}
		
		}
	
		return $return;
}

// perform_whois function returns 0 if domain is available otherwise returns either the raw info or 1
function perform_whois($domainname,$ext="com",$raw=false)
{

	$rawoutput = "";

	if($raw)
		return do_raw($domainname,$ext);

	if(($ns = fsockopen("whois.crsnic.net",43)) == false){
		$errormsg = "不能连接到服务器  <b><i>"."whois.crsnic.net"."</i></b>";
		return -1;
	}
	fputs($ns,"$domainname.$ext\n");
	while(!feof($ns))
		$rawoutput .= fgets($ns,128);

	fclose($ns);

	//echo "<!--\nAvail string = \""."No match for"."\"\nComparing against = \"".$rawoutput."\"\n-->\n";

	if(!ereg("No match for", $rawoutput))
		return 0;

	return 1;
}

// this performs the whois lookup and then shows the data returned
function do_raw($domainname, $ext)
{
	global $titlebar;
	global $template_raw_output;
	global $use_global_templates;
	global $template_header;
	global $template_footer;
	global $raw_output_title;
	global $whois_info_servers;
	global $whois_servers;
	global $rawoutput;
	global $errormsg;
	global $whois_info_servers_backup;
	global $whois_avail_strings;
	global $whois_server;

	//choose_info_server($domainname, $ext);

	if(($ns = fsockopen($whois_server,43)) == false){
		if(($ns = fsockopen($whois_info_servers[$ext],43)) == false){
			if(($ns = fsockopen($whois_info_servers_backup[$ext], 43)) == false){
	                	return -1;
			} else {
				$whois_server = $whois_info_servers_backup[$ext];
			}
		} else {
			$whois_server = $whois_info_servers[$ext];
		}
	}

	print "<!-- using \"".$whois_server."\" for whois query -->\n";

        fputs($ns,"$domainname.$ext\n");
        while(!feof($ns))
                $rawoutput = $rawoutput.fgets($ns,128);

        fclose($ns);

//	$pos = @strpos($rawoutput,$whois_avail_strings[$server]);
//	if(is_string($pos) && !$pos){}
//	else{
	if(!is_string($pos) || $pos){
		if(($ns = fsockopen($whois_info_servers_backup[$ext],43)) == false)
			return -1;
		else{
			$rawoutput = "";
			fputs($ns,"$domainname.$ext\n");
			while(!feof($ns))
				$rawoutput = $rawoutput.fgets($ns,128);
			$pos = @strpos($rawoutput,$whois_avail_strings[$whois_info_servers_backup[$ext]]);
			if(!is_string($pos) || $pos){}
			else
				return -1;
		}
	}
                
	$titlebar = $raw_output_title;

        if($use_global_templates)
                echo make_changes($template_header);

        echo make_changes($template_raw_output);

        if($use_global_templates)
                echo make_changes($template_footer);

	exit();
}
function &db()
{
	//include_once(ROOT_PATH . '/eccore/model/mysql.php'); delete by xuefen 2011-07-11  放入if里面，性能更好
    static $db = null;
    if ($db === null)
    {
        include('db_mysql.class.php');  //include_once 性能不好
        $cfg = parse_url("mysql://root:123456@localhost:3306/test");

        if ($cfg['scheme'] == 'mysql')
        {
            if (empty($cfg['pass']))
            {
                $cfg['pass'] = '';
            }
            else
            {
                $cfg['pass'] = urldecode($cfg['pass']);
            }
            $cfg ['user'] = urldecode($cfg['user']);

            if (empty($cfg['path']))
            {
                trigger_error('Invalid database name.', E_USER_ERROR);
            }
            else
            {
                $cfg['path'] = str_replace('/', '', $cfg['path']);
            }

            $charset = 'utf8';
            $db = new cls_mysql();
            //$db->cache_dir = ROOT_PATH. '/temp/query_caches/';
            $db->connect($cfg['host']. ':' .$cfg['port'], $cfg['user'],
                $cfg['pass'], $cfg['path'], $charset);
        }
        else
        {
            trigger_error('Unkown database type.', E_USER_ERROR);
        }
    }

    return $db;
}
$db_OBJ = &db();
$d = trim($_GET['d']);
if($d!="")
{
	$result = perform_whois($d);
	//$result = isRegister($d);
	if($result)
	{
	   $db_OBJ->query("insert into `domain_can_use` (`domain`)values('".$d."')");
	}else
	{
	   $table_name = substr($d,0,1);
	   $db_OBJ->query("insert into `domain_$table_name` (`domain`) values ('".$d."')");
	}
	echo json_encode(array('done'=>$result?true:false));
}else
{
    echo "5222";
}
