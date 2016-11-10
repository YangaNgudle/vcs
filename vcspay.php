<?php
/*
 Plugin Name: Gravity Forms VCS Add-On
 Plugin URI:
 Description: Integrates Gravity Forms with VCS
 Version: 1.1
 Author: B Online

*/

define( 'GF_VCS_VERSION', '1.1' );

add_action( 'gform_loaded', array( 'GF_VCS_Bootstrap', 'load' ), 5 );

 class GF_VCS_Bootstrap {

     public static function load() {

        if( !method_exists('GFForms', 'include_payment_addon_framework') )
        {
           return;
        }

        require_once('class-gf-vcspay.php');
        GFAddOn::register( 'GFVCSPay' );
     }
 }

 function gf_vcs()
 {
    return GFVCSPay::get_instance();
 }

 add_action('gform_after_submission', 'send_data_to_vcs', 10, 2);

  function send_data_to_vcs($entry, $form)
  {
    // Get data from the feed
    global $wpdb;
    $meta = $wpdb->get_var("SELECT meta FROM wp_gf_addon_feed ORDER BY id DESC LIMIT 1");
     
     $meta = json_decode($meta, true);

     var_dump($meta);
     exit();
 
    // Get Product from the Form
    $keys = array_keys($entry);
    $to_match = '/21/'; // This value should come from the settings field

    $product = [];
    $product_description = "";

    for ($i=0; $i < count($keys); $i++) { 
       # code...
      preg_match($to_match, $keys[$i], $tempArr);
      if (count($tempArr) > 0) {
        $product[] = $keys[$i];
      }
    }


    for ($x=0; $x < count($product); $x++) { 
       # code...
      if (!empty($product[$x])) {
        $product_description .= rgar($entry, $product[$x]) . " ";
      }
    }

    if ($product_description === "") {
      $product_description = "N/A";
    }

  
     // 
     $url = "https://www.vcs.co.za/vvonline/vcspay.aspx";
     $p1 =  $meta['vscaddon_terminal_id']; //1941; // Terminal Id -  $feed['meta']['vscaddon_terminal_id']
     $p2 = rand(); // Generate a random number for now
     $p3 =  $product_description; // Description of Goods
     $p4 = GFCommon::get_order_total($form, $entry); // Transactional Amount
     $secret = $meta['vscaddon_md5_hash_key']; // Private Key
     $hash = md5($p1.$p2.$p3.$p4.$secret); // Hash
     $error_url = $meta['vscaddon_error_url'];
     $success_url = $meta['"vscaddon_success_url'];

     $vcs_args = array(
       'method' => 'POST',
       'timeout' => 45,
       'redirection' => 5,
       'httpversion' => '1.0',
       'blocking' => true,
       'headers' => array(),
       'body' => array( 'p1' => $p1, 
                        'p2' => $p2, 
                        'p3' => $p3, 
                        'p4' => $p4, 
                        'hash' => $hash
        ),
       'cookies' => array()
     );

     $response = wp_remote_post($url, $vcs_args);     
    
     // On success response['body'] redirects user to payment gateway
     // On fail response['body'] redirects user to Decline page set in the terminal

     echo $response['body'];

     // Catch url returned if payOrder.aspx then success

     // check if the HTML in the response contains a redirect to the error URL 
       // If yes and error occurred
      // Else move on to the payment gateway
    //  $html_form_response = htmlspecialchars($response['body']);
     
    // if ( $error_url !== "" ) { 
    //   if (strpos($html_form_response, $error_url) )
    //   {
        
    //     echo "Has Error";
    //     exit();
    //   }

    //  }else {
    //     echo "No Error";
    //     exit();
    //  }


     
     


  }

