<?php



/*
		$servername = "localhost";
		$username = "root";
		$password = "";
		$dbname = "remindMe";
		global $conn;

		$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
	    // set the PDO error mode to exception
	    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);*/
	    // echo "Connected successfully"; 

namespace App\config;

use PDO;

class Database {

	public function connect() {

		/*$servername = "localhost";
		$username = "root";
		$password = "";
		$dbname = "remindMe";
		// global $conn;

		$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
	    // set the PDO error mode to exception
	    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	    // echo "Connected successfully"; 	*/	

		$db = parse_url(getenv("DATABASE_URL"));

		$conn = new PDO("pgsql:" . sprintf(
		    "host=%s;port=%s;user=%s;password=%s;dbname=%s",
		    $db["host"],
		    $db["port"],
		    $db["user"],
		    $db["pass"],
		    ltrim($db["path"], "/")
		));

		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		return $conn;
	}
}
