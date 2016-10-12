<?php
/*****
*px2x 11/00/2016
*rosagro
*
*/
//######## its config! begin ##########
$sc_template= 5;
$sc_root= 3;
$sc_noCategory= 32;

$tv = array(
  '1cid' => 7,
  'art' => 15,
  'price' => 9,
  'count' => 13,
  'image' => 10
);
//######## its config! end ##########



if (!stateProcessing::getBysiInfo()) exit("busy");


$benchmarkStart = microtime(true);

define('MODX_API_MODE', true);
include_once 'manager/includes/config.inc.php';
include_once 'manager/includes/document.parser.class.inc.php';
$modx = new DocumentParser;
$modx->db->connect();
$modx->getSettings();
startCMSSession();
$modx->minParserPasses=2;
define('TAB_PREFIX', $table_prefix);


$pattern = "/[A-z]/ui"; //написать нормальную регулярку
$replacement = "";
$dbaseClearApost =  preg_replace($pattern, $replacement, $dbase);

$dsn = "mysql:host=$database_server;dbname=$dbaseClearApost;charset=$database_connection_charset";
$opt = array(
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
);

try {
    $pdo = new PDO($dsn, $database_user, $database_password, $opt);
} catch (PDOException $e) {
    die('Подключение не удалось: ' . $e->getMessage());
}

if (!stateProcessing::getBysiInfo()) exit("busy");




$session_name= session_name();
$session_id= session_id();

class Logger {

    private static $modx;
    private static $pdo;


    function __construct($modx) {
        // self::$modx = $modx;
    }



    public static function setModxObject($modx, $pdo) {
        self::$modx = $modx;
        self::$pdo = $pdo;
    }


    public static function set($text) {
      $text = mysql_real_escape_string($text);
      $sql =  "INSERT INTO ". self::$modx->getFullTableName( '_1c_log' ) ." SET dth= :dth , text= :text ";
      $stm = self::$pdo->prepare($sql);
      $stm->execute(array('dth' => date( 'Y-m-d-H-i-s' ), 'text' => $text));
    }
}


Logger::setModxObject($modx , $pdo);
Logger::set('catalog::start');



class stateProcessing {

    protected $modx;
    protected $pdo;
    protected static $pdost;
    protected $table_1c;
    protected static $table_1cst;
    protected $sqlTabIDoffers;
    protected $sqlTabIDimport;
    public $pathToImport;
    public $pathToOffers;
    protected $countChunks = array();



    function __construct($modx,$pdo) {
        $this->table_1c = $modx->getFullTableName( '_1c_' );
        self::$table_1cst = $modx->getFullTableName( '_1c_' );
        $this->modx = $modx;
        $this->pdo = $pdo;
        self::$pdost = $pdo;
        $this->replaceOldFile();
    }



    public function getFileTabID($type){
      if ($type === 'offers') {
          return $this->sqlTabIDoffers;
      }elseif($type === 'import') {
          return $this->sqlTabIDimport;
      }
    }



    public function replaceOldFile (){
        $sql = "UPDATE {$this->table_1c} SET `check` = ? , dt = ? WHERE `check` = 'new' AND dt + 60*60*3 < UNIX_TIMESTAMP()";
        $DBstatement = $this->pdo->prepare($sql);
        $DBstatement->execute(['old',time()]);
    }



    public function setCountChunks ($key , $count){
        $this->countChunks[$key] = $count;
    }



    public function getChunkState ($type) {
        //$type = import
        //$type = offers
        try {
            $sql = "SELECT currentChunk, sumChunk  FROM {$this->table_1c} WHERE `fileType` = ?  AND `check` = 'chunked' AND currentChunk < sumChunk + 1 ";
            $DBstatement = $this->pdo->prepare($sql);
            $DBstatement->execute([$type]);
            $DBstatement->setFetchMode(PDO::FETCH_ASSOC);
            if ( $DBstatement->rowCount() == 1) {
              return  $DBstatement->fetch()['currentChunk'];
            }else {
              return false;
            }
        } catch (PDOException $e) {
            //echo $e->getMessage();
            return false;
        }
    }




    public function setNextChunk ($type) {
        //$type = import
        //$type = offers
        try {
            $sql = "UPDATE {$this->table_1c} SET currentChunk = currentChunk + 1 WHERE `fileType` = ?  AND `check` = 'chunked' AND currentChunk < sumChunk + 1";
            $DBstatement = $this->pdo->prepare($sql);
            $DBstatement->execute([$type]);
            if ( $DBstatement->rowCount() == 1) {
              return  true;
            }else {
              return false;
            }
        } catch (PDOException $e) {
            //echo $e->getMessage();
            return false;
        }
    }


    public function setBusy($type) {
        //$type = import
        //$type = offers
        $handle = fopen("xml_chunks/busyState.obj", "w");
        fwrite ( $handle , 'busy' );
        fclose($handle);
    }



      public function clearBusy($type) {
            //$type = import
            //$type = offers
            @unlink("xml_chunks/busyState.obj");
        }




    public function checkNewfile (){
        $sql = "SELECT id FROM {$this->table_1c} WHERE `check` = 'new' ";
        $DBstatement = $this->pdo->prepare($sql);
        $DBstatement->execute();
        return $DBstatement->rowCount();
    }



    public function setFilePath (){
        $sql = "SELECT filename, id FROM {$this->table_1c} WHERE `check` = 'new' ";
        $DBstatement = $this->pdo->prepare($sql);
        $DBstatement->execute();
        $DBstatement->setFetchMode(PDO::FETCH_ASSOC);
        if ( $DBstatement->rowCount() == 2) {
            while($row = $DBstatement->fetch()) {
                if (mb_substr($row['filename'],-10,null,'UTF-8') == 'import.xml') {
                    $this->pathToImport = $_SERVER['DOCUMENT_ROOT'].'/mybox/1c/'.$row['filename']; //убрать  /mybox/1c/ в конфиг отсюда нах!
                    $this->sqlTabIDimport = $row['id'];
                }elseif(mb_substr($row['filename'],-10,null,'UTF-8') == 'offers.xml'){
                    $this->pathToOffers = $_SERVER['DOCUMENT_ROOT'].'/mybox/1c/'.$row['filename']; //убрать  /mybox/1c/ в конфиг отсюда нах!
                    $this->sqlTabIDoffers = $row['id'];
                }
            }
        }
    }



    public static function setInfoToDB($sumChunk,$id , $fileType){
        $sql = "UPDATE ".self::$table_1cst." SET `check` = 'chunked' , fileType = :fileType , currentChunk = :currentChunk , sumChunk = :sumChunk , lastTime =  UNIX_TIMESTAMP() WHERE  `id` = :id ";
        $pdo = self::$pdost;
        $DBstatement = $pdo->prepare($sql);
        try {
            $DBstatement->execute(array('fileType' => $fileType , 'currentChunk' => 0 , 'sumChunk' => $sumChunk, 'id' => $id));
        } catch (PDOException $e) {
            die('Переданы неверные параметры: ' . $e->getMessage());
        }
    }


    public static function getBysiInfo(){

        if (file_exists('xml_chunks/busyState.obj')){
          return false;
        }else return true;
    }


}







require_once "SimpleXMLReader.php";

class ExXMLreader extends SimpleXMLReader
{
	public $counterTree = 0;
	public $counterTMP = 0;
	public $counterProduct = 0;
	public $counterOffers = 0;
	public $xml;
	public $firstElem = true;


    public function __construct(){
        $this->registerCallback("/КоммерческаяИнформация/Каталог/Товары/Товар", array($this, "callbackProduct"));
        $this->registerCallback("/КоммерческаяИнформация/Классификатор/Группы", array($this, "callbackTree"));
        $this->registerCallback("/КоммерческаяИнформация/ПакетПредложений/Предложения/Предложение", array($this, "callbackPrice"));
    }


    protected function callbackPrice($reader){
		$this->xml = $reader->expandSimpleXml();
		if ($this->counterTMP == 500) {
      $fileHandler = fopen ('xml_chunks/offers/offers_chunk'.$this->counterOffers.'.xml',"a");
      fwrite ($fileHandler , '</Предложения>'.PHP_EOL );
      fclose($fileHandler);

  		 	$this->counterTMP = 0;
  		 	$this->firstElem = true;
  		 	$this->counterOffers++;
		}

		$fileHandler = fopen ('xml_chunks/offers/offers_chunk'.$this->counterOffers.'.xml',"a");
		if ($this->firstElem){
  			//$subject = $this->xml->asXML();
        $subject = str_replace ( '<?xml version="1.0" encoding="UTF-8"?>' , '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL.'<Предложения>' , $this->xml->asXML());
  			$this->firstElem = false;
		}else {
			   $subject = str_replace ( '<?xml version="1.0" encoding="UTF-8"?>' , '' , $this->xml->asXML());
		}

		fwrite ($fileHandler , $subject);
		fclose($fileHandler);
		$this->counterTMP++;
    return true;
    }



    protected function callbackProduct($reader){
		$this->xml = $reader->expandSimpleXml();

		if ($this->counterTMP == 500) {
			$fileHandler = fopen ('xml_chunks/product/product_chunk'.$this->counterProduct.'.xml',"a");
			fwrite ($fileHandler , '</Товары>'.PHP_EOL );
			fclose($fileHandler);
  		 	$this->counterTMP = 0;
  		 	$this->firstElem = true;
  		 	$this->counterProduct++;
		}

		$fileHandler = fopen ('xml_chunks/product/product_chunk'.$this->counterProduct.'.xml',"a");
		if ($this->firstElem){
  			//$subject = $this->xml->asXML();
        $subject = str_replace ( '<?xml version="1.0" encoding="UTF-8"?>' , '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL.'<Товары>' , $this->xml->asXML());
  			$this->firstElem = false;
		}else {
			   $subject = str_replace ( '<?xml version="1.0" encoding="UTF-8"?>' , '' , $this->xml->asXML());
		}

		fwrite ($fileHandler , $subject);
		fclose($fileHandler);
		$this->counterTMP++;
    return true;
    }



	protected function callbackTree($reader){
		$this->xml = $reader->expandSimpleXml();
		$this->xml->asXML('xml_chunks/tree/tree_chunk'.$this->counterTree.'.xml');
		$this->counterTree++;
        return true;
    }


	public function unlinkFiles($directory){
  		$fileList  = scandir ($directory);
  		foreach ($fileList AS $elem){
    			if ($elem == '.' OR $elem == '..') continue;
    			unlink($directory.$elem);
  		}
    }
}







$stateProcessing = new stateProcessing($modx,$pdo);


if ($stateProcessing->checkNewfile() === 2) {
    $stateProcessing->setFilePath();

    $stateProcessing->setBusy('import');


    $reader = new ExXMLreader;
  	$reader->unlinkFiles('xml_chunks/product/'); //сделать нормально
  	$reader->unlinkFiles('xml_chunks/tree/');
    $reader->unlinkFiles('xml_chunks/offers/');

  	$reader->open($stateProcessing->pathToImport);
  	$reader->parse();
    stateProcessing::setInfoToDB($reader->counterProduct , $stateProcessing->getFileTabID('import') , 'import');
    $stateProcessing->setCountChunks ('import' , $reader->counterProduct);

 //сделать это нормально
    $fileHandler = fopen ('xml_chunks/product/product_chunk'.$reader->counterProduct.'.xml',"a");
    fwrite ($fileHandler , '</Товары>'.PHP_EOL );
    fclose($fileHandler);

    $readerOffers = new ExXMLreader;
    $readerOffers->open($stateProcessing->pathToOffers);
    $readerOffers->parse();
    stateProcessing::setInfoToDB($readerOffers->counterOffers , $stateProcessing->getFileTabID('offers') , 'offers');
    $stateProcessing->setCountChunks ('offers' , $readerOffers->counterOffers);

    //сделать это нормально
    $fileHandler = fopen ('xml_chunks/offers/offers_chunk'.$readerOffers->counterOffers.'.xml',"a");
    fwrite ($fileHandler , '</Предложения>'.PHP_EOL );
    fclose($fileHandler);



    //импорт категорий - надо продумать -куда убрать
    try {
      $catalog = simplexml_load_file( 'xml_chunks/tree/tree_chunk0.xml' );
      if (!$catalog) {
        throw new Exception('Файл с категриями не найден!');
      }
      $rows= $catalog->{'Группа'};
      $modx->runSnippet( '_act_group', array( 'group' => $rows, 'parent' => 0, 'ids1c' => array(), 'lvl' => 0 ) );
    } catch (Exception $e) {
        die('Ошибка SimpleXML при импорте категорий: ' . $e->getMessage());
    }




    $catalog = simplexml_load_file( 'xml_chunks/tree/tree_chunk0.xml' );
    $rows= $catalog->{'Группа'};
    $ids1c = $modx->runSnippet( '_act_group', array( 'group' => $rows, 'parent' => 0, 'ids1c' => array(), 'lvl' => 0 ) );

    if ( is_array($ids1c) && count($ids1c) > 0 ){
      $handle = fopen("xml_chunks/treeIDS.obj", "w");
      fwrite ( $handle , serialize($ids1c) );
      fclose($handle);
    }

    $stateProcessing->clearBusy('import');



}elseif (($stateProcessing->getChunkState('import')) !== false){
    //есть ли недопарсенные куски

    $stateProcessing->setBusy('import');

    try {
      $ids1c = file_get_contents( 'xml_chunks/treeIDS.obj');
      if (!$ids1c) {
        throw new Exception('Файл treeIDS не найден!');
      }
      if (!$ids1c = unserialize($ids1c)) {
        throw new Exception('Файл treeIDS поврежден!');
      }

    } catch (Exception $e) {
        die('Ошибка ввода/вывода: ' . $e->getMessage());
    }

    $catalog = simplexml_load_file( 'xml_chunks/product/product_chunk'.($stateProcessing->getChunkState('import')).'.xml' );
    $rows= $catalog->{'Товар'};
    $res = $modx->runSnippet( '_act_items', array( 'items' => $rows, 'ids1c' => $ids1c, 'ids1c_items' => array() ) );

    if ($res == 'import_complete') {
      $stateProcessing->setNextChunk('import');
    }

    $stateProcessing->clearBusy('import');

}elseif ($stateProcessing->getChunkState('offers') !== false){
    $stateProcessing->setBusy('offers');
    $catalog = simplexml_load_file( 'xml_chunks/offers/offers_chunk'.($stateProcessing->getChunkState('offers')).'.xml' );
    $rows= $catalog->{'Предложение'};
    $res = $modx->runSnippet( '_act_price', array( 'prices' => $rows, 'ids1c_items' => '' ) );
    if ($res == 'offers_complete') {
      $stateProcessing->setNextChunk('offers');
    }
    $stateProcessing->clearBusy('offers');
}








echo '<hr>exec time: '.(microtime(true) - $benchmarkStart).' sec.';
 ?>
