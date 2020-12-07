<?php

/**
Простий бот для ведення обліку доходів, розходів.
@author Bogdan Karpow <xymerone@gmail.com>
@version 1.0(beta)
@url: https://github.com/BKarpow/telegram-buh-bot
*/

// error_reporting(0);
ini_set('date.timezone', 'Europe/Kiev');

// Load composer
require __DIR__ . '/vendor/autoload.php';

$bot_api_key  = file_get_contents(__DIR__ .'/.token'); // файл з токеном потрібно створити в корені







$telegram = new Telegram($bot_api_key);
$text = $telegram->Text();
$username = $telegram->Username();
$chat_id = $telegram->ChatID();



$option = array( 
    //First row
    array($telegram->buildKeyboardButton("За сьогодні"),
        $telegram->buildKeyboardButton("Всі записи" )),
    [$telegram->buildKeyboardButton('За день'), $telegram->buildKeyboardButton('Кнопка')]

     );
$keyb = $telegram->buildKeyBoard($option, $onetime=true);
//$content = array('chat_id' => $chat_id, 'reply_markup' => $keyb, 'text' => "This is a Keyboard Test");

//$content = array('reply_markup' => $keyb,'chat_id' => $chat_id, 'text' => $text);
//$telegram->sendMessage($content);
//
//die();

try{
	$db = new MySql('buhbase');

	$res = $db->where('chat_id', $chat_id);
	if (empty($res)){
		$content = array('chat_id' => $chat_id, 'text' => 'Ви новий користувач.');
		$telegram->sendMessage($content);
	}
	switch ($text) {
        case 'За день':
            $telegram->sendMessage(['reply_markup' => $keyb,'chat_id' => $chat_id, 'text' => allOneDay($db)]);
            break;
        case 'Всі записи':
            $telegram->sendMessage(['reply_markup' => $keyb, 'chat_id' => $chat_id, 'text' => allOperation($db, $chat_id)]);
            break;
        case 'За сьогодні':
            $telegram->sendMessage([
                'chat_id' => $chat_id,
                'reply_markup' => $keyb,
                'text' => report($db)
            ]);
            break;
		case '/start':
			$content = array('chat_id' => $chat_id, 'text' => help() );
			$telegram->sendMessage($content);
			break;
		case '/report':
			$telegram->sendMessage(['reply_markup' => $keyb, 'chat_id' => $chat_id, 'text' => report($db)]);
			break;
		case '/all':
			$telegram->sendMessage(['reply_markup' => $keyb, 'chat_id' => $chat_id, 'text' => allOperation($db, $chat_id)]);
			break;
		case '/all_day':
			$telegram->sendMessage(['reply_markup' => $keyb,'chat_id' => $chat_id, 'text' => allOneDay($db)]);
			break;
		default:
			if (preg_match('#^(\-|\+)([\d\.]+?)$#si', trim($text), $result)){
				if ($result[1] == '-'){
					save_result(false, (float) $result[2], $db, $telegram);
				}elseif($result[1] == '+'){
					save_result(true, (float) $result[2], $db, $telegram);
				}else{
					$telegram->sendMessage(['chat_id' => $chat_id, 'text' => help() ]);
				}
			}elseif(preg_match('#^([\d\.]+)$#si', trim($text), $result2) !== false){
				save_result(false, (float) $result2[1], $db, $telegram);
			}else{
				$telegram->sendMessage(['chat_id' => $chat_id, 'text' => help() ]);
			}
			break;
	}

}catch(DBEception $e){
	echo $e->message();
	die();
}
