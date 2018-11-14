<?php 

namespace App\controllers;

include dirname( __FILE__, 3 ).'/vendor/autoload.php';

// date_default_timezone_set('America/Los_Angeles');
date_default_timezone_set('America/New_York');

use App\controllers\Reminder;
use App\models\Mreminder;
use App\models\Mwebhook;

class TimerCron {

	private $reminder;
	private $mreminder;
	private $mwebhook;

	public function __construct() {

		$this->reminder = new Reminder;

		$this->mreminder = new Mreminder;

		$this->mwebhook = new Mwebhook;
	}

	public function checkTime() {

		$currTime = date("H:i");

		$currDay = strtolower(date("l"));

		$reminderInfo = $this->mreminder->showByTime($currTime);

		if(!empty($reminderInfo)) {

			foreach ($reminderInfo as $key => $value) {

				$approved = $this->checkRepeat($value['repeats'], $currDay);

				if($approved) {

					$webhook_token = $this->mwebhook->getWebHookTokenById($value['webhook_id']);

					$url = "https://discordapp.com/api/webhooks/{$value['webhook_id']}/{$webhook_token['webhook_token']}";

					$this->reminder->curlToDiscord('POST', $url, $value['message'], FALSE);
				}
			}
		}

		return;
	}

	private function checkRepeat($repeats, $currDay) {

		$weekday = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];

		$weekend = ['saturday', 'sunday'];

		$approved = FALSE;

		if(strpos($repeats, ',')) {

			$repeats = explode(',', $repeats);
		}

		if(is_array($repeats)) {

			if(in_array($currDay, $repeats)) {

				$approved = TRUE;
			}

		} else {

			if($repeats === $currDay || $repeats === 'everyday') {

				$approved = TRUE;

			} else if($repeats === 'weekday' && in_array($currDay, $weekday)) {

				$approved = TRUE;

			} else if($repeats === 'weekend' && in_array($currDay, $weekend)) {

				$approved = TRUE;				
			}
		}

		return $approved;		
	}
}

$qwe = new TimerCron();

$qwe->checkTime();
