<?php
class User {
	function __construct($db, $data = NULL){
    $this->db = $db;

		// if we know the post id, fetch data for this post
		if(is_numeric($data)){
			$data = $this->db->q("SELECT * FROM users WHERE id = '$data'")->fetch_object();
		}
		// if we have data, import it.
		if(is_object($data)){
			foreach ($data as $key => $value){  $this->$key = $value; }
		}

	}

	function friendshipWith($friend){
		$users = [$this->id, $friend->id] ;
		return new Friendship($this->db, $users);
	}

	function isFriendOf($friend){
		$status = $this->friendshipWith($friend)->status();
		if ($status == 'Active') { return true; }
		return false;
	}

	function getPostCount() {
    $sql = "SELECT COUNT(*) AS `post_count` ".
           "FROM `posts` ".
           "WHERE `user_id` = '".$this->id."'";
    $result = $this->db->q($sql);
    $row = $result->fetch_object();
		return $row->post_count;
	}

	function saveProfile(){
		$quote = $this->db->safety($_REQUEST['quote']);
		$avatar = $this->db->safety($_REQUEST['avatar']);
		$sql = "UPDATE `users` SET `quote`= '$quote', `avatar` = '$avatar' WHERE `id` = '$this->id'";
		//echo $sql;
		$this->db->q($sql);
		header("Location: index.php");
	}


	function displayProfile(){ global $user;
		?>
		<div class="profile">
			<a class="avatar" href="<?= $this->link(); ?>">
				<img src="<?= $this->avatar; ?>" alt="<?=$this->username;?>">
			</a>
			<h2><a class="catname" href="<?= $this->link(); ?>"><?=$this->fullName;?></a></h2>
			<span class="label">joined:</span>
			<span><?= date( 'M d, Y', strtotime($this->signup_date) ); ?></span>
			<hr/>
			<p class="quote"><i class="fas fa-quote-left"></i> <?=$this->quote;?></p>
			<?php
					if($user->id != $this->id){
						// on other profiles show a status button for this relationship
						$this->friendStatusButton();
					}
					else{
						$this->editButton();
					}
			 ?>
		</div>
		<?
	}


	function editProfile(){ global $user;
		?>
		<div class="profile">
			<a class="avatar" href="<?= $this->link(); ?>">
				<img src="<?= $this->avatar; ?>" alt="<?=$this->username;?>">
			</a>
			<h2><a class="catname" href="<?= $this->link(); ?>"><?=$this->fullName;?></a></h2>
			<span class="label">joined:</span>
			<span><?= date( 'M d, Y', strtotime($this->signup_date) ); ?></span>
			<hr/>
			<form action="index.php" method="post" class="p-3">
				<input type="hidden" id="avatar" name="avatar" value="<?=$this->avatar?>">

					<div class="avatars">
						<?php for ($i = 1; $i<9 ; $i++){
							$class = ($this->avatar == 'assets/avatars/'.$i.'.png')?
									'btn btn-warning' : 'btn';
							?>
							<button class="avatar-button <?=$class?>" type="button"
								value="<?=$i?>"
								<?=$selected?>
								style="height: 50px; width: 50px; background-image: url('assets/avatars/<?=$i?>.png');">
							</button>
						<?php }	?>
					</div>
					<script>
						$('.avatar-button').on("click", function(e){
							$('.avatar img').attr('src', 'assets/avatars/'+$(e.target).attr('value') +'.png');
							$('#avatar').attr('value', 'assets/avatars/'+$(e.target).attr('value') +'.png' );
							$('.avatar-button').removeClass('btn-warning');
							$(e.target).addClass('btn-warning');
					  });
					</script>

				<div class="form-group py-3">
					<label for="quote" class="text-muted">What be yer battlecry?</label>
					<textarea name="quote" class="form-control" id="quote" rows="3"><?=$this->quote?></textarea>
				</div>
				<div class="form-group">
					<button type="submit" class="btn btn-warning" name="save_profile"><i class="fas fa-save"></i> Save</button>
				</div>
			</form>
		</div>
		<?
	}


		function displayListing(){
			// output a minimal profile for this user
			// to be displayed in the context of a listing.
			?>
			<div class="container userListing mt-2" style="background: rgba(0,0,0,0.1);">
				<div class="row">
			<div class="col-2 mb-4" >
				<img style="width: 100%;" src="<?= $this->avatar; ?>" class="align-self-end mr-3" alt="<?=$this->username;?>">
				</div>
				<div class="col-10">
					<h5 class="mt-2" >
						<a style="color: #fff;" href="<?=$this->link();?>"><?=$this->fullName;?></a>
					</h5>
					<?php $this->friendStatusButton(); ?>
					<p>
						<span class="label">joined:</span>
						<span><?= date( 'M d, Y', strtotime($this->signup_date) ); ?></span>
					</p>
				</div>

		</div>
		</div>
			<?
		}

	function link(){
		return '/catabase/?profile='.$this->id;
	}



	function friendsPanel(){
		// display details about friendships.

		?> <div class="profile pt-3">	<?php
			if($GLOBALS['user']->id == $this->id){
				// when you are on your own profile show friend requests
				$this->friendRequests();
			}
			$friendships = $this->friends();
			// if you have more that zero friends, build a friend list.
			if (count($friendships) > 0){  ?>
				<h2 class="mt-3">Crew Mates</h2>
				<ul style="list-style-type: none; padding: 0px;"><?php
					foreach ($friendships as $friendship){
						$friend = $friendship->friend_of($this->id); ?>
						<li>
							<a href="<?=$friend->link()?>"><?=$friend->fullName?></a>
						</li>
					<?php }	?>
				</ul>
			<? } else{ ?>
					<h2 class="mt-3">Crew Mates</h2>
					<p>Time To <a href="/catabase/?explore">Find Some Crew Mates</a>.</p>
			<?}?>
		</div>
<?php }


function editButton(){ ?>
			<a href="index.php?profile=<?=$this->id?>&edit" class="btn btn-warning">
				<i class="fas fa-edit"></i>Edit</a>
<?}

function friendStatusButton(){

	// display a friendship status button relative to the logged in user.
	$friendship = $this->friendshipWith($GLOBALS['user']);
	$status = $friendship->status();
	//var_dump( $friendship->user_two_id);
	if ($status == 'Active') {	echo $this->activeButton();		}
	elseif ($status == 'Requested'){
	 echo ( $this->id == $friendship->requestedBy()->id) ?
		 $this->approveButton() : $this->pendingButton();
	}
	elseif ($status == 'Inactive'){	echo $this->friendButton();	}
}

function getMessageCount(){
    $sql = "SELECT COUNT(*) AS `message_count`
						FROM `messages`
						WHERE `to_id` = '$this->id'
						AND `recieved_at` IS NULL ";

					//	echo $sql;
    $result = $this->db->q($sql);
    $row = $result->fetch_object();
		if ($row->message_count > 0 ){
			return '<b>('.$row->message_count.')</b>';
		}
		return '';
}


function activeButton(){ ob_start(); ?>
	<button type="button" class="btn btn-success">
			<i class="fas fa-check"></i><i class="fas fa-user-friends"></i> Crew Mates
	</button>
	<a class="btn btn-warning" href="?messages=compose&to=<?=$this->id?>"><i class="fas fa-envelope"></i></a>
<? return ob_get_clean();
}

	function friendButton(){
		// button to request a friendship with this user
		?>
		<form action="/catabase/">
				<input type="hidden" name="profile" value="<?=$this->id?>">
				<div class="form-group">
				<button name="friend_button" type="submit" class="btn btn-warning">
					<i class="fas fa-user-friends"></i>+ Add Crew Mate
				</button>
				</div>
		</form>
		<?php
	}

	function pendingButton(){
		return '<button type="button" class="btn btn-warning">'.
			'<i class="fas fa-user-friends"></i>+ Pending Approval'.
			'</button>';
	}

		function approveButton(){
			// button to approve a friend request from this user
			?>
			<form action="/catabase/">
					<input type="hidden" name="profile" value="<?=$this->id?>">
					<div class="form-group">
					<button name="approve_button" type="submit" class="btn btn-warning btn-success">
						<i class="fas fa-user-friends"></i>+ Aye Aye, <?=$this->fullName?>
					</button>
					</div>
			</form>
			<?php
		}


	function friendRequests(){
		// show a list of friend requests pending approval for this user
		// THE USER MUST BE LOGGED IN
		if ($this->username != $_SESSION['username']) return;

		$sql = "SELECT * FROM friendships
				WHERE user_two_id = '$this->id'
				AND requested_at IS NOT NULL
				AND approved_at IS NULL ";

		$requests = $this->db->q($sql);
		if ($requests->num_rows > 0){
			?> <h2>Crew Mate Requests</h2> <?php
			while ( $data = $requests->fetch_object() ){
				$friendship = new Friendship($this->db, $data);
				$friendship->friend_of($this->id)->showRequest();
			}
		}

	}

	function showRequest(){
		// show a notice that someone asked to befriend this user
		?> <p><a href="/catabase/?profile=<?=$this->username?>">
				<?=$this->fullName;?></a> asks: will ye join me crew?
		</p>
		<?php
		$this->approveButton();
	}

	function friends(){
		// return an array of all approved friendships
		$friends = []; // start with an empty array
		$sql = "SELECT * FROM friendships
					WHERE (user_one_id = '$this->id' OR user_two_id = '$this->id' )
					AND approved_at IS NOT NULL ";

		if ( $result = $this->db->q($sql) ){
			while ( $data = $result->fetch_object() ){
				$friends[] = new Friendship($this->db, $data);
			}
		}
		else{	echo $this->db->ixd->error; }
		return $friends;
	}

    function likesPost($post){
			// given a post determine whether this user likes it or not.
      $like = new Like($this->db, $this, $post);
      return $like->exists();
    }

}

?>
