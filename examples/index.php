<?php 
/*  AntiDos - Примеры

    Version 2014-02-18
*/
?>
<!DOCTYPE html>
<html>
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <title>Пример. Защита PHP-сайта от DoS-атак путем блокировки IP-адресов в файле .htaccess</title>
  </head>
  <body>
    <h3>Пример. Защита PHP-сайта от DoS-атак путем блокировки IP-адресов в файле .htaccess</h3>
  
    <?php require("../../antidos/config.php"); // Узнаем параметры конфигурации ?>
    
    <b>Установлены следующие параметры в файле config.php:</b><br>
    Максимальное количество запросов с одного IP до блокировки: <b><?php echo $config["max-queries-by-ip"];?></b><br>
    Минимальное время блокировки IP: <b><?php echo $config["timeout-clear-ip"];?></b> сек<br>
    
    <?php
    // Получим статистику работы AntiDos
    $ip = $_SERVER['REMOTE_ADDR'];
    $counter = @filesize('../ip-stat/'.$ip.'.txt') + 1;
    ?>
    <br>Количество запросов с вашего IP адреса: <b><?php echo $counter;?></b>
    (до блокировки осталось <b><?php echo ($config["max-queries-by-ip"] - $counter);?></b>)<br>
    
    Когда IP будет заблокирован, снять блокировку сможет запрос к сайту с другого IP через указанное время 
    (<?php echo $config["timeout-clear-ip"];?> сек)<br>
    

<?php
// Определение времени выполения кода
function get_time ($start_time=0, $precision=2)
{
	$cur_time = explode(" ", microtime());	
	$cur_time = $cur_time[0] + $cur_time[1];	
	if ($start_time == 0)
	{
		return ($cur_time);
	}
	else
	{
		return round(($cur_time - $start_time),$precision);
	}
}
$start_time = get_time();


// Add AntiDos code
require("../../antidos/antidos.php");


echo '<br>Время исполнения кода AntiDoS: <b>'.get_time($start_time, 3).'</b> сек <br>';

?>

<br><br><hr>Адрес проекта на GitHub: <em>https://github.com/eryths/AntiDoS</em>

  </body>
</html>