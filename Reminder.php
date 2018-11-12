<?php 

namespace App\controllers;

// include '../../vendor/autoload.php';

use App\models\Mreminder;
use App\controllers\Timer;

class Reminder {

	private $mreminder;

	public function __construct() {

		// $this->mreminder = new Mreminder;
	}

	public function setreminder($message) {

		$respond = 'KO';

		$reminderKeys = ['message', 'time', 'repeat'];

		$reminderInfo = explode('<', $message);

		unset($reminderInfo[0]);

		if(count($reminderInfo) === 3) {

			// remove > and change key's value
			for ($i=1; $i < 4; $i++) { 
				$reminderInfo[$reminderKeys[$i-1]] = substr(trim($reminderInfo[$i]), 0, -1);
				unset($reminderInfo[$i]);
			}

			Mreminder::store($reminderInfo);

			$respond = 'Reminder Saved';
		}

		return $respond;
	}

	public function showreminder() {

		$ctr = 1;

		$allReminders = Mreminder::show();

		if(is_array($allReminders)) {

			foreach ($allReminders as $keys => $reminder) {
			
				$respond .= PHP_EOL."[{$ctr}] <{$reminder['message']}> <{$reminder['time']}> <{$reminder['repeat']}>";
				$ctr++;
			}
		}

		return isset($respond) ? $respond : 'No reminders';
	}

	public function postToDiscord($message) {

		$data = array("content" => $message, "username" => "remindMe");
	    $curl = curl_init("https://discordapp.com/api/webhooks/510700271710502912/NbrXlcEEeaBkxPokWGJD9-s_mFx8IalgqJCdWWtTPwm_pJ_6vaukwLrZAwoPVNgF67-7");
	    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
	    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
	    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	    return curl_exec($curl);
	}
}

/*$timer = new Timer();

$qwe = new Reminder();

print_r($qwe->setreminder($_POST['param']));
print_r($qwe->showreminders());
print_r($timer->checkTime());*/
