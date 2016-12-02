<?php
error_reporting(7);


	
	/*
while ($config['from'] <= $maxPage){
	if ($data = curl_loadPage($config['baseLinkPages'].$config['from'])) {
		parseOnePart($data , $config);
	}
	$config['from']++;
}*/
	
	
//dirFileTree("./content/", "<BR>");

dirFileTreeCheckMD("./content/", "<BR>");



	
function dirFileTreeCheckMD($folder, $space) {
    $files = scandir($folder);
	
	$tt = 0;
	$max = 100;
	
    foreach($files as $file) {

	
	if ($tt == $max) break;
	
    if (($file == '.') || ($file == '..')) continue;
	if (preg_match ( "/_rem/iu" ,  $file)) {
		echo 'matched<br>';
		continue;
	}
	
	
	if (preg_match ( "/_okay/iu" ,  $file)) {
		echo 'matched<br>';
		continue;
	}
	
	$tt++;
	
    $f0 = $folder.''.$file.'/'; 
    $f0t = $folder.''.$file; 
	ECHO $f0.'<br>';
	

	if (! $art = file_get_contents($f0.'article.md5')) {continue;}
	if (! $cont = file_get_contents($f0.'pagetitle.md5')) {continue;}



	$mainRename = false;
		
		
			$filesTwo = scandir($folder);

		    foreach($filesTwo as $fileInner) {
				
				if (($fileInner == '.') || ($fileInner == '..')) continue;
				if (preg_match ( "/_rem/iu" ,  $fileInner)) {
					echo 'matchedINNEr<br>';
					continue;
				}
				
				if (preg_match ( "/_okay/iu" ,  $fileInner)) {
					echo 'matched<br>';
					continue;
				}
	
				if ($file == $fileInner) continue;
				
				$f1 = $folder.''.$fileInner.'/'; 
				$f1t = $folder.''.$fileInner;
				echo $f1.'=====<br>';
				$artINNER = file_get_contents($f1.'article.md5');
				$contINNER = file_get_contents($f1.'pagetitle.md5');
			
				ECHO $art.'<br>';
				ECHO $artINNER.'<br>';
				ECHO $cont.'<br>';
				ECHO $contINNER.'<br>';
			
				if ($art == $artINNER && $cont == $contINNER){
					rename ( $f1 , $f1t.'_rem/' );
					//echo 'renamed<br>';
					$mainRename = true;
				} 
				 
				 
			}
		
	
	rename ( $f0 , $f0t.'_okay/' );
	
	  
	  
    }
 }
 
 



function dirFileTree($folder, $space) {
    $files = scandir($folder);
    foreach($files as $file) {

    if (($file == '.') || ($file == '..')) continue;
    $f0 = $folder.''.$file.'/'; 
	ECHO $f0.'<br>';
		$art = md5(file_get_contents($f0.'article.txt'));
		$cont = md5(file_get_contents($f0.'pagetitle.txt'));
		file_put_contents($f0."article.md5",$art );
		file_put_contents($f0."pagetitle.md5",$cont );
	  
    }
 }
 
