<?php
namespace MyApp;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class Chat implements MessageComponentInterface {
	protected $clients;

	public function __construct(){
		$this->clients = array();
	}

	public function onOpen(ConnectionInterface $conn) {
		$this->clients[] = $conn;
		echo "New connection! ({$conn->resourceId})\n";
	}

	//Commands are sent to the server in the following form:
	//Command_number [message]
	//For example, consider the following message:
	//1 Hello world.
	//The server reads the number on the left to determine the command that was sent.
	//The number on the left is followed by a space
	//Then, everything until the end is considered a string that is part of the message sent to the server
	public function onMessage(ConnectionInterface $from, $msg) {
		$command = intval($msg);
		$index_of_space = strpos($msg, " ");
		$data = substr($msg, $index_of_space + 1);
		if ($command == 1){
			//BROADCAST message
			//Broadcast a string to all connected clients, including to the client sending the message
			foreach ($this->clients as $client){
				$client->send("1 " . $data);
			}
		}
		else if ($command == 2){
			//Tells the server to save a piece of data on a single connected client. Which client is selected is unknown, and may be the sender.
			$number_of_clients = count($this->clients);
			$lucky_number = rand(0, $number_of_clients - 1);
			$chosen_client = $this->clients[$lucky_number];
			if ($chosen_client != null){
				$chosen_client->send("2 $data");
			}
			else{
				echo "Error: \$chosen_client is null";
			}
		}
		

	}

	public function onClose(ConnectionInterface $conn) {
		$shift = false;
		$number_of_clients = count($this->clients);
		for ($i = 0; $i < $number_of_clients; $i++){
			if ($this->clients[$i] === $conn){
				$shift = true;
			}
			if ($shift){
				$this->clients[$i] = $this->clients[$i + 1];
			}
		}
		unset($this->clients[number_of_clients - 1]);

		echo "Connection {$conn->resourceId} has disconnected\n";
	}

	public function onError(ConnectionInterface $conn, \Exception $e) {
		echo "An error has occurred: {$e->getMessage()}\n";
		$conn->close();
	}
}
