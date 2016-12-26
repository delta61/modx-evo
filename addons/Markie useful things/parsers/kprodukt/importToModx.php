<?php
error_reporting(7);


/*
$database_type = 'mysql';
$database_server = '';
$database_user = '';
$database_password = '';
$database_connection_charset = 'utf8';
$database_connection_method = 'SET NAMES';
$dbase = '';
$table_prefix = 'kprod_';
$link = mysql_connect('localhost', '', '');
if (!$link) {
    die('Ошибка соединения: ' . mysql_error());
}

mysql_select_db('alfa-ltd-ru_kproduct') or die ('Can\'t use foo : ' . mysql_error());

*/



define('MODX_API_MODE', true);
include_once '../manager/includes/config.inc.php';
include_once '../manager/includes/document.parser.class.inc.php';
$modx = new DocumentParser;
$modx->db->connect();
$modx->getSettings();
startCMSSession();
$modx->minParserPasses=2;
define('TAB_PREFIX', $table_prefix);



$dbaseClearApost = ''; ////AAAAAAAAAAAAAAAAA!!!

$dsn = "mysql:host=$database_server;dbname={$dbaseClearApost};charset=$database_connection_charset";
$opt = array(
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
);


try {
    $pdo = new PDO($dsn, $database_user, $database_password, $opt);
} catch (PDOException $e) {
    die('Подключение не удалось: ' . $e->getMessage());
}














importToModx("./content/", "<BR>");

	
function importToModx($folder, $space) {
	
	
	$mothWords = array(
				'01' => "Январь",
				'02' => "Февраль",
				'03' => "Март",
				'04' => "Апрель",
				'05' => "Май",
				'06' => "Июнь",
				'07' => "Июль",
				'08' => "Август",
				'09' => "Сентябрь",
				'10' => "Октябрь",
				'11' => "Ноябрь",
				'12' => "Декабрь"
				);
    $files = scandir($folder);
	
	$tt = 0; 
	$max = 500;
	
	
	$root = 29;
	$tv_newsDate=6;
	$template = 1;
	
    foreach($files as $file) {

	
		if ($tt == $max) break; 

		
		if (($file == '.') || ($file == '..')) continue;
		if (preg_match ( "/_rem/iu" ,  $file)) {
			echo '_rem matched<br>'; 
			continue;
		}
		
		
		
		
		if (preg_match ( "/_imported/iu" ,  $file)) {
			echo '_imported matched<br>';
			continue;
		} 
	

		
		
		
		$f0 = $folder.''.$file.'/'; 
		$f0t = $folder.''.$file;  
		
		/****/
		/*
		if (preg_match ( "/_okay$/iu" ,  $file)) {
			echo '_k matched<br>';
			continue;
		}
		 
		$temp = explode('_impo',$file);
		
		echo $temp[0].'---<br>';
		echo $f0.'===<br>';
		
		rename ( $f0 , $folder.''.$temp[0].'/' );
		*/
		/***/
		
		
		$tt++;
		
		$pattern="/\d{10}/iu";
		preg_match ( $pattern ,  $file , $matches);
		
		print_r($matches);

		
		$dateYear = date("Y", $matches[0]);
		$dateMonth = date("m", $matches[0]); 
		
		//17-11-2016 11:22:00
		$fulldate = date("d-m-Y H:i:s", $matches[0]);

		

		if (! $art = file_get_contents($f0.'article.txt')) {continue;}
		if (! $pgtit = file_get_contents($f0.'pagetitle.txt')) {continue;}

		
		$pat2= "/(\/\>\>)/iu";
		$art = preg_replace($pat2 , "/>" , $art);
		
		$pat2= "/((height|width)=\"\d*?\">)/iu"; 
		$art = preg_replace($pat2 , "/>" , $art);
			
	
		$pat2= "/(<font size=\"[\d\s]*\">)|(<\s*\/\s*font\s*>)/iu"; 
		$art = preg_replace($pat2 , "" , $art);
			
	
		$pat2= "/(\/?\s*>\s*\/\s*>)/iu"; 
		$art = preg_replace($pat2 , "/>" , $art);
			
	
		$pat2= "/(`)/iu"; 
		$art = preg_replace($pat2 , "'" , $art);
		$pgtit = preg_replace($pat2 , "'" , $pgtit);		
	
		$pat2= "/(alt|title)=\".+?\">/iu"; 
		$art = preg_replace($pat2 , "" , $art);
	
	
	
			
		ECHO $dateYear.'<br>';
		ECHO $dateMonth.'<br>';
		ECHO $fulldate.'<br>';

	
		
		$sql = "SELECT id FROM  `kprod_site_content` WHERE parent = {$root} AND pagetitle = {$dateYear} LIMIT 1";
		$result = mysql_query($sql);
		if (mysql_num_rows($result) > 0 ) {
			$idYearPath = mysql_fetch_assoc($result)['id'];
		}else {
			$sql = "INSERT INTO  `kprod_site_content` (id,pagetitle,alias,published,parent,isfolder,template,menuindex) VALUES (NULL,{$dateYear},{$dateYear},1,{$root},1,{$template},{$dateYear} - 2008)";
			$result = mysql_query($sql);
			$idYearPath = mysql_insert_id();
		}
		
	
	
	ECHO $idYearPath.'@@<br>';
		
		
		if (is_numeric($idYearPath)) {
			
			//$dateYearMonth = $dateYear.$dateMonth;
			$dateYearMonth = $mothWords[$dateMonth];
			ECHO $dateYearMonth.'****<br>';
			
			$alias = GenerAlias($dateYearMonth);
			
			$sql = "SELECT id FROM  `kprod_site_content` WHERE parent = {$idYearPath} AND pagetitle = '{$dateYearMonth}' LIMIT 1";
			$result = mysql_query($sql);
			if (mysql_num_rows($result) > 0 ) {
				$idMonthPath = mysql_fetch_assoc($result)['id'];
			}else {
				$sql = "INSERT INTO  `kprod_site_content` (id,pagetitle,alias,published,parent,isfolder,template,menuindex) VALUES (NULL,'{$dateYearMonth}','{$alias}',1,{$idYearPath},1,{$template},{$dateMonth})";
				$result = mysql_query($sql) or die (mysql_error());
				$idMonthPath = mysql_insert_id();
			}
		}
	
	
	
	
	
		if (is_numeric($idMonthPath)) {
			
			
			$pgtit = strip_tags($pgtit);
			$pgtit = mysql_real_escape_string($pgtit);
			$art = mysql_real_escape_string($art);
			
			$pat= "/alt\s*=\s*\".+?\"\s*\/*>/iu";
			$art = preg_replace($pat , "" , $art);
			
			 
			
			
			
			$alias = GenerAlias($pgtit);
			
			
			echo $pgtit."<br>";
			$sql = "INSERT INTO  `kprod_site_content` (id,pagetitle,alias,published,parent,isfolder,template,content) VALUES (NULL,'{$pgtit}','{$alias}',1,{$idMonthPath},0,{$template},'{$art}')";
			$result = mysql_query($sql) or die ('Can\'t run SQL : ' . mysql_error());
			$idArtPath = mysql_insert_id();
			
			if (is_numeric($idArtPath)) {
				$sql = "INSERT INTO  `kprod_site_tmplvar_contentvalues` (id,tmplvarid,contentid,value) VALUES (NULL,{$tv_newsDate},{$idArtPath},'{$fulldate}')";
				$result = mysql_query($sql);

			}
			
		}
		
		rename ( $f0 , $f0t.'_imported/' );
		  
	  
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
		$rr= mysql_query("SELECT id FROM `kprod_site_content` WHERE alias='{$alias}' LIMIT 1");
		if($rr && mysql_num_rows($rr)==1) $alias .= rand(1, 9);
	}while(($rr && mysql_num_rows($rr)==1) || ! $rr);
	if( ! $rr) $alias= false;
	
	return $alias;
}




