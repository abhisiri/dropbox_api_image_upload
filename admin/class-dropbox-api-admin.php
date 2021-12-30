<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       cedcommerce.com
 * @since      1.0.0
 *
 * @package    Dropbox_Api
 * @subpackage Dropbox_Api/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Dropbox_Api
 * @subpackage Dropbox_Api/admin
 * @author     Abhishek shukla <abhishekshukla2021dec@cedcoss.com>
 */
class Dropbox_Api_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Dropbox_Api_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Dropbox_Api_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/dropbox-api-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Dropbox_Api_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Dropbox_Api_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/dropbox-api-admin.js', array( 'jquery' ), $this->version, false );

	}

}

function add_custom_meta_box_for_image()
{
    add_meta_box("demo-meta-box", "Image Upload", "custom_meta_box_for_image", "post", "side", "high", null);
}

add_action("add_meta_boxes", "add_custom_meta_box_for_image");

function custom_meta_box_for_image($object)
{
    wp_nonce_field(basename(__FILE__), "meta-box-nonce");
?>
    <!-- <form action="#" method="post" enctype="multipart/form-data"> -->
        <label for="meta_box_image">Image</label>
        <input type="file" name="fileUpload">
		<input type="submit" class="button button-primary" value="Upload Image"  id="upload_btn" name="upload_btn" /> 
  	<!-- </form> -->

	  <?php $image_urls = get_post_meta(get_the_ID(), 'dropbox_img_url', 1);
	  if(!empty($image_urls)){
         foreach($image_urls as $key => $val)
           {
	         $imageUrl = str_replace("dl=0", "dl=1", $val); ?>
	  
	        <img src="<?php echo $imageUrl ?>" alt="" width="80px" height="80px">

<?php
}
	  } 
?>
<br>
<input type="checkbox" name='image_feature' id='image_feature' > First Image As Featured Image
<?php 
}

if(isset($_POST['image_feature'])){
	update_post_meta(get_the_ID(), 'image_feature', 'yes');
		} else {
			update_post_meta(get_the_ID(), 'image_feature', 'no');
		}

add_action( 'post_edit_form_tag' , 'post_edit_form_tag' );

function post_edit_form_tag( ) {
   echo ' enctype="multipart/form-data"';
}

function save_image()
{

      if(isset($_POST['upload_btn'])){
        
		$post_id = get_the_ID(); 
	    $path = '/text-abhi/' . $_FILES['fileUpload']['name'];
		$acessToken = get_option('access_token', 1);
		$fp = fopen($_FILES['fileUpload']['tmp_name'], 'rb');
		$size = filesize($_FILES['fileUpload']['tmp_name']);
		$contentType = 'Content-Type:  application/octet-stream';
		$args = 'Dropbox-API-Arg: {"path":"' . $path . '", "mode":"add","autorename": true,"mute": false,"strict_conflict": false}';
		 	$headers = array(
			'Authorization: Bearer ' . $acessToken,
			$contentType, $args
		);
		$ch = curl_init('https://content.dropboxapi.com/2/files/upload');
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_PUT, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($ch, CURLOPT_INFILE, $fp);
		curl_setopt($ch, CURLOPT_INFILESIZE, $size);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);
		$error = curl_error($ch);
		if ($response) {
			$contentType = 'Content-Type:  application/json';
			$headers = array(
				'Authorization: Bearer ' . $acessToken,
				$contentType
			);
			$data = '{"path":"' . $path . '"}';
			$ch = curl_init('https://api.dropboxapi.com/2/sharing/create_shared_link_with_settings');
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$response = curl_exec($ch);
			if ($response) {
				$data = json_decode($response, true);
				$current_value = get_post_meta($post_id, 'dropbox_img_url', 1);
				$url = $data['url'];
				if (empty($current_value) || !isset($current_value)) {
					$current_value = array($url);
			add_post_meta($post_id, 'dropbox_img_url', $current_value, 1);
		} else {
			if (in_array($url, $current_value)) {
				_e("Image Already Exist");
			} else {
				if (!empty($current_value)) {
					$current_value[] = $url;
				} else {
					$current_value = array($url);
				}
				update_post_meta($post_id, 'dropbox_img_url', $current_value);
			}
		}

			}

		}
		
	}
}
add_action("save_post", "save_image", 10, 3);

add_action('admin_menu', 'dropbox_api_plugin_setup_menu');
 
function dropbox_api_plugin_setup_menu(){
    add_menu_page( 'Dropbox Api', 'Dropbox Api', 'manage_options', 'Dropbox-Api', 'dropbox_api_form', 'dashicons-format-aside' );
	// add_submenu_page( 'Dropbox-Api', 'Add New', 'Add New', 'manage_options', 'Add-New', 'dropbox_api_form' );
}


function dropbox_api_form() {
	$dropbox_api = '
	<h2>Dropbox Api</h2>
	<form action="" method="post" enctype="multipart/form-data">	
	app key
	<input type="text" name="api_key" value="" size="40" />
	secret key 
	<input type="text" name="secret_key" value="" size="40" /> 
	<input type="submit" id="generate_token" name="generate_token" value="Generate">
    <input type="submit" id="save_api_token" name="save_api_token" value="Save">
	
	</form>';

	echo $dropbox_api;
}

if(isset($_POST['generate_token'])){

    $api_key = isset ($_POST['api_key']) ?$_POST['api_key'] :'' ;
	$api_secret= isset ($_POST['secret_key']) ?$_POST['secret_key'] :'' ;
	update_option('ced_dropbox_api_key',  $api_key );
	update_option('ced_dropbox_api_secret',  $api_secret );
	$redirect_uri = "http://localhost/wordpress/wp-admin/admin.php?page=Dropbox-Api";
	$url = "https://www.dropbox.com/oauth2/authorize?client_id=".$api_key."&redirect_uri="
		  .$redirect_uri."&response_type=code";
		  header('Location: ' . $url, true);

}


if(isset($_POST['save_api_token'])){

	    $code = $_GET['code'];
        $api_key = get_option('ced_dropbox_api_key');
        $api_secret= get_option('ced_dropbox_api_secret');
        $auth= base64_encode($api_key.':'.$api_secret);
        $header[]="Authorization: Basic $auth";
        $curl=curl_init();
        $link="https://api.dropboxapi.com/oauth2/token?code=".$code."&grant_type=authorization_code&redirect_uri=http://localhost/wordpress/wp-admin/admin.php?page=Dropbox-Api";
        curl_setopt($curl, CURLOPT_URL, $link);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl,CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($curl);
	
        $err=curl_error($curl);
        echo $err;
        if($result){
            $decodedResult=json_decode($result,true);
            update_option('access_token',$decodedResult['access_token'],'yes');
            update_option('api_response',json_encode($decodedResult),'yes');
            echo '<div class="notice is-dismissible notice-success">
            <p>API Credentials Saved Successfully</p>
        </div>';
        }

}




