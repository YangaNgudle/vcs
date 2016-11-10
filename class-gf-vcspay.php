<?php
// Cleaned up version
GFForms::include_payment_addon_framework();

class GFVCSPay extends GFPaymentAddOn {
  //--> define variables for this plugin

  protected $_supports_callbacks = false;
  protected $_requires_credit_card = true;
  protected $_version = '1.0.0';
  protected $_min_gravityforms_version = "1.9.3";
  protected $_slug = 'gravityformsvcspay';
  protected $_path = 'gravityformsvcspay/vcspay.php';
  protected $_title = 'Gravity Forms VCS Pay Add-On';
  protected $_short_title = 'VCS Pay';
  protected $dev_url = 'https://www.vcs.co.za/vvonline/vcspay.aspx';
  protected $prod_url = '';
  private static $_instance = null;

  // end //

  //--> Allow integration for other devs

  public static function get_instance() {
    if (self::$_instance == null) {
      self::$_instance = new GFVCSPay();
    }

    return self::$_instance;
  }

  // end //

  //--> Set up our Feed Settings in wp-admin

  public function feed_settings_fields() {
    $settings = parent::feed_settings_fields();

    //--> Add new settings to Admin Section

    $new_settings = array(
      array(
        'title' => esc_html__('VCS Form Settings', 'vcsaddon'),
        'fields' => array(

          array(
            'label' => esc_html__('VCS Terminal Id', 'vcsaddon'),
            'type' => 'text',
            'name' => 'vscaddon_terminal_id',
            'tooltip' => esc_html__('Terminal ID supplied by VCS Payments', 'vcsaddon'),
          ),

          array(
            'label' => esc_html__('MD5 Hash Key', 'vcsaddon'),
            'type' => 'text',
            'name' => 'vscaddon_md5_hash_key',
            'tooltip' => esc_html__('MD5 Hash Validated by VCS Payments', 'vcsaddon')
          ),

          array(
            'label' => esc_html__('Success Page URL', 'vcsaddon'),
            'type' => 'text',
            'name' => 'vscaddon_success_url',
            'tooltip' => esc_html__('MD5 Hash Validated by VCS Payments', 'vcsaddon')
          ),

          array(
            'label' => esc_html__('Error Page URL', 'vcsaddon'),
            'type' => 'text',
            'name' => 'vscaddon_error_url',
            'tooltip' => esc_html__('MD5 Hash Validated by VCS Payments', 'vcsaddon')
          ),

          array(
            'label' => esc_html__('Fall Back Urls', 'vcsaddon'),
            'type' => 'radio',
            'choices' => array(
               array(
                'id' => 'vscaddon_use_callback_yes',
                'label' => __('Yes, Use Callback Urls', 'vcsaddon'),
                'value' => 'yes'
               ),
               array(
                'id' => 'vscaddon_use_callback_no',
                'label' => __('No, Don\'t Use Callback Urls', 'vcsaddon'),
                'value' => 'no'
               )
             ),
            'name' => 'vscaddon_use_urls',
            'tooltip' => esc_html__('MD5 Hash Validated by VCS Payments', 'vcsaddon')
          ),

          array(
            'name' => 'mode',
            'label' => __( 'Mode', 'gravityformspayfast' ),
            'type' => 'radio',
            'choices' => array(
              array(
                'id' => 'vscaddon_mode_prod',
                'label' => __('Production', 'vcsaddon'),
                'value' => 'production'
              ),
              array(
                'id' => 'vscaddon_mode_dev',
                'label' => __('Development', 'vcsaddon'),
                'value' => 'development'
              ),
            ),
          ),

        ),
      ),
    );

    // end //

    $settings = parent::add_field_after(
      'feedName',
      $new_settings,
      $settings
    );

    //--> remove unused fields
    $fields_to_remove = [
      "recurringTimes",
      "billingCycle",
      "trial",
      "recurringAmount"
    ];

    foreach ($fields_to_remove as $field) {
      $settings = $this->remove_field($field, $settings);
    }
    // end //
    return $new_settings;

   // return apply_filters( 'gform_vcs_feed_settings_fields', $new_settings );
  }
  // end //

  //--> Form submission, send payment to VCS

  public function redirect_url($feed, $submission_data, $form, $entry) {  
    //--> base redirect URL on environment set in feed
    $url = $feed['meta']['mode'] === 'production'
      ? $this->prod_url
      : $this->dev_url
    ;
    // end //

    // Redirects user to VCS payment gateway
     $url = "https://www.vcs.co.za/vvonline/vcspay.aspx";
     $p1 =  $feed['meta']['vscaddon_terminal_id'];
     $p2 = rand(); // Generate a random number for now
     $p3 = rgar($entry, '21');
     $p4 = GFCommon::get_order_total($form, $entry);
     $secret = $feed['meta']['vscaddon_md5_hash_key'];
     $hash = md5($p1.$p2.$p3.$p4.$secret);
     
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
     echo $response['body'];  // This send the user to either success page or error page these values are set in Terminal  

  }

  // end //

   private function __clone()
   {
        /* do nothing */
   }

  

  

  

  
    










  

  






}