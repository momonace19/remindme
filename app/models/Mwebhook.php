<?php 

namespace App\models;

use App\config\Database;
use PDO;

class Mwebhook {

	private $conn;
	private $db;

	public function __construct() {

		$this->db = new Database();

		$this->conn = $this->db->connect();
	}

	public function getWebHook($guild_id) {

		$stmt = $this->conn->prepare("SELECT * FROM webhook WHERE guild_id='$guild_id'"); 
    	$stmt->execute();

    	$result = $stmt->setFetchMode(PDO::FETCH_ASSOC); 

		return $stmt->fetch();
	}

	public function setWebHook($guild_id, $channel_id, $webhook_details) {

		$sql = "INSERT INTO webhook (guild_id, channel_id, webhook_id, webhook_token) VALUES ('$guild_id', '$channel_id', '{$webhook_details['id']}', '{$webhook_details['token']}')";

	    // use exec() because no results are returned
	    return $this->conn->exec($sql);
	}

	public function updateWebHookChannel($channel_id, $guild_id) {

		$sql = "UPDATE webhook SET channel_id = '$channel_id' WHERE guild_id = '$guild_id'";

	    // Prepare statement
	    $stmt = $this->conn->prepare($sql);

	    // execute the query
	    $stmt->execute();

	    return $stmt->rowCount();
	}

	public function deleteWebHook($webhook_id) {
		
		$sql = "DELETE FROM webhook WHERE webhook_id IN ($webhook_id)";

		// use exec() because no results are returned
	    return $this->conn->exec($sql);
	}
}

