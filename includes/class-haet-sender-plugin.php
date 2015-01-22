<?php
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
require HAET_MAIL_PATH . 'includes/class-haet-sender-plugin-ninja-forms.php';
require HAET_MAIL_PATH . 'includes/class-haet-sender-plugin-wp-e-commerce.php';

class Haet_Different_Plugin_Exception extends Exception {}

/**
*   detect the origin of an email
*
**/
class Haet_Sender_Plugin {
    protected $active_plugins;   
    protected $current_plugin;
    protected $mail;
    public static $plugins = array(
        'ninja-forms'   =>  array(
            'name'      =>  'ninja-forms',
            'file'      =>  'ninja-forms/ninja-forms.php',
            'class'     =>  'Haet_Sender_Plugin_Ninja_forms',
            'display_name' => 'Ninja Forms'
        ),
        'wp-e-commerce'   =>  array(
            'name'      =>  'wp-e-commerce',
            'file'      =>  'wp-e-commerce/wp-shopping-cart.php',
            'class'     =>  'Haet_Sender_Plugin_WP_E_Commerce',
            'display_name' => 'WP eCommerce'
        ),
    );

    public static function detect_plugin($mail){
        $active_plugins = Haet_Sender_Plugin::get_active_plugins();
        foreach ($active_plugins as $plugin) {
            try {
                $sender_plugin = new $plugin['class']($mail);
                $sender_plugin->current_plugin = $plugin;
                $sender_plugin->mail = $mail;
                $sender_plugin->activate_plugins = $active_plugins;

                return $sender_plugin;
            } catch (Haet_Different_Plugin_Exception $e) {

            }
        }
        return null;
    }


    /**
    *   use_template()
    *   return true if the mail template should be used for the current mail
    **/
    public function use_template(){
        $plugin_options = $this->get_plugin_options();
        if(array_key_exists($this->current_plugin['name'], $plugin_options))
            return $plugin_options[ $this->current_plugin['name'] ]['template'];
        else
            return true;
    }

    /**
    *   use_sender()
    *   return true if the sender should be overwritten for the current mail
    **/
    public function use_sender(){
        $plugin_options = $this->get_plugin_options();
        if(array_key_exists($this->current_plugin['name'], $plugin_options))
            return $plugin_options[ $this->current_plugin['name'] ]['sender'];
        else
            return true;
    }

    public function get_plugin_name(){
        return $this->current_plugin['name'];
    }

    /**
    *   get_active_plugins
    *   Check all available plugin detectors and return an array of installed plugins
    **/
    public static function get_active_plugins() {
        $active_plugins = array();
        foreach (Haet_Sender_Plugin::$plugins as $plugin_name => $plugin) {
            if(is_plugin_active($plugin['file']) || $plugin['file']=='')
                $active_plugins[$plugin_name] = $plugin;
        }
        return $active_plugins;
    }

    public static function get_plugin_options() {
        $options = array();
        foreach (Haet_Sender_Plugin::$plugins as $plugin_name => $plugin) {
            $options[$plugin_name] = array('template'=>true,'sender'=>true);
        }         
        $haet_mail_options = get_option('haet_mail_plugin_options');
        if (!empty($haet_mail_options)) {
            foreach ($haet_mail_options as $key => $option)
                $options[$key] = $option;
        }               
        update_option('haet_mail_plugin_options', $options);
        return $options;
    }

    public static function save_plugin_options($saved_options) {
        $new_options = $_POST['haet_mail_plugins'];
        $options = array_merge($saved_options,$new_options);

        update_option('haet_mail_plugin_options', $options);
        return $options;
    }
    

    /**
    *   modify_content()
    *   mofify the email content before applying the template
    **/
    public function modify_content($content){
        return $content;
    }

    /**
    *   modify_template()
    *   mofify the email template before the content is added
    **/
    public function modify_template($template){
        return $template;
    }    

    /**
    *   modify_styled_mail()
    *   mofify the email body after the content has been added to the template
    **/
    public function modify_styled_mail($message){
        return $message;
    }    
}