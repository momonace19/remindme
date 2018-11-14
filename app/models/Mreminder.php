<?php 

namespace App\models;

use App\config\Database;
use PDO;

class Mreminder {

	private $conn;
	private $db;

	public function __construct() {

		$this->db = new Database();

		$this->conn = $this->db->connect();
	}

	public function store(Array $reminderInfo) {

		$sql = "INSERT INTO reminder (message, timess, repeats, webhook_id) VALUES 
				('$reminderInfo[message]', '$reminderInfo[timess]', '$reminderInfo[repeats]', '$reminderInfo[webhook_id]')";

	    // use exec() because no results are returned
	    return $this->conn->exec($sql);
	}

	public function show() {

		$stmt = $this->conn->prepare("SELECT * FROM reminder"); 
    	$stmt->execute();

    	$result = $stmt->setFetchMode(PDO::FETCH_ASSOC); 

		return $stmt->fetchAll();
	}

	public function showByTime($currTime) {

		$stmt = $this->conn->prepare("SELECT message, repeats, webhook_id FROM reminder WHERE timess='$currTime'"); 
    	$stmt->execute();

    	$result = $stmt->setFetchMode(PDO::FETCH_ASSOC); 

		return $stmt->fetchAll();
	}

	public function delete($ids) {

		$sql = "DELETE FROM reminder WHERE id IN ($ids)";

		// use exec() because no results are returned
	    return $this->conn->exec($sql);
	}
}

