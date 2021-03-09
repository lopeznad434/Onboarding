<?
class Layout{

  function __construct($db){
      $this->db = $db;
      $this->title = '';
      $this->notices = [];
      $this->warnings = [];
      $this->content= [];
  }

  function setTitle($titleText){ $this->title = $titleText; }
  function addContent($newContent){ $this->content[]= $newContent; }

  function addNotice($newNotice){
    if (is_array($newNotice)){
      $this->notices = array_merge($this->notices, $newNotice );
    }
    else{
      $this->notices[]= $newNotice;
    }
  }

  function addWarning($newWarning){
    if (is_array($newWarning)){
      $this->warnings = array_merge($this->warnings, $newWarning );
    }
    else{
      $this->warnings[]= $newWarning;
    }
  }

  function header(){ ?>
    <html>
    <head>
      <title><?=$this->title?></title>
      <link href="favicon.ico" rel="icon" type="image/x-icon" />

      <!-- JQuery https://code.jquery.com/ -->
      <script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>

      <!-- Bootstrap  https://v5.getbootstrap.com/ -->
      <!-- Bootstrap CSS -->
      <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-CuOF+2SnTUfTwSZjCXf01h7uYhfOBuxIhGKPbfEJ3+FqH/s6cIFN9bGr1HmAg4fQ" crossorigin="anonymous">


      <link rel="stylesheet" type="text/css" href="assets/css/styles.css?2">


      <!-- Bootstrap JavaScript Bundle with Popper.js -->
      <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-alpha3/dist/js/bootstrap.bundle.min.js" integrity="sha384-popRpmFF9JQgExhfw5tZT4I9/CI5e2QcuUZPOVXb1m7qUmeR2b50u+YFEYe1wgzy" crossorigin="anonymous"></script>
      <!-- FontAwesome (get your own "Kit" at https://fontawesome.com )-->
      <script src="https://kit.fontawesome.com/5535203e19.js" crossorigin="anonymous"></script>
      <!-- jquery / ajax for the like button.-->
      <script>
        function likepost(post_id){
          $.ajax({ url: "index.php?like="+post_id	}).done(function( likes ) {
            console.log(likes);
            console.log(post_id);
            $( "#post_" + post_id + " .likes").html( likes );
            $( "#post_" + post_id + " .btn").toggleClass( "btn-warning" );
          });
        }
      </script>
    </head>
    <body>
<? } // end function header



  /* Build a menu bar.
  */
function navigation(){ global $user; ob_start(); ?>
  <nav class="navbar navbar-expand-sm navbar-light fixed-top justify-content-center">

    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarToggler" aria-controls="navbarToggler" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
    </button>
      <a class="navbar-brand pl-2" href="/catabase">CrewConnect</a>

      <div class="collapse navbar-collapse w-auto" id="navbarToggler">
        <ul class="navbar-nav mt-2 mt-lg-0 justify-content-center">
          <li class="nav-item <?= $this->linkClass( $user->link() ); ?>">
            <a class="nav-link" href="<?= $user->link(); ?>">
              <i class="fas fa-anchor"></i> <?= $user->fullName; ?>
            </a>
          </li>
          <li class="nav-item <?= $this->linkClass('?explore'); ?>">
            <a class="nav-link" href="/catabase/?explore">
              <i class="fas fa-users"></i> Plunder
            </a>
          </li>
          <li class="nav-item <?= $this->linkClass('?messages'); ?>">
            <a class="nav-link" href="/catabase/?messages">
              <i class="fas fa-wine-bottle"></i> Messages <?= $user->getMessageCount(); ?>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/catabase/?logout">  <i class="fas fa-sign-out-alt fa-lg"></i> Logout</a>
          </li>
        </ul>
      </div>
  </nav>
  <? $this->content[]= ob_get_clean();
 }

/* If the current url contains the given string
* return "active", for use in CSS. */
function linkClass($scriptName){
    if (strpos($_SERVER['REQUEST_URI'], $scriptName ) !== false) {
        return 'active';
    }
}

function explore($user){ ob_start(); ?>

  <div id="mainContent" class="container">
    <div class="row">
      <div class="col-md-3">
        <?
          $user->displayProfile();
          $user->friendsPanel();
        ?>
      </div>
      <div class="col-md-9">
        <div class="catlist">
          <h2>Sailors In Your Sea</h2>
          <?php
            $users = $this->db->q("SELECT * FROM users WHERE id !='$user->id'");
            while (  $data = $users->fetch_object() ){
                $user = new User ( $this->db, $data );
                $user->displayListing();
            }	?>
        </div>
      </div>
    </div>
  </div>
<? $this->content[]= ob_get_clean();
}



function messages(){
  global $user;
  if (in_array($_REQUEST['messages'], ['inbox', 'sent', 'compose'])){
    $action = $_REQUEST['messages'];
  }
  else{
    $action = 'inbox';
  }
  ob_start(); ?>
  <div id="mainContent" class="container">
  	<div class="row">
  		<div class="col-md-3">

        <div class="profile pt-3">
    			<h2 class="mt-3">Messaging</h2>
    			<ul style="list-style-type: none; padding: 0px;">
    					<li><a href="?messages=compose"
                class="mb-1 btn btn-secondary <?= $this->linkClass('?messages=compose');?>"><i class="fas fa-pen"></i> Compose</a>
              </li>
    					<li><a href="?messages=inbox"
                class="mb-1 btn btn-secondary <?= $this->linkClass('?messages=inbox');?>"><i class="fas fa-envelope"></i> Inbox</a>
              </li>
    					<li><a href="?messages=sent"
                class="mb-1 btn btn-secondary <?= $this->linkClass('?messages=sent');?>"><i class="fas fa-paper-plane"></i> Sent</a>
              </li>
    			</ul>
    		</div>
        <?
          //$user->displayProfile();
          // $user->friendsPanel();
        ?>
  		</div>
  		<div class="col-md-9">
        <div class="catlist">
          <?
            if ($action == 'compose'){  $this->compose();  }
            elseif ($action == 'inbox'){ $this->inbox(); }
            elseif ($action == 'sent'){ $this->sent(); }
          ?>
        </div>
      </div>
  	</div>
  </div>
<?
$this->content[]= ob_get_clean();
}


function inbox(){ global $user; ?>
  <h2>Inbox For <?=$user->fullName?></h2>
  <?
    $messages = $this->db->q(
            "SELECT * FROM messages
            WHERE to_id = $user->id
            ORDER BY sent_at DESC LIMIT 5");
    if ($messages->num_rows > 0){
      while (  $data = $messages->fetch_object()){
          $message = new Message ( $this->db, $data );
          $message->display();
      }
    }
    else{ ?>
      <p>There be no messages for ye, sailor.</p>
    <? }
 }

 function sent(){ global $user; ?>
   <h2>Messages sent by <?=$user->fullName?></h2>
   <?
     $messages = $this->db->q(
             "SELECT * FROM messages
             WHERE from_id = $user->id
             ORDER BY sent_at DESC LIMIT 5");
     while (  $data = $messages->fetch_object()){
         $message = new Message ( $this->db, $data );
         $message->display();
     }
  }

  function compose(){ global $user; ?>
    <h2>Send a Private Message</h2>
    <? if (! $user->friends() > 0) { ?>
      <p>To send a message, </a href="?explore">connect with yer crew</a>. </p>
    <? } else { ?>
    <form id="message_form" action="index.php" method="POST" enctype="multipart/form-data">
      <input type="hidden" name="messages" value="send">
      <input type="hidden" name="from" value="<?=$user->id?>">
      <div class="form-group">
        <label for="to"> To: </label>
        <select class="form-select mb-3" id="to" name="to" >
          <?php foreach ($user->friends() as $friendship){
            $friend = $friendship->friend_of($user->id);
            $selected = ($_REQUEST['to'] == $friend->id)? 'selected' : '';
            ?>
            <option value="<?=$friend->id?>" <?=$selected?>>
                <?=$friend->fullName?>
            </option>
          <?php }	?>
        </select>
      </div>
      <div class="form-group">
        <textarea class="form-control" id="body" name="body" placeholder="Hey it's me, <?=$user->fullName;?>."></textarea>
      </div>
      <div class="row">
        <div class="col">
          <label class="btn btn-default btn-lg btn-file text-white">
              <i class="fas fa-paperclip" aria-hidden="true"></i> <input onchange="showFileName(this)" type="file" name="attachment" id="attachment" style="display: none;"> <span id="attachment_file_name"></span>
          </label>
          <script>
            function showFileName(elm) {
               var fn = $(elm).val();
               var filename = fn.match(/[^\\/]*$/)[0]; // remove C:\fakename
               $('#attachment_file_name').html(filename);
            }
          </script>
        </div>
        <div class="col text-right">
          <button type="submit" name="send_message" id="send_message" class="btn btn-lg btn-warning mt-2"><i class="fas fa-paper-plane"></i> Send</button>
        </div>
      </div>
    </form>
  <? }
} //end function compose



function profile($user){ ob_start(); ?>
  <div id="mainContent" class="container">
  	<div class="row">
  		<div class="col-md-4">
  			<?
          if (isset($_REQUEST['edit'])){
            $user->editProfile();
          }
          else{
            $user->displayProfile();
          }

  				$user->friendsPanel();
  			?>
  		</div>
  		<div class="col-md-8">
        <div class="catlist">
          <?
            $this->meowform();
    			  $this->meowsBy($user);
          ?>
        </div>
      </div>
  	</div>
  </div>
<?
$this->content[]= ob_get_clean();
}

function newsfeed($user){ ob_start(); ?>
  <div id="mainContent" class="container">
  	<div class="row">
  		<div class="col-md-3">
  			<?
  				$user->displayProfile();
  				$user->friendsPanel();
  			?>
  		</div>
  		<div class="col-md-9">
        <div class="catlist">
          <?
            $this->meowform();
    			  $this->latestMeows($user);
          ?>
        </div>
      </div>
  	</div>
  </div>
<?
$this->content[]= ob_get_clean();
}



function meowform(){ ?>
  <form id="meow_form" action="index.php" method="POST" enctype="multipart/form-data">
    <!--WHen adding a new post show the profile of the logged-in user -->
    <input type="hidden" name="profile" value="<?=$GLOBALS['user']->id?>">
    <h2>Post a message in a bottle</h2>
    <div class="form-group">
      <textarea class="form-control" id="meow_text" name="meow_text" placeholder="What be the news, <?=$GLOBALS['user']->fullName;?>?"></textarea>
    </div>
    <div class="row">
      <div class="col">
        <label class="btn btn-default btn-lg btn-file text-white">
            <i class="fas fa-paperclip" aria-hidden="true"></i> <input onchange="showFileName(this)" type="file" name="meow_file" id="meow_file" style="display: none;"> <span id="meow_file_name"></span>
        </label>
        <script>
          function showFileName(elm) {
             var fn = $(elm).val();
             var filename = fn.match(/[^\\/]*$/)[0]; // remove C:\fakename
             $('#meow_file_name').html(filename);
          }
        </script>
      </div>
      <div class="col text-right">
        <button type="submit" name="meow_button" id="meow_button" class="btn btn-lg btn-warning mt-2"><i class="fas fa-skull-crossbones"></i> Post</button>
      </div>
    </div>
  </form>

<? }


// not currently using this version but it's available if needed.
// note the use of two WHERE ... IN clauses.
function friendsMeows($user){ ob_start(); ?>
  <h2>Messages By Crew Mates</h2>
  <? $posts = $this->db->q(
        "SELECT * FROM posts
        WHERE user_id IN
          (SELECT user_one_id FROM friendships WHERE user_two_id = $user->id)
        OR user_id IN
          (SELECT user_two_id FROM friendships WHERE user_one_id = $user->id)");
  while (  $data = $posts->fetch_object()){
      $post = new Post ( $this->db, $data );
      $post->display();
  }
  $this->content[]= ob_get_clean();
}

function latestMeows($user){  ?>
  <h2>Crew Feed</h2>
  <?

    $posts = $this->db->q("SELECT * FROM posts WHERE user_id != $user->id ORDER BY id DESC LIMIT 15");
    while (  $data = $posts->fetch_object()){
        $post = new Post ( $this->db, $data );
        $post->display();
    }
}

function meowsBy($user){  ?>
  <h2>Messages By <?=$user->fullName?></h2>
  <?
    $posts = $this->db->q(
            "SELECT * FROM posts
            WHERE user_id = $user->id
            ORDER BY id DESC LIMIT 5");
    while (  $data = $posts->fetch_object()){
        $post = new Post ( $this->db, $data );
        $post->display();
    }
 }


function regForm(){ ?>
  <form class="mt-2 p-4 text-center" id="regForm" action="index.php" method="POST">
    <h2>New Account</h2>
    <input class="mt-2 form-control" type="text" name="reg_fullName" placeholder="Name" value="<?=$_SESSION['reg_fullName']?>" required>
    <input class="mt-2 form-control" type="email" name="reg_email" placeholder="Email" value="<?=$_SESSION['reg_email']?>" required>
    <input class="mt-2 form-control" type="password" name="reg_password" placeholder="Password" required>
    <input class="mt-2 form-control" type="text" name="reg_quote" placeholder="Pirate Battlecry" value="<?=$_SESSION['reg_quote']?>">
    <button  class="mt-4 btn btn-warning" type="submit" name="reg_button">Join the crew</button>
      <p  class="mt-4">
        Already a crewmate? <a href="#" id="signin" class="signin">Sign in here!</a>
      </p>
  </form>
<? }


function loginForm(){ ?>
  <form class="mt-2 p-4 text-center" id="loginForm" action="/catabase/" method="POST">
      <h2>Board the ship</h2>
      <input class="mt-2 form-control" type="email" name="login_email" placeholder="Email Address" value="<?=$_SESSION['login_email']?>" required>
      <input class="mt-2 form-control" type="password" name="login_password" placeholder="Password">
      <button class="mt-4 btn btn-warning" type="submit" name="login_button">Board the ship</button>
      <p  class="mt-4">
        Want to join the crew? <a href="#" id="signup" class="signup">Register here!</a>
      </p>
  </form>
<? }

function onboarding(){ ob_start(); ?>
<div class="container">
  <div class="row">
    <div class="onboarding col-md-6 p-2 mt-5">
  		<h1>CrewConnect</h1>
  		<?
        $this->notices();
        $this->warnings();
    		$this->loginForm();
        $this->regForm();
      ?>
  	</div>
    <div class="col-md-6">
    </div>
  </div>
</div>
  <!-- jQuery animation -->
	<script>
  	$(document).ready(function() {
  		$("#signup").click(function() {
        $(".alert").slideUp("slow", function(){
          $(this).remove();
        });
  			$("#loginForm").slideUp("slow", function(){
  				$("#regForm").slideDown("slow");
  			});
  		});
  		$("#signin").click(function() {
        $(".alert").slideUp("slow", function(){
          $(this).remove();
        });
  			$("#regForm").slideUp("slow", function(){
  				$("#loginForm").slideDown("slow");
  			});
  		});
  		<? if (isset($_REQUEST['login_form'])){ ?>
        $("#loginForm").show();
        $("#regForm").hide();
      <? }	else { ?>
  			$("#loginForm").hide();
  			$("#regForm").show();
  		<? } ?>
  	});
	</script>
<? $this->content[]= ob_get_clean();
 }  // end function onboarding


  function warnings(){
    if ( empty($this->warnings) )  return; ?>
    <div id="warnings" class="container mt-2">
        <? foreach ( $this->warnings  as $warning ){ ?>
          <div class="alert alert-danger mt-2" role="alert">
              <?= $warning ?>
          </div>
        <? }?>
    </div>
  <? }

  function notices(){
    if ( empty($this->notices) )  return; ?>
    <div id="notices" class="container mt-2">
        <? foreach ( $this->notices  as $notice ){ ?>
          <div class="alert alert-success mt-2" role="alert">
              <?= $notice ?>
          </div>
        <? }?>
    </div>
  <? }

  function content(){ ?>
      <main id="content" class="container-fluid">
          <?= implode($this->content); ?>
      </main>
  <? }


  function footer(){ ?>
    <footer class="footer text-white bg-dark p-5 text-muted text-center">

       <div class="container">
         Created with <a class="text-muted" href="https://v5.getbootstrap.com/">Bootstrap</a>, PHP, and MySQL by <a class="text-muted" href="https://www.nsitu.ca">Harold Sikkema</a>. Photo by <a class="text-muted" href="https://unsplash.com/@purzlbaum">Claudio Schwarz</a>.
       </div>
     </footer>
    </body>
    </html>
  <?
  }

}  // end of Layout Class.
?>
