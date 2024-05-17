<?php 

if(!function_exists('getMailsVariable')){
    function getMailsVariable($type){
        switch ($type) {
            case 'ticket':
                return [
                    ['name' => 'Ticket ID','key' => '{ticket_id}'],
                    ['name' => 'Ticket URL','key' => '{ticket_url}'],
                    ['name' => 'Department','key' => '{ticket_department}'],
                    ['name' => 'Department Email','key' => '{ticket_department_email}'],
                    ['name' => 'Date Opened','key' => '{ticket_date}'],
                    ['name' => 'Ticket Subject','key' => '{ticket_subject}'],
                    ['name' => 'Ticket Message','key' => '{ticket_message}'],
                    ['name' => 'Ticket Status','key' => '{ticket_status}'],
                    ['name' => 'Ticket Priority','key' => '{ticket_priority}'],
                    ['name' => 'Ticket Service','key' => '{ticket_service}'],
                ];
            case 'estimate':
                return [
                    ['name' => 'Estimate Link','key' => '{estimate_link}'],
                    ['name' => 'Estimate Number','key' => '{estimate_number}'],
                    ['name' => 'Reference no.','key' => '{estimate_reference_no}'],
                    ['name' => 'Estimate Expiry Date','key' => '{estimate_expirydate}'],
                    ['name' => 'Estimate Date','key' => '{estimate_date}'],
                    ['name' => 'Estimate Status','key' => '{estimate_status}'],
                    ['name' => 'Estimate Sale Agent','key' => '{estimate_sale_agent}'],
                    ['name' => 'Estimate Total','key' => '{estimate_total}'],
                    ['name' => 'Estimate Subtotal','key' => '{estimate_subtotal}'],
                ];
            case 'contract':
                return [
                    ['name' => 'Contract ID','key' => '{contract_id}'],
                    ['name' => 'Contract Subject','key' => '{contract_subject}'],
                    ['name' => 'Contract Description','key' => '{contract_description}'],
                    ['name' => 'Contract Date Start','key' => '{contract_datestart}'],
                    ['name' => 'Contract Date End','key' => '{contract_dateend}'],
                    ['name' => 'Contract Value','key' => '{contract_contract_value}'],
                    ['name' => 'Contract Link','key' => '{contract_link}'],
                ];
            case 'invoice':
                return [
                    ['name' => 'Invoice Link','key' => '{invoice_link}'],
                    ['name' => 'Invoice Number','key' => '{invoice_number}'],
                    ['name' => 'Invoice Duedate','key' => '{invoice_duedate}'],
                    ['name' => 'Invoice Date','key' => '{invoice_date}'],
                    ['name' => 'Invoice Status','key' => '{invoice_status}'],
                    ['name' => 'Invoice Sale Agent','key' => '{invoice_sale_agent}'],
                    ['name' => 'Invoice Total','key' => '{invoice_total}'],
                    ['name' => 'Invoice Subtotal','key' => '{invoice_subtotal}'],
                    ['name' => 'Invoice Amount Due','key' => '{invoice_amount_due}'],
                    ['name' => 'Payment Recorded Total','key' => '{payment_total}'],
                    ['name' => 'Payment Recorded Date','key' => '{payment_date}'],
                ];
            case 'subscriptions':
                return [
                    ['name' => 'Subscription ID','key' => '{subscription_id}'],
                    ['name' => 'Subscription Name','key' => '{subscription_name}'],
                    ['name' => 'Subscription Description','key' => '{subscription_description}'],
                    ['name' => 'Subscription Subscribe Link','key' => '{subscription_link}'],
                ];
            case 'credit_note':
                return [
                    ['name' => 'Credit Note Number','key' => '{credit_note_number}'],
                    ['name' => 'Date','key' => '{credit_note_date}'],
                    ['name' => 'Status','key' => '{credit_note_status}'],
                    ['name' => 'Total','key' => '{credit_note_total}'],
                    ['name' => 'Subtotal','key' => '{credit_note_subtotal}'],
                    ['name' => 'Credits Used','key' => '{credit_note_credits_used}'],
                    ['name' => 'Credits Remaining','key' => '{credit_note_credits_remaining}'],
                ];
            case 'tasks':
                return [
                    ['name' => 'Staff/Contact who take action on task','key' => '{task_user_take_action}'],
                    ['name' => 'Task Link','key' => '{task_link}'],
                    ['name' => 'Comment Link','key' => '{comment_link}'],
                    ['name' => 'Task Name','key' => '{task_name}'],
                    ['name' => 'Task Description','key' => '{task_description}'],
                    ['name' => 'Task Status','key' => '{task_status}'],
                    ['name' => 'Task Comment','key' => '{task_comment}'],
                    ['name' => 'Task Priority','key' => '{task_priority}'],
                    ['name' => 'Task Start Date','key' => '{task_startdate}'],
                    ['name' => 'Task Due Date','key' => '{task_duedate}'],
                    ['name' => 'Related to','key' => '{task_related}'],
                ];
            case 'client':
                return [
                    ['name' => 'Contact Firstname','key' => '{contact_firstname}'],
                    ['name' => 'Contact Lastname','key' => '{contact_lastname}'],
                    ['name' => 'Contact Phone Number','key' => '{contact_phonenumber}'],
                    ['name' => 'Contact Email','key' => '{contact_email}'],
                    ['name' => 'Set New Password URL','key' => '{set_password_url}'],
                    ['name' => 'Email Verification URL','key' => '{email_verification_url}'],
                    ['name' => 'Reset Password URL','key' => '{reset_password_url}'],
                    ['name' => 'Client Company','key' => '{client_company}'],
                    ['name' => 'Client Phone Number','key' => '{client_phonenumber}'],
                    ['name' => 'Client Country','key' => '{client_country}'],
                    ['name' => 'Client City','key' => '{client_city}'],
                    ['name' => 'Client Zip','key' => '{client_zip}'],
                    ['name' => 'Client State','key' => '{client_state}'],
                    ['name' => 'Client Address','key' => '{client_address}'],
                    ['name' => 'Client Vat Number','key' => '{client_vat_number}'],
                    ['name' => 'Client ID','key' => '{client_id}'],
                    ['name' => 'Password','key' => '{password}'],
                    ['name' => 'Statement From','key' => '{statement_from}'],
                    ['name' => 'Statement To','key' => '{statement_to}'],
                    ['name' => 'Statement Balance Due','key' => '{statement_balance_due}'],
                    ['name' => 'Statement Amount Paid','key' => '{statement_amount_paid}'],
                    ['name' => 'Statement Invoiced Amount','key' => '{statement_invoiced_amount}'],
                    ['name' => 'Statement Beginning Balance','key' => '{statement_beginning_balance}'],
                    ['name' => 'Customer Files Admin Link','key' => '{customer_profile_files_admin_link}'],
                ];
            case 'proposals':
                return [
                    ['name' => 'Proposal ID','key' => '{proposal_id}'],
                    ['name' => 'Proposal Number','key' => '{proposal_number}'],
                    ['name' => 'Subject','key' => '{proposal_subject}'],
                    ['name' => 'Proposal Total','key' => '{proposal_total}'],
                    ['name' => 'Proposal Subtotal','key' => '{proposal_subtotal}'],
                    ['name' => 'Open Till','key' => '{proposal_open_till}'],
                    ['name' => 'Proposal Assigned','key' => '{proposal_assigned}'],
                    ['name' => 'Company Name','key' => '{proposal_proposal_to}'],
                    ['name' => 'Address','key' => '{proposal_address}'],
                    ['name' => 'City','key' => '{proposal_city}'],
                    ['name' => 'State','key' => '{proposal_state}'],
                    ['name' => 'Zip Code','key' => '{proposal_zip}'],
                    ['name' => 'Country','key' => '{proposal_country}'],
                    ['name' => 'Email','key' => '{proposal_email}'],
                    ['name' => 'Phone','key' => '{proposal_phone}'],
                    ['name' => 'Proposal Link','key' => '{proposal_link}'],
                ];
            case 'documents':
                return [
                    ['name' => 'Document ID','key' => '{document_id}'],
                    ['name' => 'Document Number','key' => '{document_number}'],
                    ['name' => 'Name','key' => '{document_name}'],
                    ['name' => 'Document Subject','key' => '{document_subject}'],
                    ['name' => 'Document From','key' => '{document_from}'],
                    ['name' => 'Open Till','key' => '{document_open_till}'],
                    ['name' => 'Document Assigned','key' => '{document_assigned}'],
                    ['name' => 'Document To','key' => '{document_to}'],
                    ['name' => 'Email','key' => '{document_email}'],
                    ['name' => 'Document Link','key' => '{document_link}'],
                ];
            case 'project':
                return [
                    ['name' => 'Project Name','key' => '{project_name}'],
                    ['name' => 'Project Description','key' => '{project_description}'],
                    ['name' => 'Project Start Date','key' => '{project_start_date}'],
                    ['name' => 'Project Deadline','key' => '{project_deadline}'],
                    ['name' => 'Project Link','key' => '{project_link}'],
                    ['name' => 'File Creator','key' => '{file_creator}'],
                    ['name' => 'Comment Creator','key' => '{comment_creator}'],
                    ['name' => 'Discussion Link','key' => '{discussion_link}'],
                    ['name' => 'Discussion Subject','key' => '{discussion_subject}'],
                    ['name' => 'Discussion Description','key' => '{discussion_description}'],
                    ['name' => 'Discussion Creator','key' => '{discussion_creator}'],
                    ['name' => 'Discussion Comment','key' => '{discussion_comment}'],
                ];
            case 'staff':
                return [
                    ['name' => 'Staff Firstname','key' => '{staff_firstname}'],
                    ['name' => 'Staff Lastname','key' => '{staff_lastname}'],
                    ['name' => 'Staff Email','key' => '{staff_email}'],
                    ['name' => 'Staff Date Created','key' => '{staff_datecreated}'],
                    ['name' => 'Reset Password Url','key' => '{reset_password_url}'],
                    ['name' => 'Reminder Text','key' => '{staff_reminder_description}'],
                    ['name' => 'Reminder Date','key' => '{staff_reminder_date}'],
                    ['name' => 'Reminder Relation Name','key' => '{staff_reminder_relation_name}'],
                    ['name' => 'Reminder Relation Link','key' => '{staff_reminder_relation_link}'],
                    ['name' => 'Two Factor Authentication Code','key' => '{two_factor_auth_code}'],
                    ['name' => 'Password','key' => '{password}'],
                ];
            case 'leads':
                return [
                    ['name' => 'Lead Name','key' => '{lead_name}'],
                    ['name' => 'Lead Email','key' => '{lead_email}'],
                    ['name' => 'Lead Position','key' => '{lead_position}'],
                    ['name' => 'Lead Website','key' => '{lead_website}'],
                    ['name' => 'Lead Description','key' => '{lead_description}'],
                    ['name' => 'Lead Phone Number','key' => '{lead_phonenumber}'],
                    ['name' => 'Lead Company','key' => '{lead_company}'],
                    ['name' => 'Lead Country','key' => '{lead_country}'],
                    ['name' => 'Lead Zip','key' => '{lead_zip}'],
                    ['name' => 'Lead City','key' => '{lead_city}'],
                    ['name' => 'Lead State','key' => '{lead_state}'],
                    ['name' => 'Lead Address','key' => '{lead_address}'],
                    ['name' => 'Lead Assigned','key' => '{lead_assigned}'],
                    ['name' => 'Lead Status','key' => '{lead_status}'],
                    ['name' => 'Lead Souce','key' => '{lead_source}'],
                    ['name' => 'Lead Link','key' => '{lead_link}'],
                ];
            case 'event':
                return [
                    ['name' => 'Event Title','key' => '{event_title}'],
                    ['name' => 'Event Description','key' => '{event_description}'],
                    ['name' => 'Start Date','key' => '{event_start_date}'],
                    ['name' => 'End Date','key' => '{event_end_date}'],
                    ['name' => 'Event Link','key' => '{event_link}'],
                ];
            case 'other':
                return [
                    ['name' => 'Logo URL','key' => '{logo_url}'],
                    ['name' => 'Logo image with URL','key' => '{logo_image_with_url}'],
                    ['name' => 'Dark logo image with URL','key' => '{dark_logo_image_with_url}'],
                    ['name' => 'CRM URL','key' => '{crm_url}'],
                    ['name' => 'Admin URL','key' => '{admin_url}'],
                    ['name' => 'Main Domain','key' => '{main_domain}'],
                    ['name' => 'Company Name','key' => '{companyname}'],
                    ['name' => 'Email Signature','key' => '{email_signature}'],
                    ['name' => 'Terms & Conditions URL','key' => '{terms_and_conditions_url}'],
                    ['name' => 'Privacy Policy URL','key' => '{privacy_policy_url}'],
                ];
            default:
                return [];
        }
    }
}

?>