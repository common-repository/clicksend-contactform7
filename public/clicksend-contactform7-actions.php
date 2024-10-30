<?php
/**
 * Send SMS notification via ClickSend everytime we have a new Contact Form mail_sent.
 */

add_action('wpcf7_mail_sent', 'action_wpcf7_mail_sent');
function action_wpcf7_mail_sent($contact_form)
{
    $service = ClickSend_ContactForm7Service::get_instance();
    // Notify admin.
    $result = $service->notifyAdmin($contact_form);
    // Notify sender, if any.
    $re = $service->notifySender($contact_form);
    return $result;
}


add_action('et_pb_contact_form_submit','action_wpcf7_mail_sent_divi', 10, 2);
function action_wpcf7_mail_sent_divi($fields) {
    $current_page_id = get_the_ID();        
    $service = ClickSend_ContactForm7Service::get_instance();
    // Notify admin.
    $result = $service->notifyAdminDivi($current_page_id,$fields);
    $re = $service->notifySenderDivi($current_page_id,$fields);
    return $result;
}





