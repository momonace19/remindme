<?php

define('BOT_NAME', 'remindMe');
/*define('BOT_ID', '510627366792593409');
define('BOT_ID_STAGING', '512091749972443147');
define('BOT_ID_LOCAL', '511746938186760192');*/

// date_default_timezone_set('America/Los_Angeles');
date_default_timezone_set('America/New_York');

include __DIR__.'/vendor/autoload.php';

use Discord\Discord;
use App\controllers\Reminder;

$discord = new Discord([
    'token' => getenv('token')
]);

$discord->on('ready', function ($discord) {
    echo "Bot is ready.", PHP_EOL;

    // Listen for events here
    $discord->on('message', function ($message) {   

    	$from = ($message->author === NULL)? 'GeneralWebhook':$message->author->username;

    	echo "Recieved a message from {$from}: {$message->content}", PHP_EOL;

        $bot_ids = ['510627366792593409', '512091749972443147', '511746938186760192'];

    	if($message->author !== NULL && !in_array($message->author->id, $bot_ids)) {

    		$content = $message->content;

        	if($content[0] === '!') {

        		$content = substr($content, 1);

        		$method =  strtok($content, " ");

                $reminder = new Reminder($message);

        		if( method_exists($reminder, $method) ) {

        			$message->reply($reminder->$method().PHP_EOL);
        			
        		} else {

        			return;
        		}
	        }	
        }        
    });
});

$discord->run();