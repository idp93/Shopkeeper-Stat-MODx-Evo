
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
	while ($data = mysql_fetch_array($data_query)){
		$stat=intval($data['status']);
		$weight=floatval($data['weight']);
		$payment=$data['payment'];
		$dataInf = unserialize($data["content"]);
		foreach($dataInf as $i => $dataArray){
				list($id, $count, $price, $name) = $dataArray;
		}
		
			if(!isset($status_price[$stat])) $status_price[$stat]=0;
			$status_price[$stat]+=$data['price'];
		
			if(!isset($status_count[$stat])) $status_count[$stat]=0;
			$status_count[$stat]++;
		
		//Статистика оплаты
		if(!isset($pay_types[$payment])) $pay_types[$payment]=0;
		$pay_types[$payment]++;
			
		//Статистика доставки    
		$fulInfo=explode('<br />',$data["short_txt"]);
		$delivery=explode(':',$fulInfo[4]);
		$delivery=str_replace(array('"','&nbsp;'),'',htmlentities(str_replace('</i>','',$delivery[1])));
		if(!isset($del_types[$delivery])) $del_types[$delivery]=0;
		$del_types[$delivery]++;
		
		if(in_array($stat,$notend_statuses)){
			$notend_info[]=array("id"=>$data['id'],"price"=>$data['price'],"status"=>$stat);
		}   
		if(in_array($stat,$del_statuses)){
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
    //Цветовая схема по статусам, можно использовать свою, зашита в настройкаъ Shopkeeper
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
    if(in_array($k,$notend_statuses)){
        $total_nocash+=$v;
        $total_nocash_count+=$status_count[$k];
        $total_nocash_info.='<tr alt="'.$k.'" style="background-color:'.$phaseColor[$phColor].';"><td>'.$langTxt['phase'.$k].'</td><td align=center>'.$v.' руб.</td><td>'.$status_count[$k].'</td></tr>';
    }else{
        $total_cash+=$v;
        $total_cash_count+=$status_count[$k];
        echo '<tr alt="'.$k.'" style="background-color:'.$phaseColor[$phColor].';"><td>'.$langTxt['phase'.$k].'</td><td align=center>'.$v.' руб.</td><td>'.$status_count[$k].'</td></tr>';
    }
}
echo '<tr><td>ИТОГО</td><td align=center><b>'.$total_cash.' руб.</b></td><td>'.$total_cash_count.'</td></tr></table>';

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
echo '</table>';
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

<br />