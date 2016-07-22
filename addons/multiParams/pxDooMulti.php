<?php
//pxDooMulti

/*
*
*
*
*	&tpl=`NameMyChunk` 
*	&tv=`testTV`
*	&resourceId=``
*	[[pxDooMulti? &tpl=`NameMyChunk_COLORS` &tv=`testTV` &resourceId=`115`]]
*
*/	
	
	
if (!is_numeric($resourceId)){
	$resourceId = $modx->documentIdentifier;
}	


$err = false;
$content='';

//getTVtype AND id
if (isset($tv)){
	$sql = "SELECT type, id FROM ". $modx->getFullTableName( 'site_tmplvars' ) ." WHERE  `name` = '".$tv."' LIMIT 1 ";
	$result = mysql_query($sql);
	if ($result && mysql_num_rows($result) > 0) {
		if ($tmp = mysql_fetch_assoc($result)){
			$typeTV = $tmp['type'];
			$idTV = $tmp['id'];
		} else $err = true;
	}else $err = true;
}else $err = true;



//getChunkBody
if (!$err) {
	if (isset($tpl)){
		$sql = "SELECT snippet FROM ". $modx->getFullTableName( 'site_htmlsnippets' ) ." WHERE  `name` = '".$tpl."' LIMIT 1 ";
		$result = mysql_query($sql);
		if ($result && mysql_num_rows($result) > 0) {
			if ($tmp = mysql_fetch_assoc($result)){
				$chunkBody = $tmp['snippet'];
			} else $err = true;
		}else $err = true;
	}else $err = true;
}


//getTVvalue
if (!$err) {
	if (isset($tpl)){
		$sql = "SELECT value FROM ". $modx->getFullTableName( 'site_tmplvar_contentvalues' ) ." WHERE  `tmplvarid` = ".$idTV." AND contentid = ".$resourceId." LIMIT 1 ";
		$result = mysql_query($sql);
		if ($result && mysql_num_rows($result) > 0) {
			if ($tmp = mysql_fetch_assoc($result)){
				$valueTV = $tmp['value'];
			} else $err = true;
		}else $err = true;
	}else $err = true;
}

if (!$err){
	switch ($typeTV) {
	
		case "images-multiple":
			$valueTVarr = explode('||' , $valueTV);
		
			$temp_template = $chunkBody;
			foreach ($valueTVarr AS $valueTVrow){
				$content .= str_replace ( "[+px_images+]" , $valueTVrow , $temp_template );
			}
		break;
		
		case "colors-multiple":
			
			$valueTVarr = explode('||' , $valueTV);
			foreach ($valueTVarr AS $valueTVrow){
				$valueTVrowArr = explode('::' , $valueTVrow);
				
				if (count($valueTVrowArr) == 3) {
					$temp_template = $chunkBody;
					$temp_template = str_replace ( "[+px_images+]" , $valueTVrowArr[0] , $temp_template );
					$temp_template = str_replace ( "[+px_name+]" , $valueTVrowArr[1] , $temp_template );
					$temp_template = str_replace ( "[+px_description+]" , $valueTVrowArr[2] , $temp_template );
					$content .= $temp_template;
				}
				
			}
		
		break;
		
		default:
			$valueTVarr = explode('||' , $valueTV);
			foreach ($valueTVarr AS $valueTVrow){
				$valueTVrowArr = explode('::' , $valueTVrow);
				$temp_template = $chunkBody;
				$i = 0;
				foreach ($valueTVrowArr AS $colVal){
					$temp_template = str_replace ( "[+px_params_".($i+1)."+]" , $colVal , $temp_template );
					$i++;
				}
				$content .= $temp_template;
			}
	}
}

return ($content);
?>