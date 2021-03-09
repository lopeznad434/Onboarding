<?
class Post {

	function __construct($db, $data = NULL){
    $this->db = $db;
		// if we know the post id, fetch data for this post
		if(is_numeric($data)){
			$data = $this->db->q("SELECT * FROM posts WHERE id = '$data'")->fetch_object();
		}
		// if we have data, import it.
		if(is_object($data)){
			foreach ($data as $key => $value){  $this->$key = $value; }
			$this->user = new User($db, $this->user_id);
		}
		else{
			// if we have no data, assume the current user is author
			$this->user = $GLOBALS['user'];
		}
	}

  function likes(){
    $sql = "SELECT * FROM `likes` WHERE `post_id` = '".$this->id."' ";
    return $this->db->q($sql)->num_rows;
  }

	function submit($body) {
    $body = $this->db->safety($body); // prevent SQL injection
		if($body != "") {
			// prepare post data to be inserted
      $data = [
        'user_id' => $this->user->id,
        'body' => $body,
        'image' => $this->fileName,
        'date_added' => date("Y-m-d H:i:s")
      ];
      // wrap structure with `backticks` and content with regular "quotemarks"
      $columns = '`'.implode('`,`',array_keys($data)).'`';
      $values = '"'.implode('","', $data).'"';
      $sql = "INSERT INTO `posts` ($columns) VALUES ($values) ";
      $result = $this->db->q($sql); // run query
			$this->id = $this->db->iid(); // get last inserted id
		}
	}

	function uploadFile($upload){
		if($upload['name'] != ''){
			$file = strtolower( $upload['name'] );
			$folder = "/catabase/assets/images/meows/";
			$destination =  $folder . uniqid() . '_' . basename( $file );
			$fileType = pathinfo($file, PATHINFO_EXTENSION);
			if (in_array($fileType, ["jpg", "jpeg", "png", "gif"] )){
				if(move_uploaded_file($upload['tmp_name'], $_SERVER['DOCUMENT_ROOT'].$destination)) {
					$this->fileName = $destination;
				}
			}
		}
	}

	function niceDate(){
		$post_time = new DateTime($this->date_added); //Time of post
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



	function display(){ ?>
	<div class="meow mb-4">
		<div id="post_<?=$this->id?>" class="row">
      <div class="col-2">
				<a href="<?=$this->user->link();?>">
						<img src="<?= $this->user->avatar; ?>" class="mr-3" alt="<?=$this->user->username;?>">
				</a>
      </div>
			<div class="col-10">
				<div class="card">
					<div class="card-header">
				   		<a href="<?=$this->user->link();?>"><?=$this->user->fullName;?></a> <span class="label text-muted">posted <?= $this->niceDate(); ?></span>
				  </div>

					<? if ($this->image != ''){ ?>
						<img src="<?= $this->image; ?>" class="card-img-top">
					<? } ?>
					<div class="card-body">
						<p class="card-text"><?=$this->body?><p>
					</div>
					<div class="card-footer">
						<? $btnClass = ( $GLOBALS['user']->likesPost($this) ) ? 'btn-warning' : ''; ?>
						 <button class="btn <?=$btnClass?>" onclick="likepost(<?=$this->id?>);"><i class="fas fa-anchor"></i></button> <span class="likes"><?=$this->likes();?></span>
					</div>
				</div>
      </div>
    </div>
	</div>
		<?
	}

}

?>
