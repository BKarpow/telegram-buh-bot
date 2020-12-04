<?php

/**
Простий бот для ведення обліку доходів, розходів.
@author Bogdan Karpow <xymerone@gmail.com>
@version 1.0(beta)
@url: https://github.com/BKarpow/telegram-buh-bot
*/

error_reporting(0);
ini_set('date.timezone', 'Europe/Kiev');

// Load composer
require __DIR__ . '/vendor/autoload.php';

$bot_api_key  = '1445268707:AAH-swERjoYIYReMRDjc-WbOJv-ZHQPbITQ';
$bot_username = 'buhbogdanbot';
$hook_url     = 'https://shli.pp.ua/bots/buhbot/index.php';



function dateFormat(string $date, string $format_input = '(Y)m.d H:i'):string
{
	$time = strtotime($date);
	$time = (int)$time + 7200; //+02:00
	return date($format_input, $time);
}


function allOperation(MySql $db, $chat_id):string 
{
	$res = $db->select('`consumption`, `income`, `date`')
	->where('chat_id', $chat_id);
	$consumption = 0.0;
	$income = 0.0;
	$text = 'Всі Ваші операції:'.PHP_EOL;
	foreach ($res as $item) {
		$consumption += (float) $item['consumption'];
		$income += (float) $item['income'];
		$date = dateFormat($item['date']);
		$text .= "-{$item['consumption']}, +{$item['income']}: {$date}".PHP_EOL;
	}
	$text .= "Рвзом: -{$consumption}, +{$income}, операцій: ".count($res).'.';
	return $text;
}



function report(MySql $db):string
{
	global $chat_id;
	$res = $db->getFromThisDay(" `chat_id` = '{$chat_id}'");
	$date = date('d.m.Y');
	$consumption = 0.0;
	$income = 0.0;
	foreach ($res as $item) {
		$consumption += (float) $item['consumption'];
		$income += (float) $item['income'];
	}
	return "Сьогодні {$date} ви витратили: {$consumption} грн, а дохід: {$income} грн. Пам'ятайие гроші люблять обережне відношення!";
}


function help():string{
	$t = "/start - Початок роботи".PHP_EOL;
	$t .= "/report - показати суму ща сьогодні".PHP_EOL;
	$t .= "/all - показати всі операції".PHP_EOL;
	$t .= "/all_day - всі операції за сьогодні".PHP_EOL;
	$t .= "Команда не коректна, пишіть +<cума>, або -<cума>.\n"
		."Автор: Богдан Карпов, @BogdanKarpov .";
		return $t;
}




$telegram = new Telegram($bot_api_key);
$text = $telegram->Text();
$username = $telegram->Username();
$chat_id = $telegram->ChatID();

try{
	$db = new MySql('buhbase');
	// die(report($db));
	$res = $db->where('chat_id', $chat_id);
	if (empty($res)){
		$content = array('chat_id' => $chat_id, 'text' => 'Ви новий користувач.');
		$telegram->sendMessage($content);
	}
	switch ($text) {
		case '/start':
			$content = array('chat_id' => $chat_id, 'text' => help() );
			$telegram->sendMessage($content);
			break;
		case '/report':
			$telegram->sendMessage(['chat_id' => $chat_id, 'text' => report($db)]);
			break;
		case '/all':
			$telegram->sendMessage(['chat_id' => $chat_id, 'text' => allOperation($db, $chat_id)]);
			break;
		case '/all_day':
			$res = $db->getFromThisDay(" `chat_id` = '{$chat_id}'");
			$date = date('d.m.Y');
			$consumption = 0.0;
			$income = 0.0;
			$text = 'За сьогодні '.$date.':'.PHP_EOL;
			foreach ($res as $item) {
				$consumption += (float) $item['consumption'];
				$income += (float) $item['income'];
				$d = dateFormat($item['date'], 'H:i:s');
				$text .= "-{$item['consumption']}грн, +{$item['income']}грн: {$d}".PHP_EOL;
			}
			$text .= "Рвзом: -{$consumption}грн, +{$income}грн, операцій: ".count($res).'.';
			$telegram->sendMessage(['chat_id' => $chat_id, 'text' => $text]);
			break;
		default:
			if (preg_match('#^(\-|\+)([\d\.]+?)$#si', trim($text), $result)){
				if ($result[1] == '-'){
					$db->insert([
						'user_name' => $username,
						'chat_id' => $chat_id,
						'description' => "{$chat_id}: {$username}",
						'income' => 0,
						'consumption' => (float) $result[2]
					]);
					$telegram->sendMessage(['chat_id' => $chat_id, 'text' => report($db)]);
				}elseif($result[1] == '+'){
					$db->insert([
						'user_name' => $username,
						'chat_id' => $chat_id,
						'description' => "{$chat_id}: {$username}",
						'income' => (float) $result[2],
						'consumption' => 0
					]);
					$telegram->sendMessage(['chat_id' => $chat_id, 'text' => report($db)]);
				}else{
					$telegram->sendMessage(['chat_id' => $chat_id, 'text' => help() ]);
				}
			}else{
				$telegram->sendMessage(['chat_id' => $chat_id, 'text' => help() ]);
			}
			break;
	}

}catch(DBEception $e){
	echo $e->message();
	die();
}
