<?php
defined('IN_MANAGER_MODE') or die();
date_default_timezone_set('Europe/Samara');
setlocale (LC_ALL, 'ru_RU.UTF-8');
$dbname = $modx->db->config['dbase'];
$dbprefix = $modx->db->config['table_prefix'];
$theme = $modx->config['manager_theme'];
$charset = $modx->config['modx_charset'];
$site_name = $modx->config['site_name'];
$manager_language = $modx->config['manager_language'];
$rb_base_url = $modx->config['rb_base_url'];
$mod_page = "index.php?a=112&id=".$_GET['id'];
if(isset($_GET['page'])){
        $mod_page.="&page=".$_GET['page'];
    }
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

$manID=$modx->getLoginUserID();    
if(!in_array($manID,array(1,5))) exit();

switch($action) {

//Module page
default:
    $sfrom=$_GET['monthStat'];
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

    $statuses=array(3,4,8,12,13,14,15,16,2,17);
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
        $begMonth=2;
        $begYear=2016;
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
                <option value="1.2016">Январь 2016</option>
                <option value="12.2015">Декабрь 2015</option>
                <option value="11.2015">Ноябрь 2015</option>
                <option value="10.2015">Октябрь 2015</option>
                </select></li>
            </ul>
        </div>
        
     </div>
    ';
	
   
    if($total>0){
         $count_val = $modx->db->select("COUNT(*) as count", $mod_tvtable, "tmplvarid=7", "id DESC", "");
         $count_val=mysql_result($count_val, 0);
         $sklad_val = $modx->db->select("contentid,value", $mod_tvtable, "tmplvarid=7 OR tmplvarid=1", "id DESC", "");
         $sklad_array=array();
		 $not_tovar=array(3942,3942,3094);
         while ($data_sklad = mysql_fetch_array($sklad_val)){
			 if(!in_array($data_sklad['contentid'],$not_tovar)){
				 $complex_item=$modx->getTemplateVar('94','*',$data_sklad['contentid']);
				 if(empty($complex_item['value'])){
					if(!isset($sklad_array[$data_sklad['contentid']])) $sklad_array[$data_sklad['contentid']]=1;
					$sklad_array[$data_sklad['contentid']]*=intval($data_sklad['value']);
				 }
			 }
         }
         $sum_val=0;
         foreach($sklad_array as $k => $v){
             $sum_val+=intval($v);
         }
        $data_query = $modx->db->select("id,content , price, delivery_price, currency, note, status,payment,short_txt,weight,is_zakazn", $shkm->mod_table, $searchstr, "id DESC", "");

    }
    include "templates/mainpage_stat.tpl.php";
	echo 1;

break;
} 

//show notify
unset($value);
if(count($notify)>0 || isset($_GET['notify'])){
  echo '<div id="notifyBlock">';
  echo '<h3>'.$langTxt['notify_title'].'</h3>';
  foreach($notify as $value){
    echo "<p>&bull; $value</p>";
  }
  unset($value);
  if(isset($_GET['notify']) && is_array($_GET['notify'])){
    foreach($_GET['notify'] as $value){
      if(isset($langTxt[$value]))
        echo "<p>&bull; ".$langTxt[$value]."</p>";
      else
        echo "<p>&bull; $value</p>";
    }
    unset($value);
  }
  echo '</div>';
}
echo "</div>\n</body>\n</html>";
if(!isset($_SESSION['mod_loaded']))
    $_SESSION['mod_loaded'] = strtotime("now");
if(strtotime("now")-$_SESSION['mod_loaded']>6*3600)
    unset($_SESSION['mod_loaded']);
?>