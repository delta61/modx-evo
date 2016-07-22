//global $modx;


$conf = array (
		'path' => "assets/sync_snippets/"
);


$catList = [];



switch ($modx->event->name) {
    case 'OnWebPageInit':
        init_px_sync($modx, $conf, $catList);
        break;
    case 'OnSnipFormSave':
        refreshSNPfile($modx , $conf);
        break;	


}






function refreshSNPfile($modx , $conf){
	
	$snippetID = $modx->event->params['id'];
	
	$sql = "SELECT ss.snippet, ss.name , cat.category FROM ".$modx->getFullTableName( 'site_snippets' )." AS ss 
			LEFT JOIN  ".$modx->getFullTableName( 'categories' )." AS cat ON cat.id = ss.category
			WHERE ss.id = ".$snippetID;
	
	$result = mysql_query($sql);
	
	if ($result && mysql_num_rows($result) > 0) {
		if ($row = mysql_fetch_assoc($result)){
			
			
			if ($row['category'] == '') {
				$snp_path = $conf['path'].$row['name'].'.php';
			} else {
				$snp_path = $conf['path'].$row['category'].'/'.$row['name'].'.php';
			}
			
			$fileHandler = fopen ('../'.$snp_path , 'w');
				
			if ($fileHandler) {
				fwrite($fileHandler , "<?php".PHP_EOL.$row['snippet'].PHP_EOL."?>");
				fclose($fileHandler);
				@unlink('../'.$conf['path'].'___SyncDATE.log');
				rewriteLog('../'.$conf['path'] , $conf , true);
				
			}	
		}
	}
}







function init_px_sync ($modx , $conf , &$catList) {
	$sql = "SELECT * FROM ".$modx->getFullTableName( 'categories' )." LIMIT 50";
	$result = mysql_query($sql);
	if ($result && mysql_num_rows($result) > 0 ) {
		while ($row = mysql_fetch_assoc($result)){
			$catList[$row['id'] ] = $row['category']; 
		}
	}
	
	$newDirectories = addNewCatToDB($modx , $conf , $catList);
	if ($newDirectories) {
		createNewSnippet($modx , $conf['path'] , $conf);
	}
	

	if (makeDir($catList , $conf)) {
		if (sync($modx , $conf , $catList) === true){
			@unlink($conf['path'].'___SyncDATE.log');
			if (rewriteLog($conf['path'] , $conf , false) === true) {
				header("Location: http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
			}	
		}
	}
}







function createNewSnippet($modx , $path, $conf) {
	
	$dirHandler = opendir($path);
	while (($file = readdir($dirHandler)) !== false) {
        if ($file == '.' || $file == '..' || $file == "___SyncDATE.log") continue;
		
		if (is_dir($path.$file)) {
			//recurssive
			createNewSnippet($modx , $path.$file."/", $conf);
		}elseif (is_file($path.$file)) {
			
			if (!file_exists($path.$file)) return false;
			$fileName = explode('.', $file);
			$fileName = $fileName[0];
			$idCategory = 0;

			$sql = "SELECT id FROM  ".$modx->getFullTableName( 'site_snippets' )." WHERE name = '".$fileName."' ";
			if ($result = mysql_query($sql)){
				if (mysql_num_rows($result) == 0) {
					$pathWay = explode('/' , $path);
					print_r($pathWay);
					if (count($pathWay) > 0){
						
						$rootPath = explode('/', $conf['path']);
						$rootPath = $rootPath[count($rootPath)-2];
						if ($pathWay[count($pathWay)-2] == $rootPath) {
							$idCategory = 0;
						} else {

							$catName = $pathWay[count($pathWay)-2];
							echo '__'.$catName.'<br>';
							
							$sql = "SELECT id FROM ".$modx->getFullTableName( 'categories' )." WHERE category = '".$catName."' LIMIT 1";
							if ($result = mysql_query($sql)) {
								if (mysql_num_rows($result) > 0){
									if ($row = mysql_fetch_assoc($result)) {
										$idCategory = $row['id'];
									}
								}
							}
						}


						$snp_code =  file($path.$file);
						$snp_code[0] = '';
						$snp_code[count($snp_code) -1 ] = '';
						$snp_code = implode("", $snp_code);
						$snp_code = mysql_escape_string($snp_code);

						$sql = "INSERT INTO ".$modx->getFullTableName( 'site_snippets' )." (name , category , snippet) VALUES ('".$fileName."' , '".$idCategory."' , '".$snp_code."') ";
						$result = mysql_query($sql);
						
						if ($result) {
							//rewriteLog($conf['path'] , $conf);
						}

					}
				}
			}
		}	
	}
}







function addNewCatToDB($modx , $conf, &$catList) {
	
	if (file_exists($conf['path'])) {
		$dirHandler = opendir($conf['path']);
	}else {
		return false;
	}
	
	$err = false;
	$fs_dirs = [];
	
	
	while (($file = readdir($dirHandler)) !== false) {
        if ($file == '.' || $file == '..' ) continue;
		
		if (is_dir($conf['path'].$file)) {
			$fs_dirs[] = $file;
		}
	}
	
	
	if (count($fs_dirs) > 0) {
		$newDirs = array_diff($fs_dirs , $catList);
	}
	
	
	if (count($newDirs) > 0) {
		
		foreach($newDirs AS $val) {
			$sql = "INSERT INTO ".$modx->getFullTableName( 'categories' )." (id, category) VALUES (NULL , '".$val."')";
			$result = mysql_query($sql);
			if ($result) {
				$sql_last = "SELECT id FROM ".$modx->getFullTableName( 'categories' )." WHERE category = '".$val."' ";
				$result_last = mysql_query($sql_last);
				if ($result_last) {
					if ($lastId = mysql_fetch_assoc($result_last)) {
						$catList[$lastId['id'] ] = $val;
					}
				}
				
			}else {
				$err = true;
			}
		}
	}
	
	
	
	if ($err) {
		return false;
	} else {
		if (count($newDirs) > 0) {
			return $newDirs;
		} else {
			return true;
		}
	}
	
}







function makeDir ($catList , $conf) {
	$err = false;
	
	if (count($catList) > 0) {
		if (!file_exists($conf['path'])) {
			if (!mkdir ($conf['path'])) {
				$err = true;
			}
		} 
		
		foreach ($catList AS $key => $value){
			
			if (!file_exists($conf['path'].$value)) {
				if (!mkdir ($conf['path'].$value, 0777, true)){
					$err = true;
					break;
				}
			}	
		}
		
	} else {
		
		$err = true;
	}
	
	
	
	if ($err) {
		return false;
	} else {
		return true;
	}
	
	
}







function sync ($modx , $conf , $catList) {

	if (count($catList) < 1) return false;
	$sql = "SELECT * FROM ".$modx->getFullTableName( 'site_snippets' )." LIMIT 500";
	$result = mysql_query($sql);
	
	if ($result && mysql_num_rows($result) > 0 ) {
		
		$dateSyncFileHandler = fopen ($conf['path'].'___SyncDATE.log' , 'a+');
		$logDataArr = [];
		$logData = @fread($dateSyncFileHandler, filesize($conf['path'].'___SyncDATE.log'));	
		$logData = explode(PHP_EOL,$logData);
		
		if (count($logData) > 0){
			foreach ($logData AS $tmp){
				$tmp = explode("::" , $tmp);
				$logDataArr[ $tmp[0] ] = $tmp[1];
			}
		}

		$refreshProcess = false;
		while ($row = mysql_fetch_assoc($result)){
			$snp_path = $conf['path'].$catList[ $row['category'] ].'/'.$row['name'].'.php';

			if (file_exists($snp_path)){
				if (filemtime($snp_path) > $logDataArr[$row['name'].'.php' ]) {	
					if (refreshDBsnippet($modx , $snp_path, $row['name'], $catList) === true){
						$refreshProcess = true;
					}
					
				}
				
			} else {

				$fileHandler = fopen ($snp_path , 'a+');
				
				if ($fileHandler) {
					fwrite($fileHandler , "<?php".PHP_EOL.$row['snippet'].PHP_EOL."?>");
					fclose($fileHandler);
				}
				
				fwrite($dateSyncFileHandler , $row['name'].'::'.filemtime($snp_path).PHP_EOL);
			}
			
		}
		fclose($dateSyncFileHandler);
	}
	if ($refreshProcess) {
		return true;
	} else return false;
	
}





function refreshDBsnippet($modx , $snp_path, $snp_name,  $catList) {
	
	//add verify
	$snp_code =  file($snp_path);
	$snp_code[0] = '';
	$snp_code[count($snp_code) -1 ] = '';
	$snp_code = implode("", $snp_code);
	$snp_code = mysql_escape_string($snp_code);
	
	$sql = "UPDATE ".$modx->getFullTableName( 'site_snippets' )." SET snippet = '".$snp_code."' WHERE name = '".$snp_name."' LIMIT 1";
	$result = mysql_query($sql) or die ("ERR 564 ".mysql_error());
	
	if ($result){
		clearCache_px($modx);
		return true;
	} else {
		return false;
	}
	
}







function clearCache_px($modx) {
	$modx->clearCache();
	include_once MODX_BASE_PATH . 'manager/processors/cache_sync.class.processor.php';
	$sync= new synccache();
	$sync->setCachepath( MODX_BASE_PATH . "assets/cache/" );
	$sync->setReport( false );
	$sync->emptyCache();
}







function rewriteLog($path , $conf , $outMNG) {

	if ($outMNG) {
		$upPath = '../';
	}else {
		$upPath = '';
	}
	
	 
	$dirHandler = opendir($path);
	while (($file = readdir($dirHandler)) !== false) {
        if ($file == '.' || $file == '..' ) continue;
	
		if (is_dir($path.$file)) {
			rewriteLog($path.$file.'/', $conf , $outMNG);
		}else {
			$fp = fopen($upPath.$conf['path'].'___SyncDATE.log' , 'a');
			fwrite($fp , $file.'::'.filemtime($path.$file).PHP_EOL);
			fclose($fp);
		}
    }
	
	return true;
	
}
