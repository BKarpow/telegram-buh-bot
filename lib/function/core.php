<?php



function dateFormat(string $date, string $format_input = '(Y)m.d H:i'):string
{
    $time = strtotime($date);
    // $time = (int)$time + 7200; //+02:00
    return date($format_input, $time);
}

/**
 * Повертає зформований рядок операцій для поточного дня
 * @param MySql $db
 * @return string
 */
function allOneDay(MySql $db)
{
    global $chat_id;
    $res = $db->getFromThisDay(" `chat_id` = '{$chat_id}'");
    $date = date('d.m.Y');
    $consumption = 0.0;
    $income = 0.0;
    $text = 'За сьогодні ' . $date . ':' . PHP_EOL;
    foreach ($res as $item) {
        $consumption += (float)$item['consumption'];
        $income += (float)$item['income'];
        $d = dateFormat($item['date'], 'H:i:s');
        $text .= "-{$item['consumption']}грн, +{$item['income']}грн: {$d}" . PHP_EOL;
    }
    $text .= "Рвзом: -{$consumption}грн, +{$income}грн, операцій: " . count($res) . '.';
    return $text;
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

function save_result(bool $income , $value, MySql $db, Telegram $telegram)
{
    global $chat_id, $username, $keyb;
    $arr = [
        'user_name' => $username,
        'chat_id' => $chat_id,
        'description' => "BETA TEST"
    ];
    // file_put_contents('.log', var_export($arr, 1), FILE_APPEND);

    if ($income){
        $arr['income'] = $value;
        $arr['consumption'] = 0.0;
    }else{
        $arr['income'] = 0.0;
        $arr['consumption'] = $value;
    }
    $db->insert($arr);
    $telegram->sendMessage(['reply_markup' => $keyb,'chat_id' => $chat_id, 'text' => report($db)]);
}


