<?php
/*
Plugin Name: WP Form Encryption
Description: jQuery plugin for encrypting form data in javascript using jCryption library
Version: 1.0
Author: Subhransu Sekhar, binaya.topno
*/

define( 'JCRYPTION__PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'JCRYPTION__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

add_action( 'admin_init', 'jcryptionSettings' );
function jcryptionSettings() {
 	add_settings_field('jcryption_encrypted_forms', 'Form to Encrypt (Comma Separated Form IDs) ', 'jcryptionFormHandler', 'general');
 	register_setting('general','jcryption_encrypted_forms');
}

function jcryptionFormHandler() {
	echo '<textarea name="jcryption_encrypted_forms" id="jcryption_encrypted_forms">' . get_option('jcryption_encrypted_forms') . '</textarea>';
}

add_action( 'login_enqueue_scripts', 'jcryption_scripts_method_login' );

function jcryption_scripts_method_login() {
	wp_register_script(
		'jcryption-lib-script',
    plugins_url( 'js/jquery.jcryption.js' , __FILE__ ),
    false,
    '1.0',
    true
	);
  
  wp_enqueue_script(
		'jcryption-form-script',
		plugins_url( 'js/script.js' , __FILE__ ),
		false,
    '1.0',
    true
	);
	
	wp_enqueue_script('jquery');
	wp_enqueue_script('jcryption-lib-script');
	wp_enqueue_script('jcryption-form-script');
}

add_action( 'init', 'jcryption_init');

function jcryption_init() {
	if(!session_id()) {
        session_start();
    }
  wp_register_script(
		'jcryption-lib-script',
    plugins_url( 'js/jquery.jcryption.js' , __FILE__ ),
    false,
    '1.0',
    true
	);
  
  wp_enqueue_script(
		'jcryption-form-script',
		plugins_url( 'js/script.js' , __FILE__ ),
		false,
	'1.0',
	true
	);
	
	wp_enqueue_script('jcryption-lib-script');
	wp_enqueue_script('jcryption-form-script');
	$encrypted_forms = get_option('jcryption_encrypted_forms');
	if($encrypted_forms) {
		$forms = explode(',', $encrypted_forms);
		wp_localize_script('jcryption-form-script','forms',$forms);
	}
	

	if(!empty($_POST['jCryption'])) {
		
		$test_es_token = md5($_SESSION["es_e"]["hex"]);
		
		if ($_SESSION["es_token"] != $test_es_token) {
      //print "Invalid encryption token. Rejecting submission." . $_SESSION["es_token"]  ."/".$test_es_token;
      //return; 
    }  
  
    // If we made it here, then everything must be okay with the submission, and we can
    // proceed.
    require_once(JCRYPTION__PLUGIN_DIR . "jcryption-lib.php");
    $jCryption = new jCryption();
    $var = $jCryption->decrypt($_POST['jCryption'], $_SESSION["es_d"]["int"], $_SESSION["es_n"]["int"]);
    
    parse_str($var,$result);
    
    $_POST = $result;
	session_destroy();
	}
	
	if(!empty($_GET['generate']) && $_GET['generate'] == 'jcryption') {
		  require_once(JCRYPTION__PLUGIN_DIR . "jcryption-lib.php");
		  
		  $keyLength = 256;  // If this is set too high, then key generation can take a long time.
		                     // 256 bit should be plenty for the target users of this module.  If you
		                     // really need anything more than that, perhaps you should invest
		                     // in an SSL cert after all!
		  $jCryption = new jCryption();
		  
		  $keys = $jCryption->generateKeypair($keyLength);
			$_SESSION["es_e"] = array("int" => $keys["e"], "hex" => $jCryption->dec2string($keys["e"],16));
			$_SESSION["es_d"] = array("int" => $keys["d"], "hex" => $jCryption->dec2string($keys["d"],16));
			$_SESSION["es_n"] = array("int" => $keys["n"], "hex" => $jCryption->dec2string($keys["n"],16));
			
		  // Create a token based on the e value and the site's private key.
		  $_SESSION["es_token"] = md5($_SESSION["es_e"]["hex"]);
		  
			echo '{"e":"' . $_SESSION["es_e"]["hex"] . '","n":"' . 
				$_SESSION["es_n"]["hex"] . '","maxdigits":"' . 
				intval($keyLength*2/16+3) . '"}';
				
			die();
	}
}

