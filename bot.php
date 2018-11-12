<?php

define('BOT_NAME', 'remindMe');
define('BOT_ID', '510627366792593409');

date_default_timezone_set('America/New_York');

include __DIR__.'/vendor/autoload.php';

use Discord\Discord;
use App\controllers\Reminder;

$discord = new Discord([
    'token' => getenv('token'),
]);

$discord->on('ready', function ($discord) {
    echo "Bot is ready.", PHP_EOL;

    // Listen for events here
    $discord->on('message', function ($message) {    

    	$from = ($message->author === NULL)? 'GeneralWebhook':$message->author->username;

    	echo "Recieved a message from {$from}: {$message->content}", PHP_EOL;

    	if($message->author !== NULL && $message->author->id !== BOT_ID) {

    		$content = $message->content;

        	if($content[0] === '!') {
        		//!setreminder <message> <time> <repeats everyday>
        		$reminder = new Reminder();

        		$content = substr($content, 1);

        		$method =  strtok($content, " ");

        		if( method_exists($reminder, $method) ) {

        			$message->reply($reminder->$method($content).PHP_EOL);
        			
        		} else {

        			return;
        		}
	        }	
        }        
    });
});

$discord->run();
