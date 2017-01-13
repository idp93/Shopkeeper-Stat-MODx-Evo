<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"  lang="en" xml:lang="en">
<head>
  <link rel="stylesheet" type="text/css" href="media/style/<?php echo $theme; ?>/style.css" />
  <link rel="stylesheet" type="text/css" href="<?php echo SHOPKEEPER_PATH; ?>module/js/colorpicker/farbtastic.css" />
  <link rel="stylesheet" type="text/css" href="<?php echo SHOPKEEPER_PATH; ?>module/js/colorbox/colorbox.css" />
  <style type="text/css">
  .span4{width:30%;float:left;margin-left:3%;}
    .but-link {padding:2px 0 2px 20px; background-repeat:no-repeat; background-position:left top;}
    .order-table {border-collapse:collapse;}
    .order-table th, .order-table td {padding:2px 5px; border:1px solid #888;}
    .order-table th {background-color:#E4E4E4;}
    .order-table th select, .order-table th input {font-weight:normal;}
    .pages {padding:5px 0;}
    table input {margin:2px 0;}
    li {margin:10px 0 0 0;}
    th.header {background-image: url(<?php echo SHOPKEEPER_PATH; ?>style/default/img/sort.gif); cursor: pointer; font-weight: bold; background-repeat: no-repeat; background-position: center left; padding-left: 15px;}
    th.headerSortUp {background-image: url(<?php echo SHOPKEEPER_PATH; ?>style/default/img/asc.gif); background-color: #D0D0D0;}
    th.headerSortDown {background-image: url(<?php echo SHOPKEEPER_PATH; ?>style/default/img/desc.gif); background-color: #D0D0D0;}
    .colorwell {border: 2px solid #fff; width: 75px; text-align: center; cursor: pointer;}
    
a.button15 {
  display: inline-block;
  font-family: arial,sans-serif;
  font-size: 11px;
  font-weight: bold;
  color: rgb(68,68,68);
  text-decoration: none;
  user-select: none;
  padding: .2em 1.2em;
  outline: none;
  border: 1px solid rgba(0,0,0,.1);
  border-radius: 2px;
  background: rgb(245,245,245) linear-gradient(#f4f4f4, #f1f1f1);
  transition: all .218s ease 0s;
}
a.button15:hover {
  color: rgb(24,24,24);
  border: 1px solid rgb(198,198,198);
  background: #f7f7f7 linear-gradient(#f7f7f7, #f1f1f1);
  box-shadow: 0 1px 2px rgba(0,0,0,.1);
}
a.button15:active {
  color: rgb(51,51,51);
  border: 1px solid rgb(204,204,204);
  background: rgb(238,238,238) linear-gradient(rgb(238,238,238), rgb(224,224,224));
  box-shadow: 0 1px 2px rgba(0,0,0,.1) inset;
}
  </style>
 
  <script src="//code.jquery.com/jquery-1.10.2.js"></script>
  <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
  <script src="<?php echo SHOPKEEPER_PATH; ?>module/js/jquery.tablesorter.min.js" type="text/javascript"></script>
  <script src="<?php echo SHOPKEEPER_PATH; ?>module/js/colorpicker/farbtastic.js" type="text/javascript"></script>
  <script src="<?php echo SHOPKEEPER_PATH; ?>module/js/colorbox/jquery.colorbox-min.js" type="text/javascript"></script>
  <link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
  <script src="https://code.highcharts.com/highcharts.js"></script>
  <script type="text/javascript">
  var src_action="index.php?<?=str_replace('&amp;','&',trim($src_action));?>";
  var colorBoxOpt = {iframe:true, innerWidth:700, innerHeight:400, opacity:0.5};
  $.fn.tabs = function(){
    var parent = $(this);
    var tabNav = $('div.tab-row',this);
    var tabContent = $('div.tab-page',this);
    $('h2.tab',tabNav).each(function(i){
      $(this).click(function(){
        $('h2.tab',tabNav).removeClass('selected');
        $('h2.tab',tabNav).eq(i).addClass('selected');
        tabContent.hide();
        tabContent.eq(i).show();
        return false;
      });
    });
  }
  $(document).bind('ready',function(){
      //$("#ordersTable").tablesorter({sortList: [[1,1]], headers: {0:{sorter: false}, 9:{sorter: false}}});
      $("a.iframe").colorbox(colorBoxOpt);
      $("a.ajax").colorbox({innerWidth:700, innerHeight:400});
      $("#tabs").tabs();
      setTimeout(function(){
        $('#notifyBlock').slideUp(700);
      },5000);
    } 
  );
  var tree = false;

 $(document).ready(function(){
    $('.exportOpen').click(function(){
        $('#exportOpen').slideToggle("slow");
    });
     $('.searchOpen').click(function(){
        $('#searchOpen').slideToggle("slow");
    });
    $('#date').change(function(){
        var attr="&monthStat="+$(this).val();
        var href=$('#refresh').attr("href");
		console.log(attr);
        $('#refresh').attr("href",href+attr);
        $('#refresh').trigger('click');
        return true;
    });
 });
  </script>
   
  <script>
  $(function() {
    $( "#slider-range" ).slider({
      range: true,
      min: 0,
      max: 15000,
      values: [ <?=$sprfrom?>, <?=$sprto?> ],
      slide: function( event, ui ) {
        $( "#sprfrom" ).val(ui.values[ 0 ])
        $( "#sprto" ).val(ui.values[ 1 ]);
      }
    });
    $( "#sprfrom" ).val($( "#slider-range" ).slider( "values", 0 ))
    $( "#sprto" ).val($( "#slider-range" ).slider( "values", 1 ));
     $( "#slider-rangedel" ).slider({
      range: true,
      min: 0,
      max: 3000,
      values: [ <?=$sdelfrom;?>, <?=$sdelto;?> ],
      slide: function( event, ui ) {
        $( "#sdelfrom" ).val(ui.values[ 0 ])
        $( "#sdelto" ).val(ui.values[ 1 ]);
      }
    });
    $( "#sdelfrom" ).val($( "#slider-rangedel" ).slider( "values", 0 ))
    $( "#sdelto" ).val($( "#slider-rangedel" ).slider( "values", 1 ));
    $("#sstat option[value=<?=$sstat;?>]").attr("selected","selected");
  });
  </script>
</head>
<body>

<br />
<div class="sectionHeader">Интернет магазин Strunki.ru <?php //if($action=='catalog'){echo $langTxt['catalog_mod'];}else{echo $langTxt['modTitle'];} ?></div>

<div class="sectionBody" style="min-height:250px;">


