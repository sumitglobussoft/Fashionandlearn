<?php include('includetop.php');?>
<html>
<header>

<link href="http://vjs.zencdn.net/4.12/video-js.css" rel="stylesheet">
<script src="http://vjs.zencdn.net/4.12/video.js"></script>
<script src="https://code.jquery.com/jquery-1.11.2.min.js"></script>
</header>
<body>
<video id="MY_VIDEO_1" class="video-js vjs-default-skin" controls
 preload="auto" width="640" height="264" poster="MY_VIDEO_POSTER.jpg"
 data-setup="{}">
 <source src="http://player.vimeo.com/external/124001455.sd.mp4?s=f4e1bd5f1816b850aaa7db6e17ae6678&profile_id=112&oauth2_token_id=59328475" type='video/mp4'>
 <source src="MY_VIDEO.webm" type='video/webm'>
 <p class="vjs-no-js">To view this video please enable JavaScript, and consider upgrading to a web browser that <a href="http://videojs.com/html5-video-support/" target="_blank">supports HTML5 video</a></p>
</video>

</body>
<script type="text/javascript">
$(document).ready(function(){
$('div').find('.vjs-poster').css('background','url("http://blog.medias.jilion.com/uploads/2010/01/sublime_video-600x480.png")');
$('div').find('.vjs-big-play-button').css('margin-left','35%');
$('div').find('.vjs-big-play-button').css('margin-top','12%');
});

</script>
</html>

<?php include('includebottom.php');?>
