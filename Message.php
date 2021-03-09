<?
class Message {

	function __construct($db, $data = NULL){
    $this->db = $db;
		// if we know the message id, fetch data for this message
		if(is_numeric($data)){
			$data = $this->db->q("SELECT * FROM messages WHERE id = '$data'")->fetch_object();
		}
		// if we have data, import it.
		if(is_object($data)){
			foreach ($data as $key => $value){  $this->$key = $value; }
			$this->from = new User($db, $this->from_id);
      $this->to = new User($db, $this->to_id);
		}
		else{
			// if we have no data, assume the current user is author
			$this->from = $GLOBALS['user'];
		}
	}

  function recieved(){
    return ( $this->recieved_at != '' )? true : false;
  }



	function send($to, $body) {
    if (!is_numeric($to)){ return false; }
    $body = $this->db->safety($body); // prevent SQL injection
		if($body != "") {
			// prepare post data to be inserted
      $data = [
        'from_id' => $this->from->id,
        'to_id' => $to,
        'body' => $body,
        'image' => $this->fileName,
        'sent_at' => date("Y-m-d H:i:s")
      ];
      // wrap structure with `backticks` and content with regular "quotemarks"
      $columns = '`'.implode('`,`',array_keys($data)).'`';
      $values = '"'.implode('","', $data).'"';
      $sql = "INSERT INTO `messages` ($columns) VALUES ($values) ";
      $result = $this->db->q($sql); // run query
      //echo $sql;
      if ($result->error) { var_dump($result->error); }
			$this->id = $this->db->iid(); // get last inserted id
		}
    header('Location: index.php?messages=sent');
	}

  function isRead(){
      if ($this->recieved_at != ''){
        return true;
      }
      return false;
  }

  function isTo($id){
      if ($this->to_id == $id){
        return true;
      }
      return false;
  }


  function markAsRead(){
    $now = date("Y-m-d H:i:s");
    $sql = "UPDATE messages SET `recieved_at` = '$now'
            WHERE `id` = '$this->id' ";
    return $this->db->q($sql);
  }

	function uploadFile($upload){
		if($upload['name'] != ''){
			$file = strtolower( $upload['name'] );
			$folder = "/catabase/assets/images/messages/";
      // we can make this more secure
			$destination =  $folder . uniqid() . '_' . basename( $file );
			$fileType = pathinfo($file, PATHINFO_EXTENSION);
      // could open this up to any type of file
      // but this would require effort on the rendering side.
			if (in_array($fileType, ["jpg", "jpeg", "png", "gif"] )){
				if(move_uploaded_file($upload['tmp_name'], $_SERVER['DOCUMENT_ROOT'].$destination)) {
					$this->fileName = $destination;
				}
			}
		}
	}

	function niceDate(){
		$post_time = new DateTime($this->sent_at); //Time of send
		$now = new DateTime(date("Y-m-d H:i:s")); //Current time
		$age = $post_time->diff($now); //Difference between dates
		if($age->y >= 1) {
			return ($age->y == 1)? "Last Year" : $age->m . " years ago";
		}
		elseif($age->m >= 1) {
			return ($age->m == 1)? "Last Month" : $age->m . " months ago";
		}
		elseif($age->d >= 1) {
			return ($age->d == 1)? "Yesterday" : $age->d . " days ago";
		}
		else if($age->h >= 1) {
			return ($age->h == 1)? "1 hr ago" : $age->h . " hrs ago";
		}
		else if($age->i >= 1) {
			return ($age->i == 1)? "1 min ago" : $age->i . " mins ago";
		}
		else {
			return ($age->s < 30)? "Just now" : $age->s . " seconds ago";
		}
	}



	function display(){ global $user; ?>
	<div class="meow mb-4">
		<div id="message_<?=$this->id?>" class="row">
      <div class="col-2">
				<a href="<?=$this->from->link();?>">
						<img src="<?= $this->from->avatar; ?>" class="mr-3" alt="<?=$this->from->username;?>">
				</a>
      </div>
			<div class="col-10">
				<div class="card">
					<div class="card-header">
               <? if (! $this->isRead() && $this->isTo($user->id) ){?>
                 <span class="badge bg-success">New</span>
               <?}?>
				   		 <span class="label text-muted">
                 Sent <?= $this->niceDate(); ?> by
               </span>
               <a href="<?=$this->from->link();?>">
                 <?=$this->from->fullName;?>
               </a>
              <span class="label text-muted"> to </span>
              <a href="<?=$this->to->link();?>">
                 <?= $this->to->fullName; ?>
              </a>
				  </div>

					<? if ($this->image != ''){ ?>
						<img src="<?= $this->image; ?>" class="card-img-top">
					<? } ?>
					<div class="card-body">
						<p class="card-text"><?=$this->body?><p>
					</div>
					<div class="card-footer">
            <? if ($this->isTo($user->id) ) {
              $replyTo = $this->from->id;
            } else{
              $replyTo = $this->to->id;
            }?>
              <form>
               <input type="hidden" name="to" value="<?=$replyTo?>">
  						 <button type="submit" name="messages" value="compose" class="btn btn-warning" ><i class="fas fa-anchor"></i> Reply</button>
             </form>

					</div>
				</div>
      </div>
    </div>
	</div>
		<?
    // only mark sa read when viewed by  the recipient.
    // when viewed in the sender's "sent items" it doesn't count.
    if ($user->id == $this->to->id){
      $this->markAsRead();
    }

	} // end function display

}

?>
