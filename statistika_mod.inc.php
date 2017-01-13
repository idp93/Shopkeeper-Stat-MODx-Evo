<?php
defined('IN_MANAGER_MODE') or die();

setlocale (LC_ALL, 'ru_RU.UTF-8');
$dbname = $modx->db->config['dbase'];
$dbprefix = $modx->db->config['table_prefix'];
$theme = $modx->config['manager_theme'];
$charset = $modx->config['modx_charset'];
$site_name = $modx->config['site_name'];
$manager_language = $modx->config['manager_language'];
$rb_base_url = $modx->config['rb_base_url'];
$mod_page = "index.php?a=112&id=".$_GET['id'];

$cur_shk_version = '1.1';

define("SHOPKEEPER_PATH","../assets/snippets/shopkeeper/");
if(file_exists(SHOPKEEPER_PATH."module/lang/".$manager_language.".php"))
  $lang = $manager_language;
elseif(file_exists(SHOPKEEPER_PATH."module/lang/russian".$charset.".php"))
  $lang = "russian".$charset;
else
  $lang = "russian";

require_once SHOPKEEPER_PATH."classes/pagination.class.php";
require_once SHOPKEEPER_PATH."module/lang/".$lang.".php";
require_once SHOPKEEPER_PATH."classes/class.shopkeeper.php";
require_once SHOPKEEPER_PATH."classes/class.SHKmanager.php";

$shkm = new SHKmanager($modx);
$shkm->cur_version = $cur_shk_version;
$shkm->langTxt = $langTxt;
$shkm->dbname = $dbname;
$shkm->mod_page = $mod_page;
$shkm->mod_table = $dbprefix."manager_shopkeeper";
$mod_tvtable = $dbprefix."site_tmplvar_contentvalues";
$shkm->mod_config_table = $dbprefix."manager_shopkeeper_config";
$shkm->mod_user_table = $dbprefix."web_user_additdata";
$shkm->mod_catalog_table = $dbprefix."catalog";
$shkm->mod_catalog_tv_table = $dbprefix."catalog_tmplvar_contentvalues";
$shkm->tab_eventnames = $dbprefix."system_eventnames";
$shkm->excepDigitGroup = true;
$tmp_config = $shkm->getModConfig();
extract($tmp_config);
$installed = isset($conf_shk_version) ? 1 : 0;
$notify = array();


$action = !empty($_GET['action']) ? $_GET['action'] : '';
$action = !empty($_POST['action']) ? $_POST['action'] : $action;


switch($action) {

//Module page
default:
    $sfrom=$_GET['monthStat'];//Месяц за который учитывается статистика
	if(!isset($sfrom)){
		$sfrom=date("m.Y",time());
	}
	$sfrom=explode('.',$sfrom);

    $hours = 0;
    $minutes = 0;
    $seconds = 0;
    $month = intval($sfrom[0]);
    $day = 1;
    $year = intval($sfrom[1]);
    $yearto=$year;
    $monthto=$month;
    if($month==12){ $monthto=0;
    $yearto++;
    }
    // используйте mktime для обновления UNIX времени
    // добавление 19 часов к $hours
    $timeBeg = mktime($hours,$minutes,$seconds,$month,$day,$year);
    $timeEnd = mktime($hours,$minutes,$seconds,$monthto+1,$day,$yearto);
    $from=date("Y.m.d 00:00:00",$timeBeg);
    $to=date("Y.m.d 00:00:00",$timeEnd);

    $statuses=array(3,4,8,12,13,14,15,16,2,17);//Статусы, которые используются для статистики
	$searchstr=" status in(".implode(',',$statuses).")";  
     $searchstr.=' AND date>="'.$from.'"';
     $searchstr.=' AND date<"'.$to.'"';

  include "templates/header_stat.tpl.php";
 
    if($searchstr!=''){
        $fcount=' WHERE '.$searchstr;
    }
    $count_query = mysql_query("SELECT COUNT(*) FROM $shkm->mod_table".$fcount);
    $total = mysql_result($count_query, 0);
    
    //top buttons
    echo '
      <div style="width:100%; height:30px;">
        <div style="width:200px;float:left;">
            <ul class="actionButtons">
                <li><a id="refresh" href="'.$mod_page.'"><img src="'.SHOPKEEPER_PATH.'style/default/img/refresh.png" alt="">&nbsp; '.$langTxt['refresh'].'</a></li>
                <li><select id="date">
                <option value="">Выберите месяц и год</option>';
        $curYear=date("Y");
        $curMonth=date("m");
        
        $month=array("Январь","Февраль","Март","Апрель","Май","Июнь","Июль","Август","Сентябрь","Октябрь","Ноябрь","Декабрь");
        $monthArray=array();
        $begMonth=2;//месяц начала работы модуля статистики
        $begYear=2016;//год начала работы модуля статистики
        while($begYear<$curYear){
            while($begMonth<=12){
                $monthArray[]=$begMonth.'.'.$begYear;
                $begMonth++;
            }
            $begMonth=1;
            $begYear++;
        }
		while($begMonth<=$curMonth){
                $monthArray[]=$begMonth.'.'.$begYear;
                $begMonth++;
        }
        $monthArray=array_reverse($monthArray);
        foreach($monthArray as $monthYear){
             $mY=explode('.',$monthYear);
             echo '<option value="'.$monthYear.'">'.$month[$mY[0]-1].' '.$mY[1].'</option>';
        }
        echo '
                </select></li>
            </ul>
        </div>
        
     </div>
    ';
	
   
    if($total>0){
		 /***
		 Рассчёт остатков товара на складе и оценочная суммарная стоимость товаров
		 ***/
         $count_val = $modx->db->select("COUNT(*) as count", $mod_tvtable, "tmplvarid=7", "id DESC", "");
         $count_val=mysql_result($count_val, 0);
         $sklad_val = $modx->db->select("contentid,value", $mod_tvtable, "tmplvarid=7 OR tmplvarid=1", "id DESC", "");
         $sklad_array=array();
		 $not_tovar=array(3942,3942,3094);//Не продовольственные товары - услуги
         while ($data_sklad = mysql_fetch_array($sklad_val)){
			 if(!in_array($data_sklad['contentid'],$not_tovar)){
					if(!isset($sklad_array[$data_sklad['contentid']])) $sklad_array[$data_sklad['contentid']]=1;
					$sklad_array[$data_sklad['contentid']]*=intval($data_sklad['value']);
			 }
         }
         $sum_val=0;
         foreach($sklad_array as $k => $v){
             $sum_val+=intval($v);
         }
		//Запрос по данным заказа
        $data_query = $modx->db->select("*", $shkm->mod_table, $searchstr, "id DESC", "");

    }
    include "templates/mainpage_stat.tpl.php";
	echo 1;

break;
} 

echo "</div>\n</body>\n</html>";
if(!isset($_SESSION['mod_loaded']))
    $_SESSION['mod_loaded'] = strtotime("now");
if(strtotime("now")-$_SESSION['mod_loaded']>6*3600)
    unset($_SESSION['mod_loaded']);
?>