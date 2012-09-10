<?

class Error {

	var $errorName;
	var $errorProperties;
	var $emailAddress;
	function Error($_errorName, $_emailAddress="debug@lawlr.us") {
		$this->errorName = $_errorName;
		$this->emailAddress = $_emailAddress;
		$this->errorProperties = array();
	}

	function addProperty($_propertyName,$_propertyValue) {
		$c = count($this->errorProperties);
		$this->errorProperties[$c]['name'] = $_propertyName;
		$this->errorProperties[$c]['value'] = $_propertyValue;
	}

	function logError() {
		$err = "";
		foreach($this->errorProperties as $value) {
			if(is_array($value['value'])) {
				$err .= $value['name'] . " = '" . print_r($value['value'],true) . "'\n";
			} else {
				$err .= $value['name'] . " = '" . $value['value'] . "'\n";
			}
		}

		if(!mail($this->emailAddress,$this->errorName . " error",$err)) {
			echo "Logging Error.";
			die();
		}


	}


}

/*
$error1 = new Error("LOGIN_ERROR");
$error1->addProperty("username","2404040440");
$error1->addProperty("password","34343434343");
$error1->logError();
*/
?>