<?php

error_reporting(7);


	
	include_once( 'config.php' );
	$database_connection_charset = 'utf8';
	$database_connection_method = 'SET NAMES';
	
	define(SITE_DONOR, 'http://matrix-bt.ru');
	
	//DB_PREFIX
	mysql_connect( DB_HOSTNAME, DB_USERNAME, DB_PASSWORD );
	mysql_select_db( DB_DATABASE );
	$rr= mysql_query( "{$database_connection_method} {$database_connection_charset}" );
	
	//error_reporting( 7 );
	//ini_set( 'display_errors', 'On' );
	//ini_set( 'display_errors', 'Off' );
// ----------------------------------------------------------------------------------

	$table= DB_PREFIX.'aaa_matrix';
	$root= '/home/o/osepya2h/osepya2h.bget.ru/public_html';
	$interval= 60*60*24;
	$parser_page_count= 100;
	
// ----------------------------------------------------------------------------------

	
$arrlinks = array(
				0 => '/posudomoechnye-mashiny/',
				1 => '/duhovye-shkafy/',
				2 => '/mikrovolnovye-pechi/',
				3 => '/stiralnye-mashiny/',
				4 => '/vytyazhki-dlya-kuhni/',
				5 => '/varochnye-poverhnosti/',
				6 => '/kuhonnye-mojki/',
				7 => '/holodilniki/'
			);

prepaireExec($arrlinks);


echo mysql_error();





	
function prepaireExec($arrlinks){
	
	$day = date("d");
	//SELECT * FROM oc_aaa_filters_links AS fl , oc_aaa_filters_collation AS fc WHERE (fl.day = 20 AND fl.dop = 'noProcessed') OR (fc.day = 20 AND fc.state = 'noProcessed')
	//SELECT * FROM oc_aaa_filters_links WHERE day = {$day} AND dop = 'noProcessed' 
	$sql = "SELECT * FROM oc_aaa_filters_links WHERE day = {$day} ";
	if (runSQLnum($sql) > 0) {
		
		$sql = "SELECT id FROM oc_aaa_filters_links WHERE day = {$day} AND dop = 'noProcessed' AND type = 'maincat' ORDER BY id ASC  LIMIT 1";
		if (runSQLnum($sql) > 0) {
			echo '11';
			getPagesLinkInCat();
		}else{
			
			$sql = "SELECT id FROM oc_aaa_filters_links WHERE day = {$day} AND dop = 'noProcessed' AND type = 'sub' ORDER BY id ASC  LIMIT 1";
			if (runSQLnum($sql) > 0) {
				echo '22';
				getItemsInPage();
			}else{
				$sql = "SELECT id,namefilter,article FROM oc_aaa_filters_collation WHERE day = {$day} AND state = 'noProcessed' ORDER BY id ASC  LIMIT 100";

					if ($resultItm = mysql_query($sql)){
						if (mysql_num_rows($resultItm) > 0){
							echo '33';
							while($rowItm = mysql_fetch_assoc($resultItm)){
								
								processedFilters($rowItm['namefilter'],$rowItm['article'],$rowItm['id']);
							}
						}
						
					}
					
				echo 'endPoint';
				
			}
			
		}
		
	}else{
		mysql_query("TRUNCATE oc_aaa_filters_links");
		mysql_query("TRUNCATE oc_aaa_filters_collation");
		foreach ($arrlinks as $elem) {
			insertLinkSQL('http://matrix-bt.ru'.$elem);
		}
	}
	
}

	
	

	
	
	
	
	
	
	
function processedFilters($vendor , $art, $helper) {
	if (strlen($vendor) > 0) {
		$result_vend_f = mysql_query("SELECT filter_id FROM `oc_filter_description` WHERE name = '{$vendor}' LIMIT 1") or die (mysql_error());
		if (mysql_num_rows($result_vend_f) > 0) {
			if ($tmp = mysql_fetch_assoc($result_vend_f)) {
				$filterID = $tmp['filter_id'];
			}else {
				$errrr = true;
			}
		}else {
			$result_ins_new = mysql_query("INSERT INTO `oc_filter` (filter_group_id, sort_order) VALUES (5,0) ") or die (mysql_error());
			if ($result_ins_new) {
				$filterID = mysql_insert_id ();
				if (mysql_query("INSERT INTO `oc_filter_description` (filter_id, language_id , filter_group_id , name) VALUES ( {$filterID}, 6,5 , '{$vendor}') ")){
							
				}else $errrr = true;
			}else {
						
				$errrr = true;
			}
					
		}
		if (!$errrr) {
			$result_prodId = mysql_query("SELECT product_id FROM `oc_product` WHERE sku LIKE '%{$art}%'LIMIT 1") or die (mysql_error());
			if ($tmp = mysql_fetch_assoc($result_prodId)) {
				print_r($tmp);
				$prodId = $tmp['product_id'];

				mysql_query("INSERT INTO  `oc_product_filter` ( product_id ,  filter_id ) VALUES  ( {$prodId} ,  {$filterID} ) 
						ON DUPLICATE KEY UPDATE product_id={$prodId} , filter_id={$filterID}") or die (mysql_error());				
				mysql_query("UPDATE `oc_aaa_filters_collation` SET state = 'processed'  WHERE id = {$helper} ") or die (mysql_error());			
			
			}
		}			
	}
}









function getItemsInPage() {
	$day = date("d");
	$sql = "SELECT * FROM oc_aaa_filters_links WHERE day = {$day} AND type = 'sub' AND dop = 'noProcessed' ORDER BY id ASC LIMIT 1";
	$noERR = true;
	
	if ($result = mysql_query($sql)){
		if ($data = mysql_fetch_assoc($result) ) {
			$pageBody = getPageBody($data['link']);
			$articlesList = getArticle($pageBody);
			
			echo '<pre>';
			print_r($articlesList);
			echo '</pre>';
			echo '<hr>';
			
			foreach($articlesList as $art) {
				if (!insertFilterToArt($art , $data['dop2'])){
					$noERR = false;
				}
			}
			
			if ($noERR) setStatus($data['id'] , 'processed');

		}
	}
	echo mysql_error();
	return false;
}





	
	

function getPagesLinkInCat() {
	$day = date("d");
	$sql = "SELECT * FROM oc_aaa_filters_links WHERE day = {$day} AND type = 'maincat' AND dop = 'noProcessed' LIMIT 1";
	$noERR = true;
	
	if ($result = mysql_query($sql)){
		
		if ($data = mysql_fetch_assoc($result) ) {
		
			$pageBody = getPageBody($data['link']);
			
			$filterList = getFilterList($pageBody);
			
			foreach($filterList as $elem){
				$linkWithFilter = $data['link'].'type/'.$elem.'/';
				$pageBody = getPageBody($linkWithFilter);
				$allPaginationList = getFullPagination($pageBody , $linkWithFilter);
					
				if (!insertEndedLink($elem , $allPaginationList)) {
					$noERR = false;
				}
			}
			if ($noERR) setStatus($data['id'] , 'processed');
		}
	}
	echo mysql_error();
	return false;
}












function insertFilterToArt($article , $filterName) {
	$day = date("d");
	$noERR = true;

	$sql = "INSERT INTO oc_aaa_filters_collation (namefilter,article, state ,day) VALUES ('{$filterName}' , '{$article}','noProcessed', '{$day}' )";
	if (!$result = mysql_query($sql)){
		$noERR = false;
	} 

	return $noERR;
}
	
	
	
	
	
	
	
	

function insertEndedLink($filterType , $allPaginationList) {
	$day = date("d");
	$noERR = true;

	
	foreach ($allPaginationList as $elem) {
		$sql = "INSERT INTO oc_aaa_filters_links (link,day, type ,dop,dop2) VALUES ('{$elem}' , '{$day}', 'sub','noProcessed', '{$filterType}' )";
		if (!$result = mysql_query($sql)){
			$noERR = false;
		}
	
	}
	return $noERR;
}
		
	
	
	
	
	
	
function insertLinkSQL($row) {
	$day = date("d");
	$sql = "INSERT INTO oc_aaa_filters_links (link,day, type ,dop) VALUES ('{$row}' , '{$day}', 'maincat','noProcessed' )";
	if ($result = mysql_query($sql)){
		return mysql_affected_rows();
	}
	return false;
}
		

		
		
		
		
		
		
function setStatus($id , $status) {

	$sql = "UPDATE oc_aaa_filters_links SET dop = '{$status}' WHERE id = {$id} ";
	if ($result = mysql_query($sql)){
		return mysql_affected_rows();
	}
	return false;
}
		
	
	
	
	
	
	
function runSQLnum($sql) {
	if ($result = mysql_query($sql)){
		return mysql_num_rows($result);
	}
	return false;
}
	
	
	
	
	
	
	
	
function getPageBody($url){
    return file_get_contents_curl($url);
    //return file_get_contents($url);
}









function file_get_contents_curl($url) {
    $referer = 'http://yandex.ru/clck/jsredir?from=yandex.ru%3Bsearch%2F%3Bweb%3B%3B&text=&etext=1197.b07BA0lB9YM4PpqeFb_iOhrzVRPGaAUmCz63-aD_Fnk.0557c404b54e9c43e594c720743a46232a73019d&uuid=&state=PEtFfuTeVD5kpHnK9lio9QkU1tHIaqSGmpn3NHuF9Zj21qB0RdKWapXypJivwxactDn6kzaP4iQXGNM7vYJGNFF8es9Gr732_UVQu-cCkPeBvPkO-xhXew&data=UlNrNmk5WktYejR0eWJFYk1LdmtxdExQUW1oeDUxMTBKc3FEQTk1UXRZblQwc0c3alhhd2hCejN5bGFldDBnR09nVmVBbnFDakppUldoRUFtS0lGd2d3cmRxUmNKTGVX&b64e=2&sign=ff2057ff5a99820e942bd632598035fd&keyno=0&cst=AiuY0DBWFJ4EhnbxqmjDhbS8bVGqa_DTxYhhtVtOuIQ7IArdmYKgciBS12jE8ONTwAM_N-RhAMvnPQwOQiTLGIm5ZY7f0lBGKqrmtQuMRZA_cU_KVi9MNWRovSJ-IkN_d5r7JaJZlqLu1z_YaE7QNXb3WRQOWpx49jCtLIazGMNjjTm2iWvdh7P6rs2Ku0tqlc0F4S9SpO-xvl6vFj7ZpsSbZkYowdck2buxYUCUOUR76WcjQRG_oFX_iYPH4teCd-bqV-TYIZ3kUighxQYSmWklTZuz5h7QpDPw0YhfIg8G831NTkyZ5c7fvV2BaZ-i9UWstUq8MDnmTL6shhp096Wxw6hzEE1KXYXNmpGfP1ROSyjgfhXdcbo58smGxkAbtFvMks9caIoHwPSi0ztsbD1ZVIBL1KVjd3j4RvnbKmj0rrnc-VdPLpAo2Or3yg7Tk18LT1DKxiph3Wdt6wTjFOcLGD3AnqHaeg91mGSEVaBYI87R328aNurt38iteDozOPE_tqVJDiQt_q86JAGRQoLO_ugTwszspjuzfVQC49Nw128IX7AUb6RMt4MvBVmqRP_TAguxk3L1QHALNjHIKQSL1M5aGeeDAWbZHdc1BC91Vzv9ex0tKlUqLFTlcXTWxr1GijBjQ439ZdCBIx_-g1op5MjwF_nAIJxVl-jYN4g8GF8MTMdXpw&ref=orjY4mGPRjk5boDnW0uvlrrd71vZw9kpD8T26WvrKY5fN2l0j7eLTgYmhYbu9ZreDZrq1qPQcB8P5fguU0YpR4eJLzi12XlV&l10n=ru&cts=1475486846552&mc=5.191410478986791';
    $useragent = 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/53.0.2785.116 Safari/537.36';
	$ch = curl_init();
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
    curl_setopt($ch, CURLOPT_COOKIEJAR, "curlCookies.txt");
    curl_setopt($ch, CURLOPT_COOKIEFILE, "curlCookies.txt");
    curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
    curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
	curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_REFERER, $referer);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_URL, $url);
	$data = curl_exec($ch);
	curl_close($ch);
	return $data;
}










function parsePage($pageBody){
    /*
    *must return array or false;
    *
    *
    */
    
    require_once( 'mymodules/phpQuery/phpQuery-onefile.php' );
    
    $document = phpQuery::newDocument($pageBody);
    $mainBlock = $document->find('div.s-block');
    $prices = $mainBlock->find('i.bp-price');
    
    foreach ($prices AS $onePrice) {
        $pq = pq($onePrice);
        $data['price'][] = $pq->text();
    }
}












function getFullPagination($pageBody , $helper){
	$pagelistinpage = getPaginateInPage($pageBody);
	end($pagelistinpage);
	$maxKey = key($pagelistinpage);
	$maxPageLink = $pagelistinpage[$maxKey];
	
	while (true){
		$pageBody = getPageBody(SITE_DONOR.$maxPageLink);
		$pagelistinpageInner = getPaginateInPage($pageBody);	
		end($pagelistinpageInner);
		$maxKeyInner = key($pagelistinpageInner);
		
		
		if ($maxKeyInner == $maxKey) {
			break;
		}else {
			$maxPageLink = $pagelistinpageInner[$maxKeyInner];
			$maxKey = $maxKeyInner;
			$pagelistinpage = array_merge($pagelistinpage , $pagelistinpageInner);
		}
	}
	

	$pagelistinpage = array_unique ( $pagelistinpage );
	
	if (count($pagelistinpage) > 0 ) {
		return ($pagelistinpage);
	}else return array(0=>$helper);


}












function getPaginateInPage($pageBody){
    require_once( 'mymodules/phpQuery/phpQuery-onefile.php' );
    $document = phpQuery::newDocument($pageBody);
    $links = $document->find('ul.pagination .item a');
	$result = array();
    foreach ($links AS $link) {
		$pq = pq($link);
		$result[$pq->text()] = SITE_DONOR.$pq->attr('href');
    }
	return $result;
}









function getArticle($pageBody){
    require_once( 'mymodules/phpQuery/phpQuery-onefile.php' );
    $document = phpQuery::newDocument($pageBody);
    $articles = $document->find('.item .article');
	$result = array();
    foreach ($articles AS $art) {
		$pq = pq($art);
		$result[] = $pq->text();
    }
	return $result;
}













function getFilterList($pageBody){
    require_once( 'mymodules/phpQuery/phpQuery-onefile.php' );
    $document = phpQuery::newDocument($pageBody);
    $filters = $document->find('.catalog-filter');
	$filterProps = array();
	foreach ($filters AS $filter) {
		$pq = pq($filter);
		
		if ( $pq->find(".title")->text() == 'Тип') {
			$filterList = $pq->find("input");
			foreach($filterList as $val){	
				$pqVal = pq($val);			
				$filterProps[]=$pqVal->attr('value');
			}
		}
	}
	return ($filterProps);
	
	
}






