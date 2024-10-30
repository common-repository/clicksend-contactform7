<?php

/**
 * Make sure WPCF7_Service class is available before
 * creating this class.
 */
if (class_exists('WPCF7_Service')) {

    /**
     * Class ClickSend_ContactForm7Service
     *
     * The ClickSend custom Contact Form 7 Service.
     */
    class ClickSend_ContactForm7Service extends WPCF7_Service
    {
        // Variables.
        private static $instance;
        private $api_credentials;
        private $notify_msg = 'Admin - {{_name}} {{_id}} {{your-message}} {{datey}}';
        private $api_url = 'https://rest.clicksend.com/v3/sms/send';

        /**
         * ClickSend_ContactForm7Service constructor.
         */
        public function __construct()
        {
            $this->api_credentials = WPCF7::get_option('clicksend');
        }

        /**
         * Get instance.
         *
         * @return ClickSend_ContactForm7Service
         */
        public static function get_instance()
        {
            if (empty(self::$instance)) {
                self::$instance = new self;
            }

            return self::$instance;
        }

        /**
         * Get title.
         *
         * @return mixed
         */
        public function get_title()
        {
            return __('ClickSend SMS', 'clicksend-contactform7');
        }

        /**
         * Check if service is active or not.
         *
         * @return bool
         */
        public function is_active()
        {

            if ( ! empty($this->api_credentials) AND ! is_null($this->api_credentials)) {

                $username = $this->get_username();
                $api_key  = $this->get_api_key();

                if ($username AND $api_key) {
                    return true;
                }

            }

            return false;
        }

        /**
         * Get categories.
         *
         * @return array
         */
        public function get_categories()
        {
            return array('sms');
        }

        /**
         * Get icon.
         *
         * @return string
         */
        public function icon()
        {
            return '';
        }

        /**
         * Display link.
         *
         */
        public function link()
        {
            echo sprintf('<a href="%1$s">%2$s</a>',
                'https://clicksend.com/help',
                'clicksend.com/help');
        }

        /**
         * Get current menu page url.
         *
         * @param string $args
         *
         * @return mixed
         */
        private function menu_page_url($args = '')
        {
            $args = wp_parse_args($args, array());

            $url = menu_page_url('wpcf7-integration', false);
            $url = add_query_arg(array('service' => 'clicksend'), $url);

            if ( ! empty($args)) {
                $url = add_query_arg($args, $url);
            }

            return $url;
        }

        /**
         * Get username.
         *
         * @return string
         */
        public function get_username()
        {
            if ($this->api_credentials) {

                $credentials = json_decode($this->api_credentials);

                return isset($credentials->username) ? $credentials->username : '';

            }

            return '';
        }

        /**
         * Get API key.
         *
         * @return string
         */
        public function get_api_key()
        {
            if ($this->api_credentials) {

                $credentials = json_decode($this->api_credentials);

                return isset($credentials->api_key) ? $credentials->api_key : '';

            }

            return '';
        }

        /**
         * Check if notification is enabled or not.
         *
         * @return string
         */
        public function is_notification_enabled()
        {
            if ($this->api_credentials) {

                $credentials = json_decode($this->api_credentials);

                return (isset($credentials->enable_notification) AND $credentials->enable_notification === true) ? true : false;

            }

            return false;
        }

        /**
         * Check if notification is enabled or not.
         *
         * @return string
         */
        public function is_notification_sender_enabled()
        {
            if ($this->api_credentials) {

                $credentials = json_decode($this->api_credentials);

                return (isset($credentials->enable_notification_sender) AND $credentials->enable_notification_sender === true) ? true : false;

            }

            return false;
        }

        /**
         * Get notification message.
         *
         * @return string
         */
        public function get_notify_msg()
        {
            if ($this->api_credentials) {

                $credentials = json_decode($this->api_credentials);

                return isset($credentials->notify_msg) ? $credentials->notify_msg : $this->notify_msg;

            }

            return $this->notify_msg;
        }
        

        /**
         * Get mobile_no.
         *
         * @return string
         */
        public function get_mobile_no()
        {
            if ($this->api_credentials) {

                $credentials = json_decode($this->api_credentials);

                return isset($credentials->mobile_no) ? $credentials->mobile_no : '';

            }

            return '';
        }

        /**
         * Get mobile_no sender key.
         *
         * @return string
         */
        public function get_mobile_no_sender_key()
        {
            return 'customer-mobile';
        }

        /**
         * Get sender id.
         *
         * @return string
         */
        public function get_sender_id()
        {

            if ($this->api_credentials) {

                $credentials = json_decode($this->api_credentials);

                return isset($credentials->sender_id) ? $credentials->sender_id : '';

            }

            return '';
        }

        /**
         * Get sender id for sender.
         *
         * @return string
         */
        public function get_sender_id_for_sender()
        {

            if ($this->api_credentials) {

                $credentials = json_decode($this->api_credentials);

                return isset($credentials->sender_id_for_sender) ? $credentials->sender_id_for_sender : '';

            }

            return '';
        }

        /**
         * Custom function to authenticate clicksend credentials
         *
         * @param string $action
         */
        
        public function send_clicksend_api_request($username,$api_key) {
            $headers = array(
            'Authorization' => 'Basic '.base64_encode($username.':'.$api_key),
            'Content-Type' => 'application/json',
            );   
            $api_url = 'https://rest.clicksend.com/v3/lists/2066428/contacts';
            $response = wp_safe_remote_request($api_url, array(
            'method' => 'POST', 
            'headers' => $headers,
            ));
            if (is_wp_error($response)) {
                echo 'API request error: ' . $response->get_error_codes();    
            } else {
                $body = wp_remote_retrieve_body($response);
                $data = json_decode($body, true);     
                return $data['http_code'];
            }
        }



        /**
         * Load or process submitted service settings.
         *
         * @param string $action
         */
        public function load($action = '')
        {
            if ('setup' == $action) {

                // When service credentials has been submitted or updated.
                if ('POST' == $_SERVER['REQUEST_METHOD']) {

                    check_admin_referer('wpcf7-clicksend-setup');

                    $username                   = $this->get_post_value('username');
                    $api_key                    = $this->get_post_value('api_key');
                    $mobile_no                  = $this->get_post_value('mobile_no','string');
                    $enable_notification        = $this->get_post_value('enable_notification', 'boolean', false);
                    $enable_notification_sender = $this->get_post_value('enable_notification_sender', 'boolean', false);
                    $notify_msg                 = $this->get_post_value('notify_msg', 'message', $this->notify_msg);
                    $notify_sender_msg          = $this->get_post_value('notify_sender_msg', 'message', $this->notify_sender_msg);
                    $sender_id                  = $this->get_post_value('sender_id');
                    $sender_id_for_sender       = $this->get_post_value('sender_id_for_sender');

                    if ($username AND $api_key) {
                         WPCF7::update_option('clicksend', json_encode(array(
                            'username'                   => $username,
                            'api_key'                    => $api_key,
                            'mobile_no'                  => $mobile_no,
                            'enable_notification'        => $enable_notification,
                            'enable_notification_sender' => $enable_notification_sender,
                            'notify_msg'                 => $notify_msg,
                            'notify_sender_msg'          => $notify_sender_msg,
                            'sender_id'                  => $sender_id,
                            'sender_id_for_sender'       => $sender_id_for_sender,
                        )));    
                        $redirect_to = $this->menu_page_url(array(
                            'message' => 'success',
                        ));
                        //Custom code to authenticate clicksend credentials
                        $authenticate = $this->send_clicksend_api_request($username,$api_key);
                        if($authenticate == 401){
                            $redirect_to = $this->menu_page_url(array(
                                'action'  => 'setup',
                                'message' => 'invalid_api_key',));
                        }
                    } elseif ($username === '' AND $api_key === '') {
                        WPCF7::update_option('clicksend', null);
                        $redirect_to = $this->menu_page_url(array(
                            'message' => 'success',
                        ));

                    } else {
                        $redirect_to = $this->menu_page_url(array(
                            'action'  => 'setup',
                            'message' => 'invalid',
                        ));
                    }
                    wp_safe_redirect($redirect_to);
                    exit();
                }

            }
        }

        /**
         * Notification messages.
         *
         * @param string $message
         */
        public function admin_notice($message = '')
        {
            if ('invalid' == $message) {
                echo sprintf(
                    '<div class="error notice notice-error is-dismissible"><p><strong>%1$s</strong>: %2$s</p></div>',
                    esc_html(__("ERROR", 'clicksend-contactform7')),
                    esc_html(__("Invalid key values.", 'clicksend-contactform7')));
            }

            if ('success' == $message) {
                echo sprintf('<div class="updated notice notice-success is-dismissible"><p>%s</p></div>',
                    esc_html(__('Settings saved.', 'clicksend-contactform7')));
            }
            if ('invalid_api_key' == $message) {
                echo sprintf(
                    '<div class="error notice notice-error is-dismissible"><p><strong>%1$s</strong>: %2$s</p></div>',
                    esc_html(__("ERROR", 'clicksend-contactform7')),
                    esc_html(__("Invalid username or API Key", 'clicksend-contactform7')));
            }
            if ('invalid_phone' == $message) {
                echo sprintf(
                    '<div class="error notice notice-error is-dismissible"><p><strong>%1$s</strong>: %2$s</p></div>',
                    esc_html(__("ERROR", 'clicksend-contactform7')),
                    esc_html(__("Invalid mobile number", 'clicksend-contactform7')));
            }
            if ('invalid_message' == $message) {
                echo sprintf(
                    '<div class="error notice notice-error is-dismissible"><p><strong>%1$s</strong>: %2$s</p></div>',
                    esc_html(__("ERROR", 'clicksend-contactform7')),
                    esc_html(__("Invalid message format", 'clicksend-contactform7')));
            }
        }

        /**
         * Display service content.
         *
         * @param string $action
         */
        public function display($action = '')
        {
            ?>
            <p><?php echo esc_html(__("Fill out below to send SMS to customers or the form admin each time this form is submitted.
", 'clicksend-contactform7')); ?></p>

            <?php
            if ('setup' == $action) {
                $this->display_setup();

                return;
            }
            if (class_exists('clicksend_submenu_page')) {
                $clicksend_custom_messages = new clicksend_submenu_page();
                $settings_notes = $clicksend_custom_messages->settings_page_note();
                
            }
            if ($this->is_active()) {

                $username                   = $this->get_username();
                $api_key                    = $this->get_api_key();
                $mobile_no                  = $this->get_mobile_no();
                $enable_notification        = $this->is_notification_enabled();
                $enable_notification_sender = $this->is_notification_sender_enabled();
                $notify_msg                 = $this->get_notify_msg();
                $notify_sender_msg          = $this->get_notify_sender_msg();

                ?>
                <fieldset style="border: 1px solid #ccc; padding: 20px;margin-bottom: 20px;">
                    <legend>Your ClickSend details</legend>
                    <table class="form-table">
                        <tbody>
                        <tr>
                            <th scope="row"><?php echo esc_html(__('ClickSend Username', 'clicksend-contactform7')); ?></th>
                            <td class="code"><?php echo esc_html($username); ?></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php echo esc_html(__('ClickSend API Key', 'clicksend-contactform7')); ?></th>
                            <td class="code"><?php echo esc_html(wpcf7_mask_password($api_key)); ?></td>
                        </tr>
                        </tbody>
                    </table>
                    <p><em>Find your ClickSend credentials <a href="https://dashboard.clicksend.com/account/subaccounts?utm_source=integration&utm_medium=referral&utm_campaign=contactform7" target="_blank">here</a>.<br>Don't have a ClickSend Account? Create one <a href="https://dashboard.clicksend.com/signup/?utm_source=integration&utm_medium=referral&utm_campaign=contactform7" target="_blank">here</a>.</em></p>
                    
                </fieldset>

                <fieldset style="border: 1px solid #ccc; padding: 20px;margin-bottom: 20px;">
                    <legend>Message the customer every time the form is completed</legend>
                     <em>Add customer-mobile into the Sender Name/Number field. The message will be sent to the number the customer enters into the form.</em>
                    <table class="form-table">
                        <tbody>
                        <tr>
                            <th scope="row"><?php echo esc_html(__('Enable Message', 'clicksend-contactform7')); ?></th>
                            <td class="code"><?php echo (esc_html($enable_notification_sender)) ? 'Yes' : 'No'; ?></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php echo esc_html(__('Message content', 'clicksend-contactform7')); ?></th>
                            <td class="code"><?php echo esc_html($notify_sender_msg); ?>
                            </td>
                            
                        </tr>
                        <tr><th></th><td><em><?php if(isset($settings_notes))echo $settings_notes;?></em></td></tr>
                        </tbody>
                    </table>
                </fieldset>

                <fieldset style="border: 1px solid #ccc; padding: 20px;margin-bottom: 20px;">
                    <legend>Receive an SMS every time the form is completed</legend>
                    <em>Add the mobile number/s you want the SMS to be sent to in Mobile No. The number/s you add will receive an SMS every time the form is completed.</em> 
                    <table class="form-table">
                        <tbody>
                        <tr>
                            <th scope="row"><?php echo esc_html(__('Send SMS to', 'clicksend-contactform7')); ?></th>
                            <td class="code"><?php echo esc_html($mobile_no); ?></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php echo esc_html(__('Enable Message', 'clicksend-contactform7')); ?></th>
                            <td class="code"><?php echo (esc_html($enable_notification)) ? 'Yes' : 'No'; ?></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php echo esc_html(__('Message content', 'clicksend-contactform7')); ?></th>
                            <td class="code"><?php echo esc_html($notify_msg); ?>
                            </td>
                            
                        </tr>
                        <tr><th></th><td><em><?php if(isset($settings_notes))echo $settings_notes;?></em></td></tr>
                        </tbody>
                    </table>
                </fieldset>

                <p><a href="<?php echo esc_url($this->menu_page_url('action=setup')); ?>" class="button"><?php echo esc_html(__("Change Settings", 'clicksend-contactform7')); ?></a></p>

                <?php
            } else {
                ?>
                <p><?php echo esc_html(__("To use this plugin, you need to add your ClickSend API credentials first.", 'clicksend-contactform7')); ?></p>

                <p><a href="<?php echo esc_url($this->menu_page_url('action=setup')); ?>" class="button"><?php echo esc_html(__("Configure Settings", 'clicksend-contactform7')); ?></a></p>

                <p><?php echo sprintf(esc_html(__("For more details, see %s.", 'clicksend-contactform7')),
                        wpcf7_link(__('https://dashboard.clicksend.com/#/dashboard/home', 'clicksend-contactform7'), __('ClickSend Dashboard', 'clicksend-contactform7'))); ?></p>
                <?php
            }
        }

        /**
         * Display service content, if not yet setup.
         */
        public function display_setup()
        {
            if (class_exists('clicksend_submenu_page')) {
                $clicksend_custom_messages = new clicksend_submenu_page();
                $settings_notes = $clicksend_custom_messages->settings_page_note();
                
            }

            ?>
            <form method="post" action="<?php echo esc_url($this->menu_page_url('action=setup')); ?>" id="clicksend-contactform7">
                <fieldset style="border: 1px solid #ccc; padding: 20px;margin-bottom: 20px;">
                    <legend>Your ClickSend details</legend>
                    <table class="form-table">
                        <tbody>
                        <tr>
                            <th scope="row"><label for="username"><?php echo esc_html(__('ClickSend Username', 'clicksend-contactform7')); ?></label></th>
                            <td><input type="text" aria-required="true" value="<?php echo $this->get_username(); ?>" id="username" name="username" class="regular-text code"
                                       placeholder="Enter your Username"/></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="api_key"><?php echo esc_html(__('ClickSend API Key', 'clicksend-contactform7')); ?></label></th>
                            <td><input type="password" aria-required="true" value="<?php echo $this->get_api_key(); ?>" id="api_key" name="api_key" class="regular-text code"
                                       placeholder="Enter your Api Key"/></td>
                        </tr>
                        </tbody>
                    </table>
                    <p><em>Find your ClickSend credentials <a href="https://dashboard.clicksend.com/account/subaccounts?utm_source=integration&utm_medium=referral&utm_campaign=contactform7" target="_blank">here</a>.<br>Don't have a ClickSend Account? Create one <a href="https://dashboard.clicksend.com/signup/?utm_source=integration&utm_medium=referral&utm_campaign=contactform7" target="_blank">here</a>.</p>
                    <p class="submit"><input type="submit" class="button button-primary" value="<?php echo esc_attr(__('Save Changes', 'clicksend-contactform7')); ?>" name="submit"/></em></p>
                </fieldset>

                <fieldset style="border: 1px solid #ccc; padding: 20px;margin-bottom: 20px;">
                    <legend>Message the customer every time they complete a form</legend>
                    <em>Add customer-mobile into the Sender Name/Number field. The message will be sent to the number the customer enters into the form.</em>
                    
                    <table class="form-table">
                        <tbody>
                        <tr>
                            <th scope="row"><label for="enable_notification_sender"><?php echo esc_html(__('Enable Message', 'clicksend-contactform7')); ?></label></th>
                            <td><input type="checkbox" aria-required="true" value="true" id="enable_notification_sender" name="enable_notification_sender"
                                       class="regular-text code" <?php echo $this->is_notification_sender_enabled() ? 'checked' : ''; ?>/></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="sender_id_for_sender"><?php echo esc_html(__('Sender number/name (optional)', 'clicksend-contactform7')); ?></label></th>
                            <td><input type="text" aria-required="true" value="<?php echo $this->get_sender_id_for_sender(); ?>" id="sender_id_for_sender" name="sender_id_for_sender"
                                       class="regular-text code"
                                       placeholder=""/><br><a href="https://clicksend.helpdocs.io/article/4kgj7krx00-what-is-a-sender-id-or-sender-number?utm_source=integration&utm_medium=referral&utm_campaign=contactform7" target="_blank">More info</a> : <em>Enter the number or name (alpha tag) you’re sending from. If you leave this field blank, we’ll send from a shared number. You can view your numbers in the ClickSend <a href="https://dashboard.clicksend.com/home?utm_source=integration&utm_medium=referral&utm_campaign=contactform7" target="_blank">Dashboard</a>.</em></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="notify_sender_msg"><?php echo esc_html(__('Message content', 'clicksend-contactform7')); ?></label></th>
                            <td>
                            <textarea type="checkbox" aria-required="true" value="" id="notify_sender_msg" name="notify_sender_msg" class="regular-text code"
                                      placeholder="Customer - {{_name}} {{_id}} {{your-message}} {{datey}}"><?php echo $this->get_notify_sender_msg(); ?></textarea>
                                      <p><em><?php if(isset($settings_notes))echo $settings_notes;?> </em><a href="https://help.clicksend.com/article/h474eseq3a-how-many-characters-can-i-send-in-an-sms?utm_source=integration&utm_medium=referral&utm_campaign=contactform7" target="_blank">More info</a></p>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <p class="submit"><input type="submit" class="button button-primary" value="<?php echo esc_attr(__('Save Customer Settings', 'clicksend-contactform7')); ?>" name="submit"/></p>
                </fieldset>

                <fieldset style="border: 1px solid #ccc; padding: 20px;margin-bottom: 20px;">
                    <legend>Receive an SMS every time the form is completed</legend>
                    <em>Add the mobile number/s you want the SMS to be sent to in Mobile No. The number/s you add will receive an SMS every time the form is completed. </em>
                    <?php wp_nonce_field('wpcf7-clicksend-setup'); ?>
                    <table class="form-table">
                        <tbody>
                        <tr>
                            <th scope="row"><label for="enable_notification"><?php echo esc_html(__('Enable Message', 'clicksend-contactform7')); ?></label></th>
                            <td><input type="checkbox" aria-required="true" value="true" id="enable_notification" name="enable_notification"
                                       class="regular-text code" <?php echo $this->is_notification_enabled() ? 'checked' : ''; ?>/></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="mobile_no"><?php echo esc_html(__('Send SMS to', 'clicksend-contactform7')); ?></label></th>
                            <td><input type="text" aria-required="true" value="<?php echo $this->get_mobile_no(); ?>" id="mobile_no" name="mobile_no" class="regular-text code"
                                       placeholder="eg: +639171234567"/></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="sender_id"><?php echo esc_html(__('Sender number/name (optional)', 'clicksend-contactform7')); ?></label></th>
                            <td><input type="text" aria-required="true" value="<?php echo $this->get_sender_id(); ?>" id="sender_id" name="sender_id" class="regular-text code"
                                       placeholder=""/><br><a href="https://clicksend.helpdocs.io/article/4kgj7krx00-what-is-a-sender-id-or-sender-number?utm_source=integration&utm_medium=referral&utm_campaign=contactform7" target="_blank">More info</a><em> : Enter the number or name (alpha tag) you’re sending from. If you leave this field blank, we’ll send from a shared number. You can view your numbers in the ClickSend <a href="https://dashboard.clicksend.com/home?utm_source=integration&utm_medium=referral&utm_campaign=contactform7" target="_blank">Dashboard</a>.</em></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="notify_msg"><?php echo esc_html(__('Message content', 'clicksend-contactform7')); ?></label></th>
                            <td>
                            <textarea type="checkbox" aria-required="true" value="" id="notify_msg" name="notify_msg" class="regular-text code"
                                      placeholder="Admin - {{_name}} {{_id}} {{your-message}} {{datey}}"><?php echo $this->get_notify_msg(); ?></textarea>
                            <p><em><?php if(isset($settings_notes))echo $settings_notes;?> </em><a href="https://help.clicksend.com/article/h474eseq3a-how-many-characters-can-i-send-in-an-sms?utm_source=integration&utm_medium=referral&utm_campaign=contactform7" target="_blank">More info</a></p>
                            </td>
                        </tr>
                        </tbody>
                    </table>

                    <hr/>
                    <p><strong>Placeholders</strong></p>
                    <p>You can use the following as placeholders.</p>
                    <table class="form-table">
                        <tbody>
                        <tr>
                            <td width="150"><em>{{_name}}</em></td>
                            <td>Your form's name.</td>
                        </tr>
                        <tr>
                            <td width="150"><em>{{_id}}</em></td>
                            <td>Your form's unique id.</td>
                        </tr>
                        <tr>
                            <td width="150"><em>{{your-field-name}}</em></td>
                            <td>
                            <p>A custom form field name you set when you created your contact form.</p>
                            <p><em><strong>Example</strong>: [text* my-nickname]</em></p>
                            <p>If you have a contact form field set like so, your placeholder will be <em>{{my-nickname}}</em></p>
                            </td>
                        </tr>
                        </tbody>
                    </table>

                    <p class="submit"><input type="submit" class="button button-primary" value="<?php echo esc_attr(__('Save Admin Settings', 'clicksend-contactform7')); ?>" name="submit"/></p>
                </fieldset>
            </form>
            <?php
        }

        /**
         * Get post values by key.
         *
         * @param        $key
         * @param string $type
         * @param string $default
         *
         * @return bool|null|string
         */
        private function get_post_value($key, $type = 'string', $default = '')
        {
            $val = isset($_POST[$key]) ? $_POST[$key] : null;

            if ($type == 'string') {

                if (is_null($val) || empty($val)) {
                    return $default;
                }
                return sanitize_text_field($val);
                

            } elseif ($type == 'boolean') {
                if (is_null($val) OR empty($val)) {
                    return $this->boolval($default);
                }
                return $this->boolval($val);
            }
            elseif ($type == 'number') {
                if (is_null($val) OR empty($val)) {
                    return $this->boolval($default);
                }
                return $this->validatePhoneNumber($val);
            }
            elseif ($type == 'message'){
                if (is_null($val) || empty($val)) {
                    return $default;
                }
                return $this->custom_strip_data($val);
               
            }

            return $val;
        }
        private function validatePhoneNumber($phoneNumber) {
            $pattern = '/^[0-9 ()+,-]+$/';
            if (preg_match($pattern, $phoneNumber)) {
                return sanitize_text_field($phoneNumber); // Phone number is valid
            } else {    
                return false;
            }
        }
        private function validateMessage($input_string) {
            $pattern = '/^[a-zA-Z0-9:{}_ ,.-]+$/';
            if (preg_match($pattern, $input_string)) {
                return sanitize_text_field($input_string); // Phone number is valid
            } else {    
                return false;
            }
        }
        private function custom_strip_data($inputString){
            $pattern = '/\\\\/';
            $cleanString = preg_replace($pattern, '', $inputString);
            return $cleanString;
        }
        /**
         * Convert value to boolean.
         *
         * @param $val
         *
         * @return bool
         */
        private function boolval($val)
        {
            if (is_string($val)) {

                return $val == 'true' ? true : false;

            } elseif (is_numeric($val)) {

                return $val >= 0 ? true : false;

            } elseif (is_bool($val)) {

                return $val;
            }

            return false;
        }

        /**
         * Prepare notification msg. This is the final msg to be sent.
         *
         * @param                   $notify_msg
         * @param WPCF7_ContactForm $contact_form
         *
         * @return mixed
         */
        public function prepareNotifyMsg($notify_msg, WPCF7_ContactForm $contact_form)
        {
            // Replace private placholders.
            // Usually prefixed by '_'.
            $notify_msg = str_replace('{{_name}}', $contact_form->name(), $notify_msg);
            $notify_msg = str_replace('{{_id}}', $contact_form->id(), $notify_msg);

            // Let's get placholders.
            preg_match_all('/{{(.*?)}}/', $notify_msg, $placeholders);

            $properties = (is_array($placeholders) AND count($placeholders) >= 2) ? $placeholders[1] : [];

            if ( ! $properties OR empty($properties)) {
                return $notify_msg;
            }

            // Replace placholders, if any.
            foreach ($properties as $name) {

                // Get contact form property value.
                $value = isset($_POST[$name]) ? sanitize_text_field($_POST[$name]) : null;

                if ($value == null) {
                    continue;
                }

                $notify_msg = str_replace('{{'.$name.'}}', $value, $notify_msg);

            }

            return $notify_msg;
        }

       public function prepareNotifyMsgDivi($notify_msg,$fields)
        {
            // Replace private placholders.
            // Usually prefixed by '_'.
            preg_match_all('/{{(.*?)}}/', $notify_msg, $placeholders);
            $properties = (is_array($placeholders) AND count($placeholders) >= 2) ? $placeholders[1] : [];
            if ( ! $properties OR empty($properties)) {
                return $notify_msg;
            }
            // Replace placholders, if any.
            foreach ($properties as $name) {
                // Get contact form property value.
                $value = isset($fields[$name]['value']) ? sanitize_text_field($fields[$name]['value']) : null;
                if ($value == null) {
                    continue;
                }

                $notify_msg = str_replace('{{'.$name.'}}', $value, $notify_msg);
            }
            return $notify_msg;
        }

        /**
         * Get notify sender msg.
         *
         * @return string
         */
        public function get_notify_sender_msg_modified(WPCF7_ContactForm $contact_form)
        {
            $default = 'Customer - {{_name}} {{_id}} {{your-message}} {{datey}}';
            if ($this->api_credentials) {
                $credentials = json_decode($this->api_credentials);
                if(!empty($custom_messages = $this->checkCustomMessageTable($contact_form))){
                    $notify_msg = $custom_messages['customer_message'];
                }
                else{
                    $notify_msg = isset($credentials->notify_sender_msg) ? $credentials->notify_sender_msg : $default;
                }
                
                $notify_msg = str_replace('{{_name}}', $contact_form->name(), $notify_msg);
                $notify_msg = str_replace('{{_id}}', $contact_form->id(), $notify_msg);
                // Let's get placholders.
                preg_match_all('/{{(.*?)}}/', $notify_msg, $placeholders);
                $properties = (is_array($placeholders) AND count($placeholders) >= 2) ? $placeholders[1] : [];
                if ( ! $properties OR empty($properties)) {
                    return $notify_msg;
                }
                // Replace placholders, if any.
                foreach ($properties as $name) {
                    // Get contact form property value.
                    $value = isset($_POST[$name]) ? sanitize_text_field($_POST[$name]) : null;
                    if ($value == null) {
                        continue;
                    }
                    $notify_msg = str_replace('{{'.$name.'}}', $value, $notify_msg);
                }
                return $notify_msg;            
            }
            return $default;
        }
        public function get_notify_sender_msg_modified_divi($current_page_id,$fields)
        {
            $default = 'Customer - {{_name}} {{_id}} {{your-message}} {{datey}}';
            if ($this->api_credentials) {
                $credentials = json_decode($this->api_credentials);
                if(!empty($custom_messages = $this->checkCustomMessageTableDivi($current_page_id))){
                    $notify_msg = $custom_messages['customer_message'];
                }
                else{
                    $notify_msg = isset($credentials->notify_sender_msg) ? $credentials->notify_sender_msg : $default;
                }
                preg_match_all('/{{(.*?)}}/', $notify_msg, $placeholders);
                $properties = (is_array($placeholders) AND count($placeholders) >= 2) ? $placeholders[1] : [];
                if ( ! $properties OR empty($properties)) {
                    return $notify_msg;
                }
                // Replace placholders, if any.
                foreach ($properties as $name) {
                    // Get contact form property value.
                    $value = isset($fields[$name]['value']) ? sanitize_text_field($fields[$name]['value']) : null;
                    if ($value == null) {
                        continue;
                    }
                    $notify_msg = str_replace('{{'.$name.'}}', $value, $notify_msg);
                }
                return $notify_msg;            
            }
            return $default;
        }
        public function get_notify_sender_msg()
        {
            $default = 'Customer - {{_name}} {{_id}} {{your-message}} {{datey}}';
            if ($this->api_credentials) {
                $credentials = json_decode($this->api_credentials);
                return isset($credentials->notify_sender_msg) ? $credentials->notify_sender_msg : $default;
            }
            return $default;
        }

        /**
         * Notify sender if mobile no is given.
         *
         * @return mixed
         */
        public function notifySender(WPCF7_ContactForm $contactForm)
        {
            // Get contact form property value.
            $mobileNoKey    = $this->get_mobile_no_sender_key();
            $senderMobileNo = isset($_POST[$mobileNoKey]) ? sanitize_text_field($_POST[$mobileNoKey]) : null;
            // Do nothing if sender mobile no is not given.
            if ( ! $senderMobileNo) {
                return false;
            }
            // Don't send SMS notification if notification flag is disabled.
            if ( ! $this->is_active() OR ! $this->is_notification_sender_enabled()) {
                return false;
            }
            $body = $this->get_notify_sender_msg_modified($contactForm);
            // Send SMS via ClickSend API.
            $http   = new WP_Http();
            $result = $http->request($this->api_url, array(
                'headers' => array(
                    'Content-Type'  => 'application/json',
                    'Authorization' => 'Basic '.base64_encode($this->get_username().':'.$this->get_api_key()),
                ),
                'method'  => 'POST',
                'body'    => json_encode(array(
                    'messages' => array(
                        array(
                            'source'   => 'wp-cf7',
                            'from'     => $this->get_sender_id_for_sender(),
                            'body'     => $body,
                            'to'       => $senderMobileNo,
                            'schedule' => '',
                        ),
                    ),
                )),
            ));
            return $result;
        }

        public function notifySenderDivi($current_page_id,$fields)
        {
            // Get contact form property value.
            $mobileNoKey    = $this->get_mobile_no_sender_key();
            $senderMobileNo = isset($fields[$mobileNoKey]['value']) ? sanitize_text_field($fields[$mobileNoKey]['value']) : null;
            // Do nothing if sender mobile no is not given.
            if ( ! $senderMobileNo) {
                return false;
            }
            // // Don't send SMS notification if notification flag is disabled.
            if ( ! $this->is_active() OR ! $this->is_notification_sender_enabled()) {
                return false;
            }
            $body = $this->get_notify_sender_msg_modified_divi($current_page_id,$fields);
            // Send SMS via ClickSend API.
            $http   = new WP_Http();
            $result = $http->request($this->api_url, array(
                'headers' => array(
                    'Content-Type'  => 'application/json',
                    'Authorization' => 'Basic '.base64_encode($this->get_username().':'.$this->get_api_key()),
                ),
                'method'  => 'POST',
                'body'    => json_encode(array(
                    'messages' => array(
                        array(
                            'source'   => 'wp-cf7',
                            'from'     => $this->get_sender_id_for_sender(),
                            'body'     => $body,
                            'to'       => $senderMobileNo,
                            'schedule' => '',
                        ),
                    ),
                )),
            ));
            return $result;
        }
        /**
         * Custom messages from table.
         *
         * @param WPCF7_ContactForm $contactForm
         *
         * @return bool
         */
        public function checkCustomMessageTableDivi($page_id)
        {
            global $wpdb;
            $response = [];
            $table_name = $wpdb->prefix.'clicksend_messages';
            $results = $wpdb->get_results($wpdb->prepare("SELECT admin_message,customer_message FROM $table_name WHERE page_id=%d AND cf_form_id=0 AND status='enabled' ",$page_id),ARRAY_A);
            if(!empty($results)){
                $response['admin_message'] = $results[0]['admin_message'];
                $response['customer_message'] = $results[0]['customer_message'];
            }
            return $response;
        }
        public function checkCustomMessageTable(WPCF7_ContactForm $contact_form)
        {
            global $wpdb;
            $response = [];
            $cf_form_id = $contact_form->id();
            $referer_url = wp_get_referer();
            $page_id = url_to_postid($referer_url);
            $table_name = $wpdb->prefix.'clicksend_messages';
            $results = $wpdb->get_results($wpdb->prepare("SELECT admin_message,customer_message,cf_form_id FROM $table_name WHERE (cf_form_id=%d OR cf_form_id=%d) AND page_id=%d AND status='enabled' ",0,$cf_form_id,$page_id),ARRAY_A);
            if(!empty($results) && count($results)>1){
                foreach($results as $result){
                    if($result['cf_form_id'] != 0){
                        $response['admin_message'] = $result['admin_message'];
                        $response['customer_message'] = $result['customer_message'];
                    }
                }
            }
            elseif (!empty($results)) {
                $response['admin_message'] = $results[0]['admin_message'];
                $response['customer_message'] = $results[0]['customer_message'];
            }
            return $response;
        }
        /**
         * Notify admin.
         *
         * @param WPCF7_ContactForm $contactForm
         *
         * @return bool
         */
        public function notifyAdmin(WPCF7_ContactForm $contactForm)
        {
            // Get current submission instance.
            $submission = WPCF7_Submission::get_instance();

            // Do nothing if no submission at all.
            // Or message is not sent.
            if ( ! $submission OR ! $submission->is('mail_sent')) {
                return false;
            }

            // Don't send SMS notification if notification flag is disabled.
            if ( ! $this->is_active() OR ! $this->is_notification_enabled()) {
                return false;
            }

            // Prepare message.
            // Replace name placeholders.
           

            if(!empty($custom_messages = $this->checkCustomMessageTable($contactForm))){
                $notify_msg = $custom_messages['admin_message'];
            }
            else{
                $notify_msg = $this->get_notify_msg();
            }
            
            $msg = $this->prepareNotifyMsg($notify_msg, $contactForm);
            $api_url = 'https://rest.clicksend.com/v3/sms/send';
            $admin_numbers = strval($this->get_mobile_no());
            $admin_numbers_arr = explode(',',$admin_numbers);
            $result = [];
            foreach($admin_numbers_arr as $admin_number){
                // Send SMS via ClickSend API.
                $http   = new WP_Http();
                $result[] = $http->request($api_url, array(
                'headers' => array(
                    'Content-Type'  => 'application/json',
                    'Authorization' => 'Basic '.base64_encode($this->get_username().':'.$this->get_api_key()),
                ),
                'method'  => 'POST',
                'body'    => json_encode(array(
                    'messages' => array(
                        array(
                            'source'   => 'wp-cf7',
                            'from'     => $this->get_sender_id(),
                            'body'     => $msg,
                            'to'       => $admin_number,
                            'schedule' => '',
                        ),
                    ),
                )),
                ));
            }
            return $result;
        }

        public function notifyAdminDivi($current_page_id,$fields)
        {
            if ( ! $this->is_active() OR ! $this->is_notification_enabled()) {
                return false;
            }
            if(!empty($custom_messages = $this->checkCustomMessageTableDivi($current_page_id))){
                $notify_msg = $custom_messages['admin_message'];
            }
            else{
                $notify_msg = $this->get_notify_msg();
            }
            $msg = $this->prepareNotifyMsgDivi($notify_msg, $fields);
            $api_url = 'https://rest.clicksend.com/v3/sms/send';
            $admin_numbers = strval($this->get_mobile_no());
            $admin_numbers_arr = explode(',',$admin_numbers);
            $result = [];
            foreach($admin_numbers_arr as $admin_number){
                // Send SMS via ClickSend API.
                $http   = new WP_Http();
                $result[] = $http->request($api_url, array(
                'headers' => array(
                    'Content-Type'  => 'application/json',
                    'Authorization' => 'Basic '.base64_encode($this->get_username().':'.$this->get_api_key()),
                ),
                'method'  => 'POST',
                'body'    => json_encode(array(
                    'messages' => array(
                        array(
                            'source'   => 'wp-cf7',
                            'from'     => $this->get_sender_id(),
                            'body'     => $msg,
                            'to'       => $admin_number,
                            'schedule' => '',
                        ),
                    ),
                )),
                ));
            }
            return $result;
        }
        
    }
}

#END OF PHP FILE
