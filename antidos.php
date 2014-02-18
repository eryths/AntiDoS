<?php
/*  AntiDos - Главный файл
    
    Version 2014-02-18
*/

// Функция-переменная выполняющая всю работу
$antidos = function()
{
    /* Данные */
    
    // Путь до папки со скриптом (исправим слеши для Windows)
    $path_antiddos = str_replace("\\", "/", __DIR__);    
    
    // Настройки
    require($path_antiddos."/config.php");
    
    // Список популярных поисковых роботов (user agent => end host)
    $list_search_robots = array('Google' => array('.googlebot.com'),
        "Yandex" => array('.yandex.ru', '.yandex.net', '.yandex.com'),
        "Mail.RU_Bot" => array('.mail.ru'),
        "bingbot" => array('.search.msn.com'));    
    
    // метка AntiDos в .htaccess для блокируемых IP
    $mark_antidos = '#ANTIDOS#';



    // Путь до файла .htaccess
    if ($config["path-htaccess"] == "") $config["path-htaccess"] = ".htaccess";

    // Время запуска скрипта
    $time = time();
    
    // IP
    $ip = $_SERVER['REMOTE_ADDR'];

    // Время последнего обнуления статистики IP
    $lasttime_clear_ip = (int)file_get_contents($path_antiddos.'/lasttime-clear-ip.txt');
    
    
    
    /* Выполнять через заданный промежуток времени */
    if (($time - $lasttime_clear_ip) > $config["timeout-clear-ip"])
    {
        /* Удалить блокировку IP */
        $mark_antidos_length = strlen($mark_antidos);
        $htaccess_lines = file($config["path-htaccess"]);
        $new_htaccess = '';
        foreach ($htaccess_lines as $line)
        {
            // Исключим из заблокированных IP с истекшим сроком
            $mark_antidos_pos = strpos($line, $mark_antidos);
            if ($mark_antidos_pos === false) 
            {
                $new_htaccess .= $line;
            }
            else
            {
                $time_ban_ip = (int)trim(substr($line, $mark_antidos_pos + $mark_antidos_length));
                if (($time - $time_ban_ip) <= $config["timeout-clear-ip"]) $new_htaccess .= $line;
            }
        }
        
        
        
        /* Удалять файлы IP.txt со старым временем обновления */
        foreach (glob($path_antiddos.'/ip-stat/*.txt') as $filename) {
            $filemtime = filemtime($filename);
            // Если время истекло
            if (($time - $filemtime) > $config["timeout-clear-ip"])
            {
                // Удалим файл
                unlink($filename);
            }
        }
        
        
        
        /* Обновим время последней очистки */
        if (file_put_contents($config["path-htaccess"], $new_htaccess, LOCK_EX) !== false)
        {
            file_put_contents($path_antiddos.'/lasttime-clear-ip.txt', $time);
        }
        
    }



    /* Обработаем запрос поисковых роботов */

    // Является ли посетитель поисковым роботом
    $is_search_robot = false;
    foreach ($list_search_robots AS $user_agent => $end_hosts)
    {
        if (stripos($_SERVER["HTTP_USER_AGENT"], $user_agent)) 
        {
            // Путь до файла IP поискового робота
            $path_ip_search_robots_file = $path_antiddos.'/ip-search-robots/'.$ip.'.txt';
    
            // Значит IP поисковика не был добавлен ранее
            // if (strpos(file_get_contents($path_antiddos.'/ips-search-robots.txt'), $ip) === false)
            if (file_exists($path_ip_search_robots_file) == false)
            {
                // Проверим хост IP на правильность
                $host = gethostbyaddr($ip);

                foreach ($end_hosts AS $end_host)
                {
                    // Если хост правильный
                    if (substr($host, -(strlen($end_host))) == $end_host)
                    {
                        $is_search_robot = true;
                        //file_put_contents($path_antiddos.'/ips-search-robots.txt', $ip."\n", FILE_APPEND);
                        file_put_contents($path_ip_search_robots_file, $user_agent);
                        break;
                    }
                }
            }
            else
            {
                $is_search_robot = true;
            }

            break;
        }
    }

    
    
    /* Если посетитель не поисковый робот */
    if ($is_search_robot == false)
    {
        // Путь до файла статистики IP
        $path_ip_stat_file = $path_antiddos.'/ip-stat/'.$ip.'.txt';
        /* Ведение статистики запросов и блокировка IP */
        
        // Обновим статистику IP
        file_put_contents($path_ip_stat_file, "+", FILE_APPEND);

        // Если количество IP адресов больше максимально разрешенного
        if (filesize($path_ip_stat_file) >= $config["max-queries-by-ip"])
        {
            // Заблокировать IP
            $htaccess = 'deny from '.$ip." ".$mark_antidos.$time."\n";
            $htaccess .= file_get_contents($config["path-htaccess"]);
            file_put_contents($config["path-htaccess"], $htaccess, LOCK_EX);
            
            // Удалить файл статистики заблокированного IP
            unlink($path_ip_stat_file);
        }
    }
};
// Запуск скрипта
$antidos();

?>