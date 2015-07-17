<?php
class WoomioBloggerSettingsPage
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Start up
     */
    public function __construct()
    {
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        // This page will be under "Settings"
        add_options_page(
            'Settings Admin', 
            'Woomio', 
            'manage_options', 
            'woomio-blogger-setting-admin', 
            array( $this, 'create_admin_page' )
        );
    }


    //helper functions 
    public function woomio_loginform_display()
    {
        $data = get_option("woomio_blogger_option_name");

        return ctype_digit($data["woomio_blogger_id"]) ? "display:none;":""; 
    }

    public function woomio_blogger_post_form_display()
    {
        $data = get_option("woomio_blogger_option_name");
        return ctype_digit($data["woomio_blogger_id"]) ? "":"display:none;"; 
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        // Set class property
        $this->options = get_option( 'woomio_blogger_option_name' );
        ?>
        
        <script type="text/javascript" charset="utf-8" src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js"></script>
       
        <div class="wrap">
            <h2>Woomio Settings</h2>
            <div id="loginForm" style=<?php echo $this->woomio_loginform_display(); ?> >
                <h4>Please Login to woomio with your facebook account</h4>
                <input type="button" onclick="connect();" class="button button-primary" value="Connect to Woomio" />
                <div id="status"></div>
            </div>


            <form method="post" action="options.php" id="woomio_blogger_post_form" style=<?php echo $this->woomio_blogger_post_form_display(); ?> >
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'woomio_blogger_option_group' );   
                do_settings_sections( 'woomio-blogger-setting-admin' );
                submit_button(); 
            ?>
            </form>
        </div>
        
        <script type="text/javascript">
        var domain = "https://www.woomio.com";
        //var domain = "http://local.woomio.com";
        function renderStatus(statusText) {
            $("#status").html(statusText);
        }

        function connect() {
            // Get code
            $.ajax({
            url: domain + "/umbraco/api/OAuth/Code",
            async: false
            }).done(function (data) {
            authenticate(data);
            });
        }

        function authenticate(code) {
            var win = window.open(domain + "/umbraco/api/RemoteFbAuth/Connect?wcode=" + code, "", "width=1000，height=560");
            var winInterval = setInterval(function () {
            if (win.closed) {
            clearInterval(winInterval);
            getToken(code);
            }
            }, 1000);
        }

        function getToken(code) {
            // Get code
            console.log("Get code :"+code);
            $.ajax({
            url: domain + "/umbraco/api/OAuth/Token?code=" + code,
            async: false
            }).done(function (data) {
            //console.log("Step to getToken!!! The token is:"+ data);
            getBlogger(data,
            function (bloggerId) 
            {
                if (data == null) {
                    $("#status").html("Fail to Login.");
                }
                else {
                    $("#woomio_blogger_id").val(bloggerId.replace(/"/g, ""));
                    $.ajax({
                    url: window.location.href,
                    data: {"bloggerId":bloggerId},
                    type: "POST",
                    success : function()
                    {
                        //console.log("post success!");
                        $("#loginForm").hide();
                        $("#woomio_blogger_post_form").show();
                    }
                    });
                }
            },
            function (errorMessage)
            { renderStatus("Cannot get woomio blogger right now!" + errorMessage); });
        });
        }

        function getBlogger(token, callback, errorCallback) {
            var wooUrl = domain + "/umbraco/api/endpoints/GetBlogger?token=" + token;
            var x = new XMLHttpRequest();
            x.open('GET', wooUrl);
            x.responseType = '';
            x.onload = function () {
            var bloggerId = x.responseText;
            if (!bloggerId || bloggerId.length === 0) {
            errorCallback('No response from Woomio Server!');
            return;
            }
            callback(bloggerId);
            };
            x.onerror = function () {
            errorCallback('Network error.');
            };
            x.send();
        }
    </script>


        <?php
    }

    /**
     * Register and add settings
     */
    public function page_init()
    {        
        register_setting(
            'woomio_blogger_option_group', // Option group
            'woomio_blogger_option_name' // Option name
             //array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'setting_section_id', // ID
            '', // Title
            array( $this, 'print_section_info' ), // Callback
            'woomio-blogger-setting-admin' // Page
        );  

         add_settings_field(
            'woomio_convertlink_checkbox', 
            'Enable auto affiliate link conversion', 
            array( $this, 'woomio_convertlink_checkbox_callback' ), 
            'woomio-blogger-setting-admin', 
            'setting_section_id'
        );      

        add_settings_field(
            'woomio_blogger_id', // ID
            '', // Title 
            array( $this, 'woomio_blogger_id_callback' ), // Callback
            'woomio-blogger-setting-admin', // Page
            'setting_section_id' // Section           
        );      

       
    }

  
    /** 
     * Print the Section text
     */
    public function print_section_info()
    {
       // print 'Please Login to woomio with your facebook account';
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function woomio_blogger_id_callback()
    {
        printf(
            '<input type="hidden" id="woomio_blogger_id" name="woomio_blogger_option_name[woomio_blogger_id]" value=%s />',
            isset( $this->options['woomio_blogger_id'] ) ? $this->options['woomio_blogger_id'] : ''
        );
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function woomio_convertlink_checkbox_callback()
    {
        printf(
            '<input type="checkbox" id="woomio_convertlink_checkbox" name="woomio_blogger_option_name[woomio_convertlink_checkbox]" %s />',
            isset( $this->options['woomio_convertlink_checkbox'] ) ? (esc_attr( $this->options['woomio_convertlink_checkbox'])=='on' ? 'checked' : '' ) : ''
        );
    }

}
