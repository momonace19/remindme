<?php 

namespace App\controllers;

include dirname( __FILE__, 3 ).'/vendor/autoload.php';

// date_default_timezone_set('America/Los_Angeles');
date_default_timezone_set('America/New_York');

use App\controllers\Reminder;
use App\models\Mreminder;

class TimerCron {

	private $reminder;
	private $mreminder;

	public function __construct() {

		$this->reminder = new Reminder;

		$this->mreminder = new Mreminder;

	}

	public function checkTime() {

		$currTime = date("H:i");

		$currDay = strtolower(date("l"));

		$reminderInfo = $this->mreminder->showByTime($currTime);

		if(!empty($reminderInfo)) {

			foreach ($reminderInfo as $key => $value) {

				$approved = $this->checkRepeat($value['repeats'], $currDay);

				if($approved) {

					$webhook_id = $this->mreminder->getWebHookIdByWebHookToken($value['webhook_token']);

					$url = "https://discordapp.com/api/webhooks/{$webhook_id['webhook_id']}/{$value['webhook_token']}";

					$qwe = $this->reminder->curlToDiscord('POST', $url, $value['message'], FALSE);

					print_r($qwe);
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
