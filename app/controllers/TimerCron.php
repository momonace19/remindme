<?php 

namespace App\controllers;

include dirname( __FILE__, 3 ).'/vendor/autoload.php';

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

					$this->reminder->postToDiscord($value['message']);
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
