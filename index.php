<?php
require 'appConfig.php';

$isAuth = $fb->getUser(); // check if user is authenticated
$loginURL = $fb->getLoginUrl(array(
	'scope' => 'publish_stream,manage_pages',
  'redirect_uri' => $baseURL.'/index.php'
	)
);
$logoutURL = $fb->getLogoutUrl(array(
	'next' => $baseURL.'/index.php'
	)
);

if(isset($_POST['NotesFileSubmit']) && isset($_FILES['NotesFile'])){
  $NotesFile = $_FILES['NotesFile'];
  move_uploaded_file($NotesFile['tmp_name'],'notes.csv');
  // read both files notes and page ids
  $Notes  = file('notes.csv');
  $PageID = file('pageids');
  // setup a cookie counting number of lines to both files
  setcookie('TotalNotesRow',count($Notes));
  setcookie('TotalPagesRow',count($PageID));
  setcookie('NoteRow',0);
  setcookie('PageRow',0);
}

?>

<!DOCTYPE html>
<html>
  <head>
    <title>FBAppAutoNote</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap.min.css" rel="stylesheet" media="screen">
  </head>
  <body style="padding-top:40px;">
    <div class="container">
    <?php if(!$isAuth):?>
      <div class="row" id="LoginDialog">
        <div class="col-lg-2 col-lg-offset-5">
          <a class="btn btn-primary btn-block" href="<?php echo $loginURL;?>">Login with Facebook</a>
        </div>
      </div>
    <?php endif;//user not authenticated?>

    <?php if($isAuth):?>

	    <div class="row" id="AppConfigDialog">
        <div class="col-lg-6 col-lg-offset-3">
          <div class="panel panel-default">
            <div class="panel-heading">
              <h3 class="panel-title">FACEBOOK AUTO NOTES APPLICATION</h3>
            </div>
            <div class="panel-body">

              <h4 id="notesCount" class="alert alert-danger" style="display:none;">
                <button type="button" class="btn btn-success btn-sm pull-right" onclick="publishOn();" id="publishOn">PUBLISH NOW!</button>
                THERE ARE <span class="counter">0</span> NOTES TO PUBLISH. 
              </h4>

              <section id="logger" style="display:none;">
                <div class="row">
                  <div class="col-sm-12">
                    <span class="pull-right">
                      <button type="button" class="btn btn-success btn-xs" onclick="clearLogs();">Clear Log</button>
                      <button type="button" class="btn btn-success btn-xs">Download Result</button>
                    </span>
                  </div>
                </div>

                <div class="row">
                  <div class="col-sm-12">
                    <pre id="ResultLog" class="pre-scrollable">
                      <!--result log-->
                    </pre>
                  </div>
                </div>
              </section>

            	<div class="panel panel-info">
                <div class="panel-heading">
                  <h3 class="panel-title">THE FOLLOWING PAGES WILL POST NOTES.</h3>
                </div>

                <div class="list-group" id="ManagePages">
                <?php 
                  //write page_id to disk 
                  $fp = fopen('pageids','w+');
                  // beginning this line, this will captures pages associated on your account
                  $acctpages = $fb->api('/me/accounts');
               	  foreach($acctpages['data'] as $page)://start loop account pages
                ?>
                  <a class="list-group-item" href="#" data-pageid="<?php echo $page['id'];?>" data-accesstoken="<?php echo $page['access_token'];?>" data-toggle="listItem">
                    <b class="glyphicon glyphicon-link pull-right" onclick="window.open('http://fb.com/<?php echo $page['id'];?>','_blank');"></b>
                    <h3 class="list-group-item-heading"><?php echo $page['name'];?></h3>
                    <p class="list-group-item-text">
                      <span id="PageCat" class="pull-right"><?php echo $page['category'];?></span>
                      <span id="PageUID"><?php echo $page['id'];?></span>
                    </p>
                  </a>
                <?php
                  fwrite($fp,$page['id'].':'.$page['access_token']."\n");//begin writing
                  endforeach;//end loop for account pages
                  fclose($fp);//close writing
                ?>
                </div>
              </div>

              <hr>
              <div class="col-lg-12">
                <form action="<?=$_SERVER['PHP_SELF']?>" method="POST" enctype="multipart/form-data" role="form">
                  <div class="form-group">
                    <label for="NotesFile">Browse Bulk Notes (CSV File)</label>
                    <input type="file" name="NotesFile" id="NotesFile">
                  </div>
                  <button type="submit" name="NotesFileSubmit" class="btn btn-primary">Upload</button>
                </form>
              </div>

            </div><!--/.panel-body-->
          </div><!--/.panel-->
        </div><!--/col-lg-*-->
      </div><!--/.row-->
    <?php endif;//user is authenticated?>
   	</div>
    
    <!--begin script-->
    <script src="//code.jquery.com/jquery.js"></script>
    <script>
    var notes = parseInt(getCookie('TotalNotesRow')), lastnote = parseInt(getCookie('NoteRow'));

    showNotesCount();
    function showNotesCount(){
      if(notes>lastnote){
        $('#notesCount > .counter').text(notes);
        $('#notesCount').show();
      }
      else{
        $('#notesCount').hide();
      }
    }
    function clearLogs(){
      $('#ResultLog').html('');
    }
    function publishOn(){
      var published = parseInt(getCookie('NoteRow'));
      $.get('poster.php',function(result){
        $('#notesCount').text('THERE ARE '+published+' NOTES PUBLISHED.')
        $('#ResultLog').prepend(result+"\n");
      }).success(function(){
        if(notes>published){
          publishOn();
        }
      });
      $('#publishOn').hide();
      $('#logger').show();
    }
    function getCookie(name) {
      var parts = document.cookie.split(name + "=");
      if (parts.length == 2) return parts.pop().split(";").shift();
    }
    </script>

  </body>
</html>