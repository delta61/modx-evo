<?php
error_reporting(7);

include_once( 'phpQuery/phpQuery.php' );
use com\soloproyectos\common\dom\node\DomNode;




$config = array(
	'baseLink' => 'http://kprodukt.ru',
	'baseLinkPages' => 'http://kprodukt.ru/node?page=',
	'from' => 0,
	'max' => 10,
	'localPath' => 'assets/images/articles/'
	);
	
	
	
	
	
if ($state = file_get_contents('state.txt')) {
	$config['from'] = $state;
}
	
	
	
$maxPage = $config['from']+$config['max'];
	
	
	
while ($config['from'] <= $maxPage){
	if ($data = curl_loadPage($config['baseLinkPages'].$config['from'])) {
		parseOnePart($data , $config);
	}
	$config['from']++;
}
	
file_put_contents('state.txt' , $maxPage+1);



	
	
function parseOnePart($data, $config) {
	$html= phpQuery::newDocumentHTML( $data );
	$itemsList = $html->find('div.node h2 a');
	
	foreach ($itemsList as $node){
		$pageTitle = $node->nodeValue.'<br>';
		$href = pq( $node )->attr( 'href' );
		parseArticle($pageTitle, curl_loadPage($config['baseLink'].$href));
	}
	

}


function parseArticle($pageTitle, $pageContent) {
	
	
	$html= phpQuery::newDocumentHTML( $pageContent );
	$date = $html->find('#content div.node .submitted');
	$content = $html->find('#content div.node .content');
	//echo $date.'<br>';
	//echo $content.'<br>';
	echo '<hr>';
	$timestamp = parseDateToUNIX($date);
	$clearedContent = clearHTML($content);
	//echo $clearedContent;
	$clearedContent = replaceImages($clearedContent);
	
	$i = 0;
	while (true) {
		
		if (file_exists($_SERVER['DOCUMENT_ROOT'].'/contentParser/content/'.$timestamp.'_'.$i.'/')) {
			$i++;
			continue;
		}else{
			
			if (mkdir($_SERVER['DOCUMENT_ROOT'].'/contentParser/content/'.$timestamp.'_'.$i.'/')) {
				file_put_contents($_SERVER['DOCUMENT_ROOT'].'/contentParser/content/'.$timestamp.'_'.$i.'/article.txt' , $clearedContent);
				file_put_contents($_SERVER['DOCUMENT_ROOT'].'/contentParser/content/'.$timestamp.'_'.$i.'/pagetitle.txt' , $pageTitle);
				break;
			}
			
		}
		
		
		
	}
		

		
	
}



function replaceImages($content) {
	/* 
	*
	*
	*
	*/
	$pattern="/(<\s*img.*src\s*=\s*[\"'])(.*)((.)(jpe?g|png|gif))[\"']/iu";
	$contentWithNewImg = preg_replace_callback ( $pattern ,  'callback_downloadImages' , $content);
	return $contentWithNewImg;

}




function callback_downloadImages($matches) {
	/* 
	*
	*	$matches = Array
	*		(
	*			[0] => <img alt="" src="/sites/default/files/%D0%A0%D0%B5%D0%BF%D0%B5%D1%80%D1%82%D1%83%D0%B0%D1%80%20%D0%A0%D0%90%D0%A1%D0%9E%20%D0%B4%D0%B5%D0%BA%D0%B0%D0%B1%D1%80%D1%8C.JPG"
	*			[1] => <img alt="" src="
	*			[2] => /sites/default/files/%D0%A0%D0%B5%D0%BF%D0%B5%D1%80%D1%82%D1%83%D0%B0%D1%80%20%D0%A0%D0%90%D0%A1%D0%9E%20%D0%B4%D0%B5%D0%BA%D0%B0%D0%B1%D1%80%D1%8C
	*			[3] => .JPG
	*			[4] => .
	*			[5] => JPG
	*		)
	*
	*
	*/
	if ($localImgPath = curl_loadImage( $matches[2] , $matches[5] ) ) {
		return '<img src="'.$localImgPath.'" />'; 
	}
	


}




function clearHTML($content) {
	/* 
	*
	*/
	$pattern="/(\s*)((style=['\"](.*?)['\"])|(class=['\"]([\w\s*]*)['\"]))|((border|cellspacing|cellpadding|v?align|width)=['\"](.*?)['\"])|((<)[\/\s]*(tbody|hr)[\/\s]*(>))|­|<\s*o\s*:p\s*>\s*<\s*\/\s*o\s*:\s*p\s*>/iu";
	$patternLinks="/(<\s*a\s*)((href\s*=['\"].+?['\"])\s*(>))/iu";
	$patternStrongs="/(<\s*(strong|b)\s*>)(.+?)(<\s*[\/]\s*(strong|b)\s*>)/iu";
	
	$clearedContent = preg_replace ( $pattern ,  "" , $content);
	$clearedContent = preg_replace ( $patternLinks ,  '${1}${3} rel="nofollow"${4}' , $clearedContent);
	$clearedContent = preg_replace ( $patternStrongs ,  '<span class="boldText">${3}</span>' , $clearedContent);

	return $clearedContent;

}





function parseDateToUNIX($date) {
	/* 
	*	LIKES
	*	$date = 'ann ср, 30/11/2016 - 22:42'
	*	$matches = Array ( 
	*		[0] => 30/11/2016 - 22:42 
	*		[1] => 30 
	*		[2] => / 
	*		[3] => 11 
	*		[4] => 2016 
	*		[5] => 22 
	*		[6] => 42 
	*	) 
	*
	*
	*/

	$pattern="/(\d{2})(\/)(\d{2})\/(\d{4})[\s]*-[\s]*(\d{2}):(\d{2})/iu";
	$matches = array();
	preg_match ( $pattern ,  $date , $matches);
	$timeStamp = mktime($matches[5], $matches[6], 0, $matches[3], $matches[1], $matches[4]);
	return $timeStamp;

}






function curl_loadImage($link, $extension) {

	global $config;

	
	$localImgName = microtime(true).'.'.strtolower($extension);
	$localPath = $_SERVER['DOCUMENT_ROOT'].'/'.$config['localPath'].$localImgName;
	//echo 'localPath' - $localPath;
	
	$ch = curl_init($config['baseLink'].$link.'.'.$extension);
    $fp = fopen($localPath, "a+");
    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_exec($ch);
    curl_close($ch);
    fclose($fp);
	
	return $config['localPath'].$localImgName;
	
}


function curl_loadPage($url) {
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
	curl_setopt($ch, CURLOPT_AUTOREFERER, 1); 
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); 
	curl_setopt($ch, CURLOPT_MAXREDIRS, 5); 
	curl_setopt($ch, CURLOPT_URL, $url);
	$data = curl_exec($ch);
	curl_close($ch);
	return $data;
}











?>