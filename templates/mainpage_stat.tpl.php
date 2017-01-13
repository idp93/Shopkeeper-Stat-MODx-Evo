
<?php if($total>0 && isset($data_query)): ?>


<?php

mb_internal_encoding("UTF-8");
    $num = 0;
    $status_price=array();
    $post_price=array();
    $del_types=array();
    $pay_types=array();
    $notend_info=array();
    $status_count=array();
	$types_count=array();
	$types_weight=array();
	$gitarmaster=array("cost"=>0);
	$gitarmaster["zak"]=array();
while ($data = mysql_fetch_array($data_query)){
    $stat=intval($data['status']);
    $type=intval($data['is_zakazn']);
    $weight=floatval($data['weight']);
    $payment=$data['payment'];
	$dataInf = unserialize($data["content"]);
    foreach($dataInf as $i => $dataArray){
            list($id, $count, $price, $name) = $dataArray;
			if($id==5202){
				$gitarmaster["cost"]+=$count*$price;
				if(!isset($gitarmaster["zak"][$data["id"]]))
					$gitarmaster["zak"][$data["id"]]=$data["id"];
			}
	}
	//Статистика типов посылок
	if($stat==12) $type=12;
	if(!empty($type)){
		if(!isset($types_count[$type])){$types_count[$type]=0;$types_weight[$type]=0;}
		$types_count[$type]++;$types_weight[$type]+=$weight;
	}//Статистика заказов
	if($stat!=17){
		if(!isset($status_price[$stat])) $status_price[$stat]=0;
		$status_price[$stat]+=$data['price'];
    
		if(!isset($status_count[$stat])) $status_count[$stat]=0;
		$status_count[$stat]++;
    }else{
		if(!isset($status_back)) $status_back=0;
		$status_back+=$data['delivery_price']*1.8;
		
		if(!isset($status_back_count)) $status_back_count=0;
		$status_back_count++;
		
	}
    //Статистика оплаты
    if(!isset($pay_types[$payment])) $pay_types[$payment]=0;
    if(!in_array($stat,array(4,13,16,15)))
        $pay_types[$payment]++;
        
    //Статистика доставки    
    $fulInfo=explode('<br />',$data["short_txt"]);
    $delivery=explode(':',$fulInfo[4]);
	$delivery=str_replace(array('"','&nbsp;'),'',htmlentities(str_replace('</i>','',$delivery[1])));
    if(!isset($del_types[$delivery])) $del_types[$delivery]=0;
    if(!in_array($stat,array(4,13,16,15)))
        $del_types[$delivery]++;
    
    if(in_array($stat,array(2,8,16))){
        $notend_info[]=array("id"=>$data['id'],"price"=>$data['price'],"status"=>$stat);
    }   
    if(in_array($stat,array(3,14))){
        if(!isset($post_price[$stat])) $post_price[$stat]=0;
        $post_price[$stat]+=$data['delivery_price'];
    }
    
    $num++; 
}
$total_cash_count=0;
$total_cash=0;
$total_nocash_count=0;
$total_nocash=0;
$total_nocash_info="";
echo '<br clear=all/>
<div class="span4">
<h2>Статистика за '.date("m.Y",$timeBeg).':</h2>
<table id="ordersTable" class="order-table" width="100%">
<thead>
  <tr>
    <th>Статус</th>
    <th>Сумма</th>
    <th>Кол-во</th>
</tr>
</thead>
';
foreach($status_price as $k => $v){
    switch($k){
     case 8: $phColor=6; break;
       
     case 12: $phColor=7; break;
      case 13: $phColor=8; break;
      case 14: $phColor=9; break;
      case 15: $phColor=10; break;
      case 16: $phColor=11; break;
      case 17: $phColor=12; break;
        default:
       $phColor=$k-1;
        };
    if(in_array($k,array(2,8,16))){
        $total_nocash+=$v;
        $total_nocash_count+=$status_count[$k];
        $total_nocash_info.='<tr alt="'.$k.'" style="background-color:'.$phaseColor[$phColor].';"><td>'.$langTxt['phase'.$k].'</td><td align=center>'.$v.' руб.</td><td>'.$status_count[$k].'</td></tr>';
    }else{
        $total_cash+=$v;
        $total_cash_count+=$status_count[$k];
        echo '<tr alt="'.$k.'" style="background-color:'.$phaseColor[$phColor].';"><td>'.$langTxt['phase'.$k].'</td><td align=center>'.$v.' руб.</td><td>'.$status_count[$k].'</td></tr>';
    }
}
$in_shop_count=0;
$in_shop=0;

$in_internet_count=0;
$in_internet=0;
foreach($status_count as $k => $v)
    if(in_array($k,array(4,13,15))){
        $in_shop_count+=$v;
        $in_shop+=$status_price[$k];
    } else if(in_array($k,array(3,12,14))){
        $in_internet_count+=$v;
        $in_internet+=$status_price[$k];
    }
echo '<tr style="border-top:3px solid #000;"><td>Продажи в магазине</td><td align=center><b>'.$in_shop.' руб.</b></td><td>'.$in_shop_count.'</td></tr>';
echo '<tr><td>Продажи в интернете</td><td align=center><b>'.$in_internet.' руб.</b></td><td>'.$in_internet_count.'</td></tr>';
echo '<tr><td>ИТОГО</td><td align=center><b>'.$total_cash.' руб.</b></td><td>'.$total_cash_count.'</td></tr></table>';


echo '
<br/>
<h2>Услуги гитарного мастера:</h2>
<table id="ordersTable" class="order-table" width="100%">
<tr>
    <td>Заказы</td>
    <td><b>'.implode(',',$gitarmaster["zak"]).'</b></td>
</tr>
<tr>
    <td>Cумма</td>
    <td><b>'.number_format($gitarmaster["cost"], 0, ',', ' ').' руб.</b></td>
</tr>
</table>';
echo '
<br/>
<h2>Возвраты:</h2>
<table id="ordersTable" class="order-table" width="100%">
<tr>
    <td>Количество</td>
    <td><b>'.number_format($status_back_count, 0, ',', ' ').'</b></td>
</tr>
<tr>
    <td>Cумма</td>
    <td><b>'.number_format($status_back, 0, ',', ' ').' руб.</b></td>
</tr>
</table>';
echo '
<br/>
<h2>На складе:</h2>
<table id="ordersTable" class="order-table" width="100%">
<tr>
    <td>Количество товаров(id) на складе</td>
    <td><b>'.number_format($count_val, 0, ',', ' ').'</b></td>
</tr>
<tr>
    <td>Cумма товаров на складе</td>
    <td><b>'.number_format($sum_val, 0, ',', ' ').' руб.</b></td>
</tr>
</table>
</div>';

?>


<?
echo '
<div class="span4">
<div id="del_container" style="min-width: 100%; height: 500px; max-width: 100%; margin: 0 auto"></div>
';
$total_count=0;
foreach($del_types as $k => $v){
    $total_count+=$v;
}
arsort($del_types);
echo 'Всего: <b>'.$total_count.'</b>';
echo '
</div>';
$delivery_info='';
foreach($del_types as $k => $v){
    $delivery_info.=',{
                    name: "'.$k.'",
                    y: '.number_format($v/$total_count*100,2).',
                    size:'.$v.'
                }';
}
$delivery_info=substr($delivery_info,1);
?>
<script type="text/javascript">
$(document).ready(function(){
    $('#del_container').highcharts({
            chart: {
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false,
                type: 'pie'
            },
            title: {
                text: 'Диаграмма доставки'
            },
            tooltip: {
                pointFormat: '{series.name}: <b>{point.size}({point.percentage:.1f}%)</b>'
            },
            legend:{
                labelFormat: '{name} <br/>{size}({y:.1f}%)'
            },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: false
                    },
                    showInLegend: true
                }
            },
            series: [{
                name: 'Заказы',
                colorByPoint: true,
                data: [<?=$delivery_info;?>]
            }]
        });
 });
  </script>

<?
echo '
<div class="span4">
<div id="pay_container" style="min-width: 100%; height: 500px; max-width: 100%; margin: 0 auto"></div>
';
$total_count=0;
foreach($pay_types as $k => $v){
    $total_count+=$v;
}

arsort($pay_types);
echo 'Всего: <b>'.$total_count.'</b>';
echo '
</div>';
$payment_info='';
foreach($pay_types as $k => $v){
    $payment_info.=',{
                    name: "'.$k.'",
                    y: '.number_format($v/$total_count*100,2).',
                    size:'.$v.'
                }';
}
$payment_info=substr($payment_info,1);
?>
<script type="text/javascript">
$(document).ready(function(){
    $('#pay_container').highcharts({
            chart: {
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false,
                type: 'pie'
            },
            title: {
                text: 'Диаграмма оплаты'
            },
            tooltip: {
                pointFormat: '{series.name}: <b>{point.size}({point.percentage:.1f}%)</b>'
            },
            legend:{
                labelFormat: '{name} <br/>{size}({y:.1f}%)'
            },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: false
                    },
                    showInLegend: true
                }
            },
            series: [{
                name: 'Заказы',
                colorByPoint: true,
                data: [<?=$payment_info;?>]
            }]
        });
 });
  </script>


<?
//Почтовые расходы
echo '
<div class="span4">
<h2>Почтовые расходы</h2>
<table id="ordersTable" class="order-table" width="100%">
<thead>
  <tr>
    <th>Статус</th>
    <th>Стоимость доставки</th>
</tr>
</thead>
';
$total_post=0;
foreach($post_price as $k => $v){
    $total_post+=$v;
     switch($k){
     case 8: $phColor=6; break;
       
     case 12: $phColor=7; break;
      case 13: $phColor=8; break;
      case 14: $phColor=9; break;
      case 15: $phColor=10; break;
      case 16: $phColor=11; break;
        default:
       $phColor=$k-1;
        };
        echo '<tr style="background-color:'.$phaseColor[$phColor].';"><td>'.$langTxt['phase'.$k].'</td><td align=center>'.$v.' руб.</td></tr>';
}
echo '<tr><td>ИТОГО</td><td align=center><b>'.$total_post.' руб.</b></td></tr>';
echo '</table>
<br/>
<h2>Типы почтовых отправлений</h2>
<table id="ordersTable" class="order-table" width="100%">
<thead>
  <tr>
    <th>Статус</th>
    <th>Количество</th>
    <th>Средний Вес(г.)</th>
</tr>
</thead>
';
$total_type_count=0;
$total_weight=0;
ksort($types_count);
$types_info=array(-1=>"Не указан",1=>"Ценная",2=>"Заказная",3=>"Заказная бандероль 1-го класса",4=>"Ценная посылка",5=>"Бандероль 1-го класса с оплатой при получении",12=>"СДЭК");
foreach($types_count as $k => $v){
    $total_type_count+=$v;
    $total_weight+=$types_weight[$k];
    echo '<tr><td>'.$types_info[$k].'</td><td align=center>'.$v.'</td><td align=center>'.round($types_weight[$k]/$v,2).'</td></tr>';
}
echo '<tr><td>ИТОГО</td><td align=center><b>'.$total_type_count.'</b></td><td align=center>-</td></tr>';
echo '</table>
</div>';
?>


<?
echo '
<div class="span4">
<h2>Не выполненные заказы:</h2>
<table id="ordersTable" class="order-table" width="80%">
<thead>
  <tr>
    <th>Статус</th>
    <th>Сумма</th>
    <th>Кол-во</th>
</tr>
</thead>
';
echo $total_nocash_info;
echo '<tr><td>ИТОГО</td><td align=center><b>'.$total_nocash.' руб.</b></td><td>'.$total_nocash_count.'</td></tr>';
echo '</table>
</div>';
echo '
<div class="span4">
<h2>Подробнее</h2><table id="ordersTable" class="order-table" width="100%">
<thead>
  <tr>
    <th>ID</th>
    <th>Статус</th>
    <th>Сумма</th>
</tr>
</thead>
';
foreach($notend_info as $v){
    echo '<tr><td>'.$v["id"].'</td><td>'.$langTxt['phase'.$v["status"]].'</td><td align=center>'.$v['price'].' руб.</td></tr>';
}
echo '</table>
</div>
<br clear=all />';
?>
<?php else: ?>

<div style="clear:both; text-align:center; line-height:70px;"><i><?php echo $langTxt['noOrders']; ?></i></div>

<?php endif;?>

<br />

<!--<div align="right">
    <ul class="actionButtons">
        <li><a href="#" onclick="postForm('csv_export',null,null);return false;"><img src="<?php echo SHOPKEEPER_PATH; ?>style/default/img/layout_go.png" alt="">&nbsp; <?php echo $langTxt['csv_export']; ?></a></li>
    </ul>
</div>-->

<br />