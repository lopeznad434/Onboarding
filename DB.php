<?php
class DB {

  function __construct(){
    $hostname = "localhost";
    $username = "ixd1671_yeet";
    $password = "llamascanbepurple";
    $database = "ixd1671_catabase";
    $this->ixd = new mysqli($hostname, $username, $password, $database);
    $this->ixd->set_charset("utf8");
    if ($this->ixd->connect_error) {  die("Error: " . $this->ixd->connect_error);   }
  }

  // Shortcut function to run queries.
  function q($sql){
    return $this->ixd->query($sql);
  }

  // Shortcut function to get last inserted ID
  //https://www.php.net/manual/en/mysqli.insert-id.php
  function iid(){
    return $this->ixd->insert_id;
  }

  // Validation function to help clean up input
  function safety($text){
    $text = strip_tags($text);  //Remove html tags
    $text = $this->ixd->real_escape_string($text);  // Make safe for Database.
    return $text;
  }


  function login(){
      $email = filter_var($_REQUEST['login_email'], FILTER_SANITIZE_EMAIL);
      $sql = "SELECT * FROM `users` WHERE `email` = '$email'";
      $result = $this->q($sql);
      if ( $user = $result->fetch_object() ){
        if (password_verify($_REQUEST['login_password'], $user->password)){
            $_SESSION['user_id'] = $user->id;
            header("Location: index.php");
        }
      }
      return 'Login failed. Please check your credentials. ';
  }


  /* validate and process user registrations  */
  function register(){
    //FullName
    $fullName = $this->safety($_REQUEST['reg_fullName']);
  	$_SESSION['reg_fullName'] = $fullName;
    //Email
  	$email = $this->safety($_REQUEST['reg_email']);
  	if (! filter_var($email, FILTER_VALIDATE_EMAIL) ){
    		$errors[] =  "Invalid email format";
  	}
  	elseif( $this->emailExists($email) ) {
  			$errors[] = "Email already in use";
  	}
  	$_SESSION['reg_email'] = $email; //Stores email into session variable
  	//Password
  	$password = $_REQUEST['reg_password'];
    //Fav. Quote
  	$quote = $this->safety($_REQUEST['reg_quote']);
    //Current date
  	$date = date("Y-m-d");
    //Validate name
  	if(strlen($fullName) > 100 || strlen($fullName) < 2) {
  		$errors[] = "Your name must be between 2 and 100 characters";
  	}
    //Validate password
  	if (strlen($password) < 6) {
  	   $errors[] = "Password is too short!";
  	}
  	/* Check Password Strength */
    /* More about regular expressions https://www.w3schools.com/php/php_regex.asp */
  	if (!preg_match("#[0-9]+#", $password)) {
  	   $errors[] = "Password must include at least one number!";
  	}
  	if (!preg_match("#[a-zA-Z]+#", $password)) {
  	   $errors[] = "Password must include at least one letter!";
  	}
    // only proceed if there are no errors.
  	if(empty($errors)) {
      //Encrypt before sending to database
      $password = password_hash($password, PASSWORD_DEFAULT);
  		//Assign a random profile picture
  		$number = rand(1, 16); //Random number between 1 and 16
  		$avatar = 'assets/avatars/'.$number.'.png';
  		// prepare data to be inserted
      $data = [
        'fullName' => $fullName,
        'email' => $email,
        'password' => $password,
        'quote' => $quote,
        'signup_date' => $date,
        'avatar' => $avatar
      ];
      // wrap structure with `backticks` and content with regular "quotemarks"
      $columns = '`'.implode('`,`',array_keys($data)).'`';
      $values = '"'.implode('","', $data).'"';
      $sql = "INSERT INTO `users` ($columns) VALUES ($values) ";
      if ( $this->q($sql) ){
        // redirect if registration was successful.
        header("Location: index.php?login_form&reg_success=".$email);
  		}
  		else{	$errors[] = "Something went wrong: ".$this->ixd->error; }
  	}
    return $errors;
  } // end funciton register

  //function to check if username already exists
  function userExists($username){
    $sql = "SELECT username FROM users WHERE username = '$username'";
    $count = $this->q()->num_rows;
    if ($count > 0 ){ return true; } else{ return false; }
  }

  //function to check if an email is already being used
  function emailExists($email){
    $sql = "SELECT email FROM users WHERE email = '$email'";
    $count = $this->q($sql)->num_rows;
    if ($count > 0 ){ return true; } else{ return false; }
  }


  // close the connection
  function close(){
    $this->ixd->close();
  }

}
?>
