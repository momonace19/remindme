<?php 

namespace App\controllers;

// include '../../vendor/autoload.php';

use App\models\Mreminder;
// use App\controllers\TimerCron;

class Reminder {

	private $mreminder;

	public function __construct() {

		$this->mreminder = new Mreminder;
	}

	public function setreminder($message) {

		$respond = $this->helpreminder();

		$reminderKeys = ['message', 'timess', 'repeats'];

		$reminderInfo = explode('<', $message);

		unset($reminderInfo[0]);

		if(count($reminderInfo) === 3) {

			// remove > and change key's value
			for ($i=1; $i < 4; $i++) { 

				$reminderInfo[$reminderKeys[$i-1]] = substr(trim($reminderInfo[$i]), 0, -1);

				unset($reminderInfo[$i]);
			}

			$reminderInfo['repeats'] = strtolower($reminderInfo['repeats']);

			$ctr = $this->mreminder->store($reminderInfo);

			if($ctr === 1) {
				$respond = "Reminder saved.";
			} else {
				$respond = 'Error saving';
			}
		}

		return $respond;
	}

	public function showreminder() {

		$respond = '';

		$allReminders = $this->mreminder->show();

		if(is_array($allReminders)) {

			foreach ($allReminders as $keys => $reminder) {
			
				$respond .= PHP_EOL."[{$reminder['id']}] <{$reminder['message']}> <{$reminder['timess']}> <{$reminder['repeats']}>";
			}
		}

		return !empty($respond) ? $respond : 'No reminders';
	}

	public function deletereminder($message) {

		$error = FALSE;

		$respond = $this->helpreminder();

		$ids = explode('<', $message);

		unset($ids[0]);
		
		if(count($ids) === 1) {

			// remove > and explode
			$ids = explode(',', substr(trim($ids[1]), 0, -1));
			
			foreach ($ids as $key => $value) {
				
				if(!is_numeric($value)) {

					$error = TRUE;
				}
			}

			if(!$error) {

				$ids = implode(',', $ids);

				$ctr = $this->mreminder->delete($ids);

				if($ctr>0) {
					$respond = "Deleted $ctr reminder/s.";
				} else {
					$respond = 'Error deleting';
				}
			}

			return $respond;
		}
	}

	public function helpreminder() {

		$respond = PHP_EOL.'!setreminder - <message> <time ex. 23:05|06:02> <repeat ex. monday,wednesday|weekday|weekend|everyday>';
		$respond .= PHP_EOL."\t\tExample: !setreminder <World boss in 5 mins> <12:25> <everyday>";

		$respond .= PHP_EOL.PHP_EOL.'!showreminder - Shows all reminders';
		$respond .= PHP_EOL."\t\tExample: !showreminder";

		$respond .= PHP_EOL.PHP_EOL.'!deletereminder - <number ex. 1,6,7|2|3|4|5>';
		$respond .= PHP_EOL."\t\tExample - !deletereminder <1> or if you want to delete multiple reminders !deletereminder <1,2,6,8>";

		$respond .= PHP_EOL.PHP_EOL.'!helpreminder - Show all commands';
		$respond .= PHP_EOL."\t\tExample - !helpreminder";

		return $respond;
	}

	public function postToDiscord($message) {

		$message = '@everyone '.$message;

		$data = array("content" => $message, "username" => "remindMe");
	    $curl = curl_init("https://discordapp.com/api/webhooks/511051874812559360/_ilYnuxbnL-gB_CJYNS1Lr_VMS4g1YjmLThjgZib_Y_W-UB3CdXQlUOnCs-UcZ713rJx");
	    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
	    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
	    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	    return curl_exec($curl);
	}
}

/*$timer = new TimerCron();

$qwe = new Reminder();

print_r($qwe->setreminder($_POST['param']));
print_r($qwe->showreminder());
print_r($timer->checkTime());*/
