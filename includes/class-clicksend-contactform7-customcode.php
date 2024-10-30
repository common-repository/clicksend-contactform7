<?php

/**
 * Make sure ClickSend_ContactForm7Service class is available before
 * creating this class.
 */
if (class_exists('ClickSend_ContactForm7Service')) {

	class clicksend_submenu_page extends ClickSend_ContactForm7Service{

		public function __construct(){
			add_action('admin_menu', array($this,'clicksend_submenu_page'));
			add_action('wpcf7_admin_init', array($this,'capture_form_data'));
			// add_action('wpcf7_admin_init', array($this,'my_plugin_update_callback'));
			add_action('wp_ajax_delete_message', array($this,'delete_custom_message'));
		}
      	public function delete_custom_message(){
      		global $wpdb;
      		$id = $_POST['id'];
      		$table_name = $wpdb->prefix.'clicksend_messages';
      		$deleted = $wpdb->delete($table_name,['id'=>$id]);
      		if($deleted){
      			$res = ["code"=>200,"message"=>"successfully deleted"];
      			echo json_encode($res);
      		}
      		else{
      			$res = ["code"=>401,"message"=>"error"];
      			echo json_encode($res);
      		}
      		wp_die();
      	}
		public function my_plugin_update_callback() {
		    global $wpdb; 
		    $db_table_name = $wpdb->prefix.'clicksend_messages';  // table name
		    $charset_collate = $wpdb->get_charset_collate();
		     //Check to see if the table exists already, if not, then create it
		    if($wpdb->get_var( "show tables like '$db_table_name'" ) != $db_table_name ) 
		     {
		        $sql1 = "CREATE TABLE $db_table_name (
		        `id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
		        `admin_message` text NOT NULL,
		        `customer_message` text NOT NULL,
		        `status` enum('enabled','disabled') NOT NULL,
		        `page_id` int(11) NOT NULL,
		        `cf_form_id` int(11) NOT NULL
		        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
		       require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		       dbDelta( $sql1 );
		     }
		}
			
		public function clicksend_submenu_page() {
		    add_submenu_page(
		        'wpcf7',   // Parent menu slug or the built-in menu slug (e.g., 'options-general.php')
		        'Create messages',   // Page Title
		        'Create messages',        // Menu Title
		        'manage_options',        // Capability required to access
		        'create-message',        // Menu Slug
		        array($this,'clicksend_message_page_content'), // Callback function to display content
		    );
		    
		}
		private function menu_page_url($args = '') {
	        $args = wp_parse_args($args, array());

	        $url = menu_page_url('create-message', false);
	        $url = add_query_arg(array('service' => 'clicksend'), $url);

	        if ( ! empty($args)) {
	            $url = add_query_arg($args, $url);
	        }

	        return $url;
	    }
	    private function get_post_value($key, $type = 'string', $default = '') {
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

	        return $val;
	    }
	    
	    public function settings_page_note(){
	    	return "A standard SMS is 160 standard characters.";
	    }
		   
	    public function get_contact_forms() {
	    	// Include the Contact Form 7 plugin if it's not already included
			if (class_exists('WPCF7_ContactForm')) {

				$all_contact_forms = WPCF7_ContactForm::find();

				$cf_data = [];
				if ($all_contact_forms) {
				    foreach ($all_contact_forms as $contact_form) {
				        $form_id = $contact_form->id();
				        $form_title = $contact_form->title();
				        $cf_data[] = ["form_id" => $form_id,"form_title"=>$form_title];
				    }
				} 
				else {
				   return '';
				}
				return $cf_data;
			}
		}    	
		public function capture_form_data(){

			$current_page = isset($_GET['page']) ? $_GET['page'] : '';
    		$target_page = 'create-message';
    		

			if ($current_page === $target_page && 'POST' == $_SERVER['REQUEST_METHOD'] && isset($_POST['submit'])  && $_GET['action']=='save_message') {
				
				global $wpdb;
				$table_name = $wpdb->prefix.'clicksend_messages';
				$admin_message = $this->get_post_value('admin_message');
				$customer_message = $this->get_post_value('customer_message');
				$cf_form_id = $this->get_post_value('cf_form_id');
				$page_id = $this->get_post_value('page_id');
				$msg_datas = $this->list_messages();   
				foreach($msg_datas as $msg_data){
					if($msg_data['cf_form_id'] == $cf_form_id && $msg_data['page_id'] == $page_id){
						echo "<script>alert('Message for same form already exists')</script>";
						return;
					}
				}
				$data = ['admin_message'=>$admin_message,'customer_message'=>$customer_message,'cf_form_id'=>$cf_form_id,'page_id'=>$page_id];
				$result = $wpdb->insert($table_name,$data);
				if ($result === false) {
				$error_message = $wpdb->last_error;
				echo $error_message;
				} 
			}
		}
		public function list_messages(){
			global $wpdb;
			$table_name = $wpdb->prefix.'clicksend_messages';
	        $results = $wpdb->get_results("SELECT id,page_id,cf_form_id,admin_message,customer_message,status FROM $table_name",ARRAY_A);
	        $msg_datas = [];
	        if(!empty($results)){
	        	foreach($results as $result){
		        	$page_title = get_the_title($result['page_id']);
		        	if($result['cf_form_id'] != 0){
		        		$form_name = $this->get_cf7_form_name($result['cf_form_id']);
		        	}
		        	else{
		        		$form_name = "All";
		        	}
		        	$msg_datas[] = ["form_name"=>$form_name,"page_title"=>$page_title,"admin_message"=>$result['admin_message'],"customer_message"=>$result['customer_message'],"status"=>$result['status'],"id"=>$result['id'],"cf_form_id"=>$result['cf_form_id'],"page_id"=>$result['page_id']];

	        	}
	        }	        
	        return $msg_datas;
		}
		public function get_cf7_form_name($form_id) {
		    $form = get_post($form_id);
		    if ($form && $form->post_type === 'wpcf7_contact_form') {
		        return $form->post_title;
		    }
		    return false;
		}
		public function clicksend_message_page_content() {
		    // Your submenu page content goes here
		    $contact_forms = $this->get_contact_forms();
		    $html =  '<div class="wrap">';
		    $html .= '<h2>SMS form settings</h2>';
		    $html .= '<form method="post" action="'.esc_url($this->menu_page_url('action=save_message')).'" id="clicksend-custom-messages">
			<fieldset style="border: 1px solid #ccc; padding: 20px;margin-bottom: 20px;">
			<legend>Add Messages</legend>'.
			wp_nonce_field("create-message").
			'<table class="form-table">
			<tbody>
			<tr>
			<th scope="row"><label for="cf_form_id">'.esc_html(__('Select Contact Form', 'clicksend-contactform7')).'</label></th>
			<td><select id="cf_form_id" name="cf_form_id"><option value="0">All</option>';
			foreach ($contact_forms as $contact_form) {
				$html .= '<option value="'.$contact_form['form_id'].'">'.$contact_form['form_title'].'</option>';
			}
            $html .=  '</select></td></tr>
                <tr><th scope="row"><label for="page_id">'.esc_html(__('Select Page', 'clicksend-contactform7')).'</label></th>
                    <td><select name="page_id" id="page_id"><option>Select</option>';
			$pages = get_pages();                            
			foreach ($pages as $page) {
				$html .= '<option value="'. $page->ID.'">'.$page->post_title.'</option>';
			}
            $html .=  '</select></td>
                </tr>
                <tr>
                    <th scope="row"><label for="admin_message"><?php <label>Admin message</label></th>
                    <td><textarea type="checkbox" aria-required="true" value="" id="admin_message" name="admin_message" class="regular-text code"
                              placeholder="Write your message here. E.g. You have a new lead, {{_name}} {{_id}}"></textarea></td>
                </tr>
                <tr>
                    <th scope="row"><label for="customer_message">Customer Message</label></th>
                    <td>
                    <textarea type="checkbox" aria-required="true" value="" id="customer_message" name="customer_message" class="regular-text code"
                              placeholder="Write your message here. E.g. Thanks for your enquiry. We will be in touch in 24 hours."></textarea>
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
                    <td>Your form name.</td>
                </tr>
                <tr>
                    <td width="150"><em>{{_id}}</em></td>
                    <td>Your form unique id.</td>
                </tr>
                <tr>
                    <td width="150"><em>{{your-field-name}}</em></td>
                    <td
                    <p>A custom form field name you set when you created your contact form.</p>
                    <p><em><strong>Example</strong>: [text* my-nickname]</em></p>
                    <p>If you have a contact form field set like so, your placeholder will be <em>{{my-nickname}}</em></p>
                    </td>
                </tr>
                </tbody>
            </table>

            <p class="submit"><input type="submit" class="button button-primary" value="Save Message" name="submit"/></p>
        </fieldset>';
		    $html .= '</form></div>';
		    
		    $html .=  '<div class="wrap"><fieldset style="border: 1px solid #ccc; padding: 20px;margin-bottom: 20px;">
	                    <legend>List Messages</legend><table class="form-table"><tr><th>#</th><th>Admin message</th><th>Customer message</th><th>Contact Form Name</th><th>Page</th><th>Options</th></tr>';        
	        $msg_datas = $this->list_messages();      
	        $i = 1;  
	        foreach($msg_datas as $msg_data){
	        	$html .= '<tr><td>'.$i.'</td><td>'.$msg_data['admin_message'].'</td><td>'.$msg_data['customer_message'].'</td><td>'.$msg_data['form_name'].'</td><td>'.$msg_data['page_title'].'</td><td><button id="delete_message" class="buton button-secondary" data-id="'.$msg_data['id'].'">Delete</button></td></tr>';
	        	$i++;
	        }        
		    $html .= '';
		    $html .= '</fieldset></div>';
		    $html .="<div><center>Content suggested for Admin message and Customer message will populate under 'List Messages'</center></div>";
		    echo $html;
		}
	}
	$clicksend_custom_messages = new clicksend_submenu_page();
}
          
        