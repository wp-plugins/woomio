<?php
/*
Plugin Name: Woomio
Plugin URI: https://www.woomio.com/en/
Description: This plugin eases the use of Woomio for WordPress users. With Woomio, anyone can post their purchases - and get a revenue from it.
Author: The Woomio Team
Version: 1.0
*/

add_action( 'add_meta_boxes', 'woomio_call_meta_box');

function woomio_call_meta_box()
{
	
	add_meta_box('woomio_box', 'Woomio', 'woomio_display_meta_box');
}

function woomio_display_meta_box()
{
?>
	
<div id="Woomio">

        <style>
          #Woomio {
            width: 100%;
            max-width: 600px;
          }

          #Woomio * {
          font-family: 'Open Sans', sans-serif;
          }

          #Woomio h2 {
          color: #666;
          }
          
          #Woomio #wooImg {
            width: 250px;
          }

          #Woomio input, #Woomio .success {
          width: 100%;
          max-width: 250px;
          }

          #Woomio .success {
          padding: 8px 0;
          margin-bottom: 20px;
          border: 1px solid transparent;
          border-radius: 4px;
          background-color: #dff0d8;
          border-color: #d6e9c6;
          color: #3c763d;
          display: none;
          }
        </style>

        <div id="connectLnk">
            <input type="button" onclick="connect();" class=" button button-primary button-large" value="Connect to Woomio" />
        </div>

        <div id="postLnk" style="display: none;">

            <h2>
                Your Woomio post:
            </h2>
            Image:<br />
            <img src="" id="wooImg" style="display: none;" />

            <div>
              <input type="button" onclick="updatePostImage(); return false;" class="button button-large" value="Take the first image from your post" /><br />
              <input type="text" id="wooImgUrl" placeholder="or enter an address for your image here" /><br /><br />
            </div>

            Text:<br /> <input type="text" id="wooText" placeholder="Your text" /><br /><br />

            Item:<br /> <input type="text" id="wooCode" placeholder="Your Woomio code" /><br />
            <input type="text" id="wooLink" placeholder="...or a link to where to buy the item" /><br /><br />

            <input type="button" onclick="postToWoomio();" id="postBtn" class=" button button-primary button-large" value="Post to Woomio" />
            <div class="success">&nbsp;&nbsp;&nbsp;Thank you for posting to Woomio</div>
        </div>

        <script type="text/javascript">

          /* Initialize WP events */

          // Woomio text
          jQuery('#title').on('keyup', function (key) {
          jQuery('#Woomio #wooText').val(jQuery(this).val());
          });

          // Woomio image
          var imgInterval = setInterval(function () {
          if (jQuery('#Woomio #wooImgUrl').val() == '') {
          updatePostImage();
          }
          else {
          clearInterval(imgInterval);
          }
          }, 2000);

          jQuery('#Woomio #wooImgUrl').on('keyup', function () {
          jQuery('#Woomio #wooImg').attr('src', jQuery(this).val()).show();
          });

          function updatePostImage() {
          if (jQuery('#content').val().indexOf(' src="') == -1)
          return false;

          var imgsrc = jQuery('#content').val().split(' src="')[1].split('"')[0];
          jQuery('#Woomio #wooImgUrl').val(imgsrc);
          jQuery('#Woomio #wooImg').attr('src', imgsrc).show();
          jQuery('#Woomio #wooImgDiv').show();
          }

          /* Flow */

          var token = "";
          var domain = 'https://www.woomio.com';

          function connect() {

          // Get code
          jQuery.ajax({
          url: domain + "/umbraco/api/OAuth/Code",
          async: false
          }).done(function (data) {
          authenticate(data);
          });
          }

          function authenticate(code) {
          var win = window.open(domain + '/umbraco/api/RemoteFbAuth/Connect?wcode=' + code);
          jQuery('#Woomio #connectLnk input').attr('disabled', 'disabled');
          var winInterval = setInterval(function () {
          if (win.closed) {
          clearInterval(winInterval);
          getToken(code);
          }
          }, 1000);
          }

          function getToken(code) {
          // Get code
          jQuery.ajax({
          url: domain + "/umbraco/api/OAuth/Token?code=" + code
          }).done(function (data) {
          token = data;

          jQuery('#Woomio #connectLnk').hide();
          jQuery('#Woomio #postLnk').fadeIn(500);
          });
          }

          function postToWoomio() {

          jQuery('#Woomio #postBtn').attr('disabled', 'disabled');

          var imgUrl = jQuery('#Woomio #wooImgUrl').val();
          var txt = jQuery('#Woomio #wooText').val();
          var code = jQuery('#Woomio #wooCode').val();
          var link = jQuery('#Woomio #wooLink').val();

          jQuery.ajax({
          url: domain + "/umbraco/api/Endpoints/Post?token=" + token + "&imageUrl=" + imgUrl + "&text=" + txt + "&code=" + code + "&link=" + link

                }).done(function (data) {

                    if (data == 'OK')
                    {
                        jQuery('#Woomio #postBtn').hide();
                        jQuery('#Woomio .success').fadeIn(500);
                    }
                    console.log("post = " + data);
                });
            }

        </script>

    </div>
<?php
}

?>