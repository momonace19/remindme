<?php 

namespace App\controllers;

// include '../../vendor/autoload.php';

use App\models\Mreminder;
use App\models\Mwebhook;
use App\controllers\TimerCron;

class Reminder {

	private $mreminder;
	private $mwebhook;
	private $message;
	private $base_url;

	public function __construct($message = '') {

		$this->mreminder = new Mreminder();
		$this->mwebhook = new Mwebhook();
		$this->message = $message;
		$this->base_url = 'https://discordapp.com/api/';
	}

	public function setreminder() {

		$db_webhook = $this->mwebhook->getWebHook($this->message->channel->guild_id);

		if(!empty($db_webhook)) {

			$client_webhook = $this->checkCLientWebHook($db_webhook);

			$client_webhook = json_decode($client_webhook,TRUE);

			//client webhook exist
			if(!isset($client_webhook['code'])) {

				$respond = $this->helpreminder();

				$reminderKeys = ['message', 'timess', 'repeats'];

				$reminderInfo = explode('<', $this->message->content);

				unset($reminderInfo[0]);

				if(count($reminderInfo) === 3) {

					// remove > and change key's value
					for ($i=1; $i < 4; $i++) { 

						$reminderInfo[$reminderKeys[$i-1]] = substr(trim($reminderInfo[$i]), 0, -1);

						unset($reminderInfo[$i]);
					}

					$reminderInfo['repeats'] = strtolower($reminderInfo['repeats']);

					$reminderInfo['webhook_id'] = $db_webhook['webhook_id'];

					$ctr = $this->mreminder->store($reminderInfo);

					if($ctr === 1) {
						$respond = "Reminder saved.";
					} else {
						$respond = 'Error saving';
					}
				}
			} else {

				$this->mwebhook->deleteWebhookAndReminders($db_webhook['webhook_id']);

				$respond = 'Reminder channel not yet configured.';
			}

		} else {

			$respond = 'Reminder channel not yet configured.';
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

	public function deletereminder() {

		$error = FALSE;

		$respond = $this->helpreminder();

		$ids = explode('<', $this->message->content);

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

		$respond = PHP_EOL.'!setreminder - <message> <time ex. 03:05> <repeat ex. monday,wednesday|weekday|weekend|everyday>';
		$respond .= PHP_EOL."\t\tExample: !setreminder <World boss in 5 mins> <12:25> <everyday>";

		$respond .= PHP_EOL.PHP_EOL.'!showreminder - Shows all reminders';
		$respond .= PHP_EOL."\t\tExample: !showreminder";

		$respond .= PHP_EOL.PHP_EOL.'!deletereminder - <numbers from !showreminder ex. 1,6,7|2|3|4|5>';
		$respond .= PHP_EOL."\t\tExample - !deletereminder <1> or if you want to delete multiple reminders !deletereminder <1,2,6,8>";

		$respond .= PHP_EOL.PHP_EOL.'!setreminderchannel - Set what channel the reminder will be posted';
		$respond .= PHP_EOL."\t\tExample - !helpreminder <general>";

		$respond .= PHP_EOL.PHP_EOL.'!helpreminder - Show all commands';
		$respond .= PHP_EOL."\t\tExample - !helpreminder";

		return $respond;
	}

	public function setreminderchannel() {

		$webhook_name = 'remindMe';

		$has_error = TRUE;

		$channel = explode('<', $this->message->content);

		unset($channel[0]);

		// remove >
		$channel = substr(trim($channel[1]), 0, -1);

		// url to get guild channels
		$url = $this->base_url."guilds/{$this->message->channel->guild_id}/channels";

		// get guild channels
		$guild_channels = $this->curlToDiscord('GET', $url);

		$guild_channels = json_decode($guild_channels,TRUE);

		// match channel name to get channel id
		foreach ($guild_channels as $key => $value) {
			
			if($value['name'] == $channel) {

				$channel_id = $value['id'];

				$has_error = FALSE;
			}
		}

		if(!$has_error) {

			//change to $channel_id for reminder by channel but reminder by guild is okay for now
			//check if already has webhook in db
			$db_webhook_exist = $this->mwebhook->getWebHook($this->message->channel->guild_id);

			// if not exist, create. else update
			if(!$db_webhook_exist) {

				$respond = $this->createReminderChannel($webhook_name, $channel_id, $this->base_url);

			} else {

				$discord_webhook_exist = $this->checkCLientWebHook($db_webhook_exist);

				$discord_webhook_exist = json_decode($discord_webhook_exist, TRUE);

				// if no discord webhook exist
				if(isset($discord_webhook_exist['code'])) {

					//delete db_webhook and reminders
					$deleted = $this->mwebhook->deleteWebhookAndReminders($db_webhook_exist['webhook_id']);

					if($deleted > 0) {

						// create new webhook
						$respond = $this->createReminderChannel($webhook_name, $channel_id, $this->base_url);
					}

				} else {

					$url = $this->base_url."webhooks/{$db_webhook_exist['webhook_id']}";

					$data = array('channel_id' => $channel_id);

					//update webhook
					$discord_webhook_updated = $this->curlToDiscord('PATCH', $url, $data);

					$discord_webhook_updated = json_decode($discord_webhook_updated, TRUE);

					// returns 1 in postgresql even with no changes
					$ctr = $this->mwebhook->updateWebHookChannel($discord_webhook_updated['channel_id'], $discord_webhook_updated['guild_id']);
var_dump($ctr);
					if($ctr === 1) {

						$respond = "Reminder channel updated to $channel.";
					} else {

						$respond = "Reminder channel is already set to $channel.";
					}

					return $respond;
				}
			}

		} else {

			$respond = 'Channel not available.';
		}

		return $respond;
	}

	private function createReminderChannel($webhook_name, $channel_id) {

		// url to create webhook
		$url = $this->base_url."channels/{$channel_id}/webhooks";

		$data = array('name' => $webhook_name);

		// create webhook for the given channel
		$webhook_details = $this->curlToDiscord('POST', $url, $data);

		$webhook_details = json_decode($webhook_details, TRUE);

		$ctr = $this->mwebhook->setWebHook($this->message->channel->guild_id, $channel_id, $webhook_details);

		if($ctr === 1) {
			$respond = "Channel saved.";
		} else {
			$respond = 'Error saving';
		}

		return $respond;
	}

	private function checkCLientWebHook($db_webhook) {

		$url = $this->base_url."webhooks/{$db_webhook['webhook_id']}/{$db_webhook['webhook_token']}";

		//check if guild also has the webhook
		return $discord_webhook_exist = $this->curlToDiscord('GET', $url);
	}

	public function curlToDiscord($method, $url, $data='', $header = TRUE) {

	    $ch = curl_init($url);     

		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method); 

		if($method != 'GET') {

			if(!$header) {

				$data = array("content" => $data);
			}

			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));  
		}                                             

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                                                      
		
		if($header) {

			curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
			    'Content-Type: application/json',                                                                                
			    'Authorization: Bot '.getenv('token'))
			);   
		}                                                                                                               
                                                                                                                 
		$result = curl_exec($ch);

		return $result;
	}
}

/*$timer = new TimerCron();

$qwe = new Reminder();

// print_r($qwe->setreminder($_POST['param']));
print_r($qwe->showreminder());
print_r($timer->checkTime());*/
