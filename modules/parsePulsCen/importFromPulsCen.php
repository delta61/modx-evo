<?
error_reporting(7);

//INC PHPQuery


$parserEnDis = file_get_contents('parserEnDis.txt');
if ($parserEnDis == 'disable') exit("Синхронизация выключена");


$lafuup = file_get_contents('lastFullUpdate.txt');
if ($lafuup + 60 *60 *24  > time()) exit("Начало сдежующей синхронизации -  ".date("d/m/Y H:i:s",$lafuup + 60 *60 *24));



//DB CONNECT
$sqlServer = '';
$sqlUser = '';
$sqlPass = '';
$sqlDBname = '';
$sqlCharset = 'utf8';

$connect = mysql_connect($sqlServer, $sqlUser, $sqlPass) or die('Could not connect: ' . mysql_error());
mysql_select_db($sqlDBname) or die('Could select DB: ' . $sqlDBname);
mysql_query("SET character_set_results = '".$sqlCharset."',
                character_set_client = '".$sqlCharset."', 
                character_set_connection = '".$sqlCharset."', 
                character_set_database = '".$sqlCharset."', 
                character_set_server = '".$sqlCharset."'", $connect);





				
				
				
				
				
function downloadRemoteFile ($remotrFilePath, $fileName) {
    //http://sanvodsnab.pulscen.ru/system/company_yml_export/000/032/355.xml
    //product.xml
    
	$useragent = 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/53.0.2785.116 Safari/537.36';
	
    $state = false;
    $ch = curl_init($remotrFilePath);
    $fp = fopen($fileName, "w");
    curl_setopt($ch, CURLOPT_FILE, $fp);
	curl_setopt($ch, CURLOPT_COOKIEJAR, "curlCookies.txt");
    curl_setopt($ch, CURLOPT_COOKIEFILE, "curlCookies.txt");
    curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
    $state = curl_exec($ch);
	
	echo print_r( curl_getinfo (  $ch  ));
	
    curl_close($ch);
    fclose($fp);
    
    return $state;
}
  
  

//LOAD YML
function downloadRemoteFile_OLD ($remotrFilePath, $fileName) {
    //http://sanvodsnab.pulscen.ru/system/company_yml_export/000/032/355.xml
    //product.xml
    
	echo $remotrFilePath;
	echo $fileName;

    
    //$referer = 'http://yandex.ru/clck/jsredir?from=yandex.ru%3Bsearch%2F%3Bweb%3B%3B&text=&etext=1197.b07BA0lB9YM4PpqeFb_iOhrzVRPGaAUmCz63-aD_Fnk.0557c404b54e9c43e594c720743a46232a73019d&uuid=&state=PEtFfuTeVD5kpHnK9lio9QkU1tHIaqSGmpn3NHuF9Zj21qB0RdKWapXypJivwxactDn6kzaP4iQXGNM7vYJGNFF8es9Gr732_UVQu-cCkPeBvPkO-xhXew&data=UlNrNmk5WktYejR0eWJFYk1LdmtxdExQUW1oeDUxMTBKc3FEQTk1UXRZblQwc0c3alhhd2hCejN5bGFldDBnR09nVmVBbnFDakppUldoRUFtS0lGd2d3cmRxUmNKTGVX&b64e=2&sign=ff2057ff5a99820e942bd632598035fd&keyno=0&cst=AiuY0DBWFJ4EhnbxqmjDhbS8bVGqa_DTxYhhtVtOuIQ7IArdmYKgciBS12jE8ONTwAM_N-RhAMvnPQwOQiTLGIm5ZY7f0lBGKqrmtQuMRZA_cU_KVi9MNWRovSJ-IkN_d5r7JaJZlqLu1z_YaE7QNXb3WRQOWpx49jCtLIazGMNjjTm2iWvdh7P6rs2Ku0tqlc0F4S9SpO-xvl6vFj7ZpsSbZkYowdck2buxYUCUOUR76WcjQRG_oFX_iYPH4teCd-bqV-TYIZ3kUighxQYSmWklTZuz5h7QpDPw0YhfIg8G831NTkyZ5c7fvV2BaZ-i9UWstUq8MDnmTL6shhp096Wxw6hzEE1KXYXNmpGfP1ROSyjgfhXdcbo58smGxkAbtFvMks9caIoHwPSi0ztsbD1ZVIBL1KVjd3j4RvnbKmj0rrnc-VdPLpAo2Or3yg7Tk18LT1DKxiph3Wdt6wTjFOcLGD3AnqHaeg91mGSEVaBYI87R328aNurt38iteDozOPE_tqVJDiQt_q86JAGRQoLO_ugTwszspjuzfVQC49Nw128IX7AUb6RMt4MvBVmqRP_TAguxk3L1QHALNjHIKQSL1M5aGeeDAWbZHdc1BC91Vzv9ex0tKlUqLFTlcXTWxr1GijBjQ439ZdCBIx_-g1op5MjwF_nAIJxVl-jYN4g8GF8MTMdXpw&ref=orjY4mGPRjk5boDnW0uvlrrd71vZw9kpD8T26WvrKY5fN2l0j7eLTgYmhYbu9ZreDZrq1qPQcB8P5fguU0YpR4eJLzi12XlV&l10n=ru&cts=1475486846552&mc=5.191410478986791';
    $referer = 'http://yandex.ru/';
    $useragent = 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/53.0.2785.116 Safari/537.36';
    $state = false;
	
	$ch = curl_init();
	$fp = fopen($fileName, "w");
	
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
    curl_setopt($ch, CURLOPT_COOKIEJAR, "curlCookies.txt");
    curl_setopt($ch, CURLOPT_COOKIEFILE, "curlCookies.txt");
    curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
    curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_REFERER, $referer);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_URL, $remotrFilePath);
	$state = curl_exec($ch);
	
	echo print_r( curl_getinfo (  $ch  ));
	
	curl_close($ch);
	fclose($fp);
	return $state;



}
  
  
//READ YML
function iterateYMLoffersToBase ($filePath) {
    //wiith simple
    $xml= simplexml_load_file($filePath);
    if ($xml) {
        $offers= $xml->{'shop'}->{'offers'}->{'offer'}; 
    }
    
    if (is_object($offers) && count($offers) > 0) {
        
        $iterationControl = 0 ;
        foreach ($offers AS $oneItem) {
            $pageinfo['url'] =  $oneItem->{'url'};
            $pageinfo['name'] =  $oneItem->{'name'};
			$sql = "INSERT INTO berkut_aaa_px_pulscen (link,  state, title) VALUES ('".$pageinfo['url']."', 'waiting' ,'".$pageinfo['name']."')";
			mysql_query($sql);
        }
    }
    

}


//READ YML
function iterateBaseRec ($filePath) {
    //wiith simple
  
	$sql = "SELECT * FROM  `berkut_aaa_px_pulscen` WHERE state = 'waiting' ORDER BY id DESC LIMIT 10";
	if ($result = mysql_query($sql)) {
		
		if (mysql_num_rows($result) > 0) {
			
			while($row = mysql_fetch_assoc($result)){
           
				$row['title'] = mysql_escape_string($row['title']);
				$sql = "SELECT id FROM berkut_site_content WHERE  pagetitle = '".$row['title']."' LIMIT 1 ";
				//echo $sql.'<br>';
				if ($resultSC = mysql_query($sql)) {
					
					
					if (mysql_num_rows($resultSC) > 0 ) {
						//update
						
						$contentid = mysql_fetch_assoc($resultSC)['id']; /// PHP 5.4 OR >
						if ($pageBody = getPageBody($row['link'])) {
							if ($data = parsePage($pageBody)) {
								recordToBase ($data , $contentid);
								mysql_query("UPDATE `berkut_aaa_px_pulscen` SET state = 'updated' WHERE id = ".$row['id']." ");	
							}
						}
						 
					}else{
						//add
						
						if ($pageBody = getPageBody($row['link'])) {
							if ($data = parsePage($pageBody)) {
								recordToBase ($data);
								mysql_query("UPDATE `berkut_aaa_px_pulscen` SET state = 'added' WHERE id = ".$row['id']." ");								
							}
						}
						
					}
					
				}
			}
			
		}else {
			file_put_contents('parserStatement.txt' , 'allRECfinished');
			file_put_contents('lastFullUpdate.txt' , time());
			
			
		}
		
	}

}







function file_get_contents_curl($url) {
    
    $referer = 'http://yandex.ru/clck/jsredir?from=yandex.ru%3Bsearch%2F%3Bweb%3B%3B&text=&etext=1197.b07BA0lB9YM4PpqeFb_iOhrzVRPGaAUmCz63-aD_Fnk.0557c404b54e9c43e594c720743a46232a73019d&uuid=&state=PEtFfuTeVD5kpHnK9lio9QkU1tHIaqSGmpn3NHuF9Zj21qB0RdKWapXypJivwxactDn6kzaP4iQXGNM7vYJGNFF8es9Gr732_UVQu-cCkPeBvPkO-xhXew&data=UlNrNmk5WktYejR0eWJFYk1LdmtxdExQUW1oeDUxMTBKc3FEQTk1UXRZblQwc0c3alhhd2hCejN5bGFldDBnR09nVmVBbnFDakppUldoRUFtS0lGd2d3cmRxUmNKTGVX&b64e=2&sign=ff2057ff5a99820e942bd632598035fd&keyno=0&cst=AiuY0DBWFJ4EhnbxqmjDhbS8bVGqa_DTxYhhtVtOuIQ7IArdmYKgciBS12jE8ONTwAM_N-RhAMvnPQwOQiTLGIm5ZY7f0lBGKqrmtQuMRZA_cU_KVi9MNWRovSJ-IkN_d5r7JaJZlqLu1z_YaE7QNXb3WRQOWpx49jCtLIazGMNjjTm2iWvdh7P6rs2Ku0tqlc0F4S9SpO-xvl6vFj7ZpsSbZkYowdck2buxYUCUOUR76WcjQRG_oFX_iYPH4teCd-bqV-TYIZ3kUighxQYSmWklTZuz5h7QpDPw0YhfIg8G831NTkyZ5c7fvV2BaZ-i9UWstUq8MDnmTL6shhp096Wxw6hzEE1KXYXNmpGfP1ROSyjgfhXdcbo58smGxkAbtFvMks9caIoHwPSi0ztsbD1ZVIBL1KVjd3j4RvnbKmj0rrnc-VdPLpAo2Or3yg7Tk18LT1DKxiph3Wdt6wTjFOcLGD3AnqHaeg91mGSEVaBYI87R328aNurt38iteDozOPE_tqVJDiQt_q86JAGRQoLO_ugTwszspjuzfVQC49Nw128IX7AUb6RMt4MvBVmqRP_TAguxk3L1QHALNjHIKQSL1M5aGeeDAWbZHdc1BC91Vzv9ex0tKlUqLFTlcXTWxr1GijBjQ439ZdCBIx_-g1op5MjwF_nAIJxVl-jYN4g8GF8MTMdXpw&ref=orjY4mGPRjk5boDnW0uvlrrd71vZw9kpD8T26WvrKY5fN2l0j7eLTgYmhYbu9ZreDZrq1qPQcB8P5fguU0YpR4eJLzi12XlV&l10n=ru&cts=1475486846552&mc=5.191410478986791';
    
    //$referer = 'http://yandex.ru/';
    
    $useragent = 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/53.0.2785.116 Safari/537.36';
	
    echo 'curlGetContent - '.$url.'<br/>';
	
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




function getPageBody($url){
    echo $url.'<br/>';
    return file_get_contents_curl($url);
    //return file_get_contents($url);
}



function parsePage($pageBody){
    /*
    *must return array or false;
    *
    *
    *
    *
    *
    */

    
    require_once('phpQuery/phpQuery.php');
    
    
    $document = phpQuery::newDocument($pageBody);
    $mainBlock = $document->find('div.s-block');
    $prices = $mainBlock->find('i.bp-price');
    
    foreach ($prices AS $onePrice) {
        $pq = pq($onePrice);
        $data['price'][] = $pq->text();
    }
    
    
    $curency = $mainBlock->find('span.price-currency');
    $data['currency'] = $curency->text();
    
    $h1title = $mainBlock->find('h1.company-header-title');
    $data['pagetitle'] = $h1title->text();
    
    
    $breadcrumbs = $mainBlock->find('div.breadcrumbs-slider-wrapper');
    $pathWay = $breadcrumbs->find('a');

    $firstIteration = true;
    $secondIteration = true;
    foreach ($pathWay AS $onePath) {
        if ($firstIteration) {
            $firstIteration = false;
            continue;
        }
        
        if ($secondIteration) {
            $secondIteration = false;
            continue;
        }
        
        $pq = pq($onePath);
        $data['pathway'][] = $pq->find('span[itemprop=name]')->text();
    }
    
    
        
    $images = $mainBlock->find('div.fc-left-col')->find('img.ibb-img');


    
    
    foreach ($images AS $oneImage) {
        $pq = pq($oneImage);
        //tr_replace ( mixed search, mixed replace, mixed subject [, int &count] )
        $tmp = str_replace('small' , 'big' ,$pq->attr('data-original'));
        $tmp = str_replace('medium' , 'big' ,$tmp);
        $data['imageURL'][] = $tmp;
        break;
    }
    
    $fiter = true;
    $siter = true;
    foreach ($images AS $oneImage) {
        if ($fiter) {
            $fiter = false;
            continue;
        }
        
        if ($siter) {
            $siter = false;
            continue;
        }
        
        
        $pq = pq($oneImage);
        //tr_replace ( mixed search, mixed replace, mixed subject [, int &count] )
        $tmp = str_replace('small' , 'big' ,$pq->attr('src'));
        $tmp = str_replace('medium' , 'big' ,$tmp);
        $data['imageURL'][] = $tmp;
    }
    
    
    
    
    $propsList = $mainBlock->find('div.sil-item');
    
    $i = 0;
    foreach ($propsList AS $oneProps) {
        $pq = pq($oneProps);
        $nameProps = trim($pq->find('div.fc-left-col')->text());
//        $valueProps = trim($pq->find('div.fc-right-col-helper')->text());
        
        if ($nameProps == 'Цена:')  continue;
        
        if ($nameProps == 'Наличие на складе:') {
            $data['available'] = trim($pq->find('div.fc-right-col-helper')->text());
            continue;
        } 
        
        if ($nameProps == 'Условия оплаты:') {
            $data['payment'] = trim($pq->find('div.fc-right-col-helper')->text());
            continue;
        } 
        
        if ($nameProps == 'Доставка:') {
            $data['shipment'] = trim($pq->find('div.fc-right-col-helper')->text());
            continue;
        } 
        
   
        
        $data['props'][++$i]['name'] = $nameProps;
        $data['props'][$i]['value'] = trim($pq->find('div.fc-right-col-helper')->text());
    }
    
    
    
    $description = $mainBlock->find('div[itemprop=description]');
    $data['description'] = $description->html();
    
    
    
  
    
    if ($data['modxResID'] = craetePathWay($data['pathway'])){
        $data['lacalImagePath'] = loadImages($data['imageURL']);  
        //recordToBase ($data);
        return $data;
    }
    
    return false;
//    
//    echo '<pre>';
//    print_r($data);
//    echo '</pre>';
    
}




function craetePathWay($patway){
    
    $root = 66;
    $template = 5;
    if (is_array($patway) && count($patway) > 0 ){
        
        foreach ($patway  AS $elem){
            $sql = "SELECT id FROM berkut_site_content WHERE parent = {$root} AND pagetitle = '{$elem}' LIMIT 1 ";
            if ($result = mysql_query($sql)) {
				
                if (mysql_num_rows($result) > 0 ) {
                    if ($tmp = mysql_fetch_assoc($result)['id']) {
                        $root = $tmp;
                        continue;
                    }
                }else{
                    $alias = GenerAlias($elem);
                    $sql = "INSERT INTO berkut_site_content (
                    `pagetitle` , 
                    `alias` , 
                    `published` , 
                    `parent` , 
                    `isfolder` , 
                    `template`
                    ) VALUES (
                    '".$elem."',
                    '".$alias."',
                    '1',
                    '".$root."',
                    '1',
                    '".$template."'
                    )";
                    mysql_query($sql) or die (mysql_error());
                    $root = mysql_insert_id();
                }
                
            }
		
        }
        return $root;
        
        
    }
    return false;
}
   


   
   
   
   
   
   
   
   
   
   
   



function recordToBase($data, $contentid = false) {
    
    $root = 66;
    $template = 5;
    $tv_price_min = 15;//
    $tv_avaiable = 19;//
    $tv_descr = 12;
    $tv_props = 22;//
    $tv_shipment = 20;
    $tv_imagesMulti = 21;//
    $tv_imageMain = 1;//
        
	$modxid = false;
	
    if (is_array($data) && count($data) > 0 ){    
		$data['description'] = mysql_escape_string($data['description']);
        $alias = GenerAlias($data['pagetitle']);
        $sql = "INSERT INTO berkut_site_content (
                `pagetitle` , 
                `alias` , 
                `published` , 
                `parent` , 
                `isfolder` , 
                `template`
                ) VALUES (
                '".$data['pagetitle']."',
                '".$alias."',
                '1',
                '".$data['modxResID']."',
                '0',
                '".$template."'
                )"; 
        	
		if (func_num_args() > 1 && is_numeric($contentid)) {
			$modxid = $contentid;
		}else{
			if ($result = mysql_query($sql) ) {
				
				$modxid = mysql_insert_id();
			}
			
		}
				
        if ($modxid) {
			
			$data['available'] = $data['available'] == 'в наличии' ? 1:0;
			$data['shipment'] = $data['shipment'] == 'Есть' ? 1:0;
			
			/*
            if ($data['available'] == 'в наличии') {
                $data['available'] = 1;
            }else {
                $data['available'] = 0;
            }
                  
            if ($data['shipment'] == 'Есть') {
                $data['shipment'] = 1;
            }else {
                $data['shipment'] = 0;
            }
            */
     
            $multiSTR = '';
            if (is_array($data['props']) && count($data['props']) > 0) {
                foreach ($data['props'] as $tmpVal){
                    $tmpVal['name'] = trim(str_replace(':' , '' , $tmpVal['name']));
                    $tmpVal['value'] = trim(str_replace(':' , '' , $tmpVal['value']));
                    $multiSTR .= $tmpVal['name'].'::'.$tmpVal['value'].'||';   
                }
                $multiSTR = mb_substr($multiSTR, 0 , -2 , 'UTF-8');
            }
                
            
            $multiIMAGE = '';
            if (is_array($data['lacalImagePath']) && count($data['lacalImagePath']) > 0) {
                foreach ($data['lacalImagePath'] as $tmpVal){
                    $multiIMAGE .= $tmpVal.'||';   
                }
                $multiIMAGE = mb_substr($multiIMAGE, 0 , -2 , 'UTF-8');
            }
			

            $strWithoutChars = preg_replace('/[^0-9]/', '', $data['price'][0]);
	
            mysql_query("INSERT INTO berkut_site_tmplvar_contentvalues VALUES (NULL, {$tv_price_min} , {$modxid} , '".$strWithoutChars."' )  ON DUPLICATE KEY UPDATE `value` = '".$strWithoutChars."' ") or die(mysql_error());
            mysql_query("INSERT INTO berkut_site_tmplvar_contentvalues VALUES (NULL, {$tv_avaiable} , {$modxid} , '".$data['available']."' )  ON DUPLICATE KEY UPDATE `value` = '".$data['available']."' ")or die(mysql_error());
            mysql_query("INSERT INTO berkut_site_tmplvar_contentvalues VALUES (NULL, {$tv_descr} , {$modxid} , '".$data['description']."' )  ON DUPLICATE KEY UPDATE `value` = '".$data['description']."' ")or die(mysql_error());
            mysql_query("INSERT INTO berkut_site_tmplvar_contentvalues VALUES (NULL, {$tv_shipment} , {$modxid} , '".$data['shipment']."' )  ON DUPLICATE KEY UPDATE `value` = '".$data['shipment']."' ")or die(mysql_error());
            mysql_query("INSERT INTO berkut_site_tmplvar_contentvalues VALUES (NULL, {$tv_imageMain} , {$modxid} , '".$data['lacalImagePath'][0]."' )  ON DUPLICATE KEY UPDATE `value` = '".$data['lacalImagePath'][0]."' ")or die(mysql_error());
            mysql_query("INSERT INTO berkut_site_tmplvar_contentvalues VALUES (NULL, {$tv_imagesMulti} , {$modxid} , '".$multiIMAGE."' )  ON DUPLICATE KEY UPDATE `value` = '".$multiIMAGE."' ")or die(mysql_error());
            mysql_query("INSERT INTO berkut_site_tmplvar_contentvalues VALUES (NULL, {$tv_props} , {$modxid} , '".$multiSTR."' )   ON DUPLICATE KEY UPDATE `value` = '".$multiSTR."' ")or die(mysql_error());
           
        }
    } 

}











function loadImages($images){
    $localPath = array();
    $pathPrefix = 'assets/images/';
    
    if (is_array($images) && count($images) > 0 ){
        
        foreach ($images AS $elem){
            
            $fileName = md5($elem).'.'.end(explode("." , $elem));
            $dir = substr($fileName , 0 , 1).'/';
            $localPath[] = $pathPrefix.$dir.$fileName;
            
            if (!file_exists($pathPrefix.$dir)) {
                mkdir($pathPrefix.$dir , 0755);
            }
            
            if (!file_exists($pathPrefix.$dir.$fileName)) {
               
                $ch = curl_init($elem);
                $fp = fopen($pathPrefix.$dir.$fileName, "w");
                curl_setopt($ch, CURLOPT_FILE, $fp);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_exec($ch);
                curl_close($ch);
                fclose($fp);
                
                
            }
            
        }
    }
    
    if (count($localPath) > 0) {
        return $localPath;
    } else {
        return false;
    }
}
    
    
    

function GenerAlias($txt)
{
	$trans = array("а"=>"a", "б"=>"b", "в"=>"v", "г"=>"g", "д"=>"d", "е"=>"e",
        "ё"=>"jo", "ж"=>"zh", "з"=>"z", "и"=>"i", "й"=>"jj", "к"=>"k", "л"=>"l",
        "м"=>"m", "н"=>"n", "о"=>"o", "п"=>"p", "р"=>"r", "с"=>"s", "т"=>"t", "у"=>"u",
        "ф"=>"f", "х"=>"kh", "ц"=>"c", "ч"=>"ch", "ш"=>"sh", "щ"=>"shh", "ы"=>"y",
        "э"=>"eh", "ю"=>"yu", "я"=>"ya", "А"=>"a", "Б"=>"b", "В"=>"v", "Г"=>"g",
        "Д"=>"d", "Е"=>"e", "Ё"=>"jo", "Ж"=>"zh", "З"=>"z", "И"=>"i", "Й"=>"jj",
        "К"=>"k", "Л"=>"l", "М"=>"m", "Н"=>"n", "О"=>"o", "П"=>"p", "Р"=>"r", "С"=>"s",
        "Т"=>"t", "У"=>"u", "Ф"=>"f", "Х"=>"kh", "Ц"=>"c", "Ч"=>"ch", "Ш"=>"sh",
        "Щ"=>"shh", "Ы"=>"y", "Э"=>"eh", "Ю"=>"yu", "Я"=>"ya", " "=>"-", "."=>"-",
        ","=>"-", "_"=>"-", "+"=>"-", ":"=>"-", ";"=>"-", "!"=>"-", "?"=>"-");
		
	$alias= addslashes($txt);
	$alias= strip_tags(strtr($alias, $trans));
	$alias= preg_replace("/[^a-zA-Z0-9-]/", '', $alias);
	$alias= preg_replace('/([-]){2,}/', '-', $alias);
	$alias= trim($alias, '-');
	
	if(strlen($alias) > 20) $alias= trim(substr($alias, 0, 20), '-');
	
	do{
		$rr= mysql_query("SELECT id FROM berkut_site_content WHERE alias='{$alias}' LIMIT 1");
		if($rr && mysql_num_rows($rr)==1) $alias .= rand(1, 9);
	}while(($rr && mysql_num_rows($rr)==1) || ! $rr);
	if( ! $rr) $alias= false;
	
	return $alias;
}
    
    

    
$urlTarget = "http://www.xn----9sbboz9acobhe1h.xn--p1ai/system/company_yml_export/000/069/338.xml";
$toFileName = 'product.xml';


$statement = file_get_contents('parserStatement.txt');

if ($statement == 'allRECfinished') {
	mysql_query("TRUNCATE berkut_aaa_px_pulscen");
	downloadRemoteFile($urlTarget , $toFileName);
	file_put_contents('parserStatement.txt' , 'YMLdownloaded');
}elseif($statement == 'YMLdownloaded'){
	iterateYMLoffersToBase($toFileName);
	
	file_put_contents('parserStatement.txt' , 'OkPrepaireData');
}elseif($statement == 'OkPrepaireData'){
	iterateBaseRec($toFileName);
	

}

echo 'all'.mysql_error();

?>
