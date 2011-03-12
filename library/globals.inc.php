<?php
// Copyright (C) 2010 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

// $GLOBALS['print_command'] is the
// Print command for spooling to printers, used by statements.inc.php
// This is the command to be used for printing (without the filename).
// The word following "-P" should be the name of your printer.  This
// example is designed for 8.5x11-inch paper with 1-inch margins,
// 10 CPI, 6 LPI, 65 columns, 54 lines per page.
//
// IF lpr services are installed on Windows this setting will be similar
// Otherwise configure it as needed (print /d:PRN) might be an option for Windows parallel printers

//  Current supported languages:    // Allow capture of term for translation:
//   Amharic                        // xl('Amharic')
//   Arabic                         // xl('Arabic')
//   Armenian                       // xl('Armenian')
//   Bahasa Indonesia               // xl('Bahasa Indonesia')
//   Chinese (Simplified)           // xl('Chinese (Simplified)')
//   Chinese (Traditional)          // xl('Chinese (Traditional)')
//   Danish                         // xl('Danish')
//   Dutch                          // xl('Dutch')
//   English (Indian)               // xl('English (Indian)')
//   English (Standard)             // xl('English (Standard)')
//   French                         // xl('French')
//   German                         // xl('German')
//   Greek                          // xl('Greek')
//   Hebrew                         // xl('Hebrew')
//   Norwegian                      // xl('Norwegian')
//   Polish                         // xl('Polish')
//   Portuguese (Brazilian)         // xl('Portuguese (Brazilian)')
//   Portuguese (European)          // xl('Portuguese (European)')
//   Russian                        // xl('Russian')
//   Slovak                         // xl('Slovak')
//   Spanish                        // xl('Spanish')
//   Swedish                        // xl('Swedish')
//   Turkish                        // xl('Turkish')

// OS-dependent stuff.
if (stristr(PHP_OS, 'WIN')) {
  // MS Windows
  $mysql_bin_dir       = 'C:/xampp/mysql/bin';
  $perl_bin_dir        = 'C:/xampp/perl/bin';
  $temporary_files_dir = 'C:/windows/temp';
  $backup_log_dir      = 'C:/windows/temp';
}
else {
  // Everything else
  $mysql_bin_dir       = '/usr/bin';
  $perl_bin_dir        = '/usr/bin';
  $temporary_files_dir = '/tmp';
  $backup_log_dir      = '/tmp';
}

// Language constant declarations:
// xl('Appearance')
// xl('Locale')
// xl('Features')
// xl('Calendar')
// xl('Security')
// xl('Notifications')
// xl('Miscellaneous')

// List of user specific tabs and globals
$USER_SPECIFIC_TABS = array('Appearance',
                            'Locale');
$USER_SPECIFIC_GLOBALS = array('default_top_pane',
                               'concurrent_layout',
                               'css_header',
                               'units_of_measurement',
                               'date_display_format',
                               'time_display_format');

$GLOBALS_METADATA = array(

  // Appearance Tab
  //
  'Appearance' => array(

    'default_top_pane' => array(
      xl('Main Top Pane Screen'),       // descriptive name
      array(
        'main_info.php' => xl('Calendar Screen'),
        '../new/new.php' => xl('Patient Search/Add Screen'),
      ),
      'main_info.php',                  // default = calendar
      xl('Type of screen layout')
    ),

    'concurrent_layout' => array(
      xl('Layout Style'),               // descriptive name
      array(
        '0' => xl('Old style layout with no left menu'),
        '1' => xl('Navigation menu consists of pairs of radio buttons'),
        '2' => xl('Navigation menu is a tree view'),
        '3' => xl('Navigation uses a sliding menu'),
      ),
      '3',                              // default = tree menu
      xl('Type of screen layout')
    ),

    'css_header' => array(
      xl('Theme'),
      'css',
      'style_oemr.css',
      xl('Pick a CSS theme.')
    ),

    'gbl_nav_area_width' => array(
      xl('Navigation Area Width'),
      'num',
      '130',
      xl('Width in pixels of the left navigation frame.')
    ),

    'openemr_name' => array(
      xl('Application Title'),
      'text',
      'OpenEMR',
      xl('Application name for login page and main window title.')
    ),

    'full_new_patient_form' => array(
      xl('New Patient Form'),
      array(
        '0' => xl('Old-style static form without search or duplication check'),
        '1' => xl('All demographics fields, with search and duplication check'),
        '2' => xl('Mandatory or specified fields only, search and dup check'),
        '3' => xl('Mandatory or specified fields only, dup check, no search'),
      ),
      '1',                              // default
      xl('Style of form used for adding new patients')
    ),

    'patient_search_results_style' => array(
      xl('Patient Search Results Style'),
      array(
        '0' => xl('Encounter statistics'),
        '1' => xl('Mandatory and specified fields'),
      ),
      '0',                              // default
      xl('Type of columns displayed for patient search results')
    ),

    'simplified_demographics' => array(
      xl('Simplified Demographics'),
      'bool',                           // data type
      '0',                              // default = false
      xl('Omit insurance and some other things from the demographics form')
    ),

    'simplified_prescriptions' => array(
      xl('Simplified Prescriptions'),
      'bool',                           // data type
      '0',                              // default = false
      xl('Omit form, route and interval which then become part of dosage')
    ),

    'simplified_copay' => array(
      xl('Simplified Co-Pay'),
      'bool',                           // data type
      '0',                              // default = false
      xl('Omit method of payment from the co-pay panel')
    ),

    'use_charges_panel' => array(
      xl('Use Charges Panel'),
      'bool',                           // data type
      '0',                              // default = false
      xl('Enables the old Charges panel for entering billing codes and payments. Not recommended, use the Fee Sheet instead.')
    ),

    'online_support_link' => array(
      xl('Online Support Link'),
      'text',                           // data type
      'http://sourceforge.net/projects/openemr/support',
      xl('URL for OpenEMR support.')
    ),

  ),

  // Locale Tab
  //
  'Locale' => array(

    'language_default' => array(
      xl('Default Language'),
      'lang',                           // data type
      'English (Standard)',             // default = english
      xl('Default language if no other is allowed or chosen.')
    ),

    'language_menu_showall' => array(
      xl('All Languages Allowed'),
      'bool',                           // data type
      '1',                              // default = true
      xl('Allow all available languages as choices on menu at login.')			     				     
    ),
			
    'language_menu_other' => array(
      xl('Allowed Languages'),
      'm_lang',                         // data type
      '',                               // default = none
      xl('Select which languages, if any, may be chosen at login. (only pertinent if above All Languages Allowed is turned off)')
    ),

    'translate_layout' => array(
      xl('Translate Layouts'),
      'bool',                           // data type
      '1',                              // default = true
      xl('Is text from form layouts to be translated?')
    ),

    'translate_lists' => array(
      xl('Translate Lists'),
      'bool',                           // data type
      '1',                              // default = true
      xl('Is text from lists to be translated?')
    ),

    'translate_gacl_groups' => array(
      xl('Translate Access Control Groups'),
      'bool',                           // data type
      '1',                              // default = true
      xl('Are access control group names to be translated?')
    ),

    'translate_form_titles' => array(
      xl('Translate Patient Note Titles'),
      'bool',                           // data type
      '1',                              // default = true
      xl('Are patient note titles to be translated?')
    ),

    'translate_document_categories' => array(
      xl('Translate Document Categories'),
      'bool',                           // data type
      '1',                              // default = true
      xl('Are document category names to be translated?')
    ),

    'translate_appt_categories' => array(
      xl('Translate Appointment Categories'),
      'bool',                           // data type
      '1',                              // default = true
      xl('Are appointment category names to be translated?')
    ),

    'units_of_measurement' => array(
      xl('Units for Visit Forms'),
      array(
        '1' => xl('Show both US and metric (main unit is US)'),
        '2' => xl('Show both US and metric (main unit is metric)'),
        '3' => xl('Show US only'),
        '4' => xl('Show metric only'),
      ),
      '1',                              // default = Both/US
      xl('Applies to the Vitals form and Growth Chart')
    ),

    'disable_deprecated_metrics_form' => array(
      xl('Disable Old Metric Vitals Form'),
      'bool',                           // data type
      '1',                              // default = true
      xl('This was the older metric-only Vitals form, now deprecated.')
    ),

    'phone_country_code' => array(
      xl('Telephone Country Code'),
      'num',
      '1',                              // default = North America
      xl('1 = North America. See http://www.wtng.info/ for a list of other country codes.')
    ),

    'date_display_format' => array(
      xl('Date Display Format'),
      array(
        '0' => xl('YYYY-MM-DD'),
        '1' => xl('MM/DD/YYYY'),
        '2' => xl('DD/MM/YYYY'),
      ),
      '0',
      xl('Format used to display most dates.')
    ),
    
    'time_display_format' => array(
      xl('Time Display Format'),
      array(
        '0' => xl('24 hr'),
        '1' => xl('12 hr'),
      ),
      '0',
      xl('Format used to display most times.')
    ),

    'currency_decimals' => array(
      xl('Currency Decimal Places'),
      array(
        '0' => xl('0'),
        '1' => xl('1'),
        '2' => xl('2'),
      ),
      '2',
      xl('Number of digits after decimal point for currency, usually 0 or 2.')
    ),

    'currency_dec_point' => array(
      xl('Currency Decimal Point Symbol'),
      array(
        '.' => xl('Period'),
        ',' => xl('Comma'),
      ),
      '.',
      xl('Symbol used as the decimal point for currency. Not used if Decimal Places is 0.')
    ),

    'currency_thousands_sep' => array(
      xl('Currency Thousands Separator'),
      array(
        ',' => xl('Comma'),
        '.' => xl('Period'),
        ' ' => xl('Space'),
        ''  => xl('None'),
      ),
      ',',
      xl('Symbol used to separate thousands for currency.')
    ),

    'gbl_currency_symbol' => array(
      xl('Currency Designator'),
      'text',                           // data type
      '$',                              // default
      xl('Code or symbol to indicate currency')
    ),

  ),

  // Features Tab
  //
  'Features' => array(

    'specific_application' => array(
      xl('Specific Application'),
      array(
        '0' => xl('None'),
        '1' => xl('Athletic team'),
        '2' => xl('IPPF'),
        '3' => xl('Weight loss clinic'),
      ),
      '0',                              // default
      xl('Indicator for specialized usage')
    ),

    'inhouse_pharmacy' => array(
      xl('Drugs and Products'),
      array(
        '0' => xl('Do not inventory and sell any products'),
        '1' => xl('Inventory and sell drugs only'),
        '2' => xl('Inventory and sell both drugs and non-drug products'),
        '3' => xl('Products but no prescription drugs and no templates'),
      ),
      '0',                              // default
      xl('Option to support inventory and sales of products')
    ),

    'disable_chart_tracker' => array(
      xl('Disable Chart Tracker'),
      'bool',                           // data type
      '0',                              // default = false
      xl('Removes the Chart Tracker feature')
    ),

    'disable_phpmyadmin_link' => array(
     xl('Disable phpMyAdmin'),
     'bool',                            // data type
     '0',                               // default = false
     xl('Removes support for phpMyAdmin')
    ),

    'disable_immunizations' => array(
      xl('Disable Immunizations'),
      'bool',                           // data type
      '0',                              // default = false
      xl('Removes support for immunizations')
    ),

    'disable_prescriptions' => array(
      xl('Disable Prescriptions'),
      'bool',                           // data type
      '0',                              // default = false
      xl('Removes support for prescriptions')
    ),

    'omit_employers' => array(
      xl('Omit Employers'),
      'bool',                           // data type
      '0',                              // default = false
      xl('Omit employer information in patient demographics')
    ),

    'select_multi_providers' => array(
      xl('Support Multi-Provider Events'),
      'bool',                           // data type
      '0',                              // default = false
      xl('Support calendar events that apply to multiple providers')
    ),

    'disable_non_default_groups' => array(
      xl('Disable User Groups'),
      'bool',                           // data type
      '1',                              // default = true
      xl('Normally this should be checked. Not related to access control.')
    ),

    'ignore_pnotes_authorization' => array(
      xl('Skip Authorization of Patient Notes'),
      'bool',                           // data type
      '1',                              // default = true
      xl('Do not require patient notes to be authorized')
    ),

    'support_encounter_claims' => array(
      xl('Allow Encounter Claims'),
      'bool',                           // data type
      '0',                              // default = false
      xl('Allow creation of claims containing diagnoses but not procedures or charges. Most clinics do not want this.')
    ),

    'advance_directives_warning' => array(
      xl('Advance Directives Warning'),
      'bool',                           // data type
      '0',                              // default = false
      xl('Display advance directives in the demographics page.')
    ),

    'configuration_import_export' => array(
      xl('Configuration Export/Import'),
      'bool',                           // data type
      '0',                              // default = false
      xl('Support export/import of configuration data via the Backup page.')
    ),

    'restrict_user_facility' => array(
      xl('Restrict Users to Facilities'),
      'bool',                           // data type
      '0',                              // default
      xl('Restrict non-authorized users to the Schedule Facilities set in User admin.')
    ),

    'set_facility_cookie' => array(
      xl('Remember Selected Facility'),
      'bool',                           // data type
      '0',                              // default
      xl('Set a facility cookie to remember the selected facility between logins.')
    ),

    'discount_by_money' => array(
      xl('Discounts as Monetary Amounts'),
      'bool',                           // data type
      '1',                              // default = true
      xl('Discounts at checkout time are entered as money amounts, as opposed to percentage.')
    ),

    'gbl_visit_referral_source' => array(
      xl('Referral Source for Encounters'),
      'bool',                           // data type
      '0',                              // default = false
      xl('A referral source may be specified for each visit.')
    ),

    'gbl_mask_patient_id' => array(
      xl('Mask for Patient IDs'),
      'text',                           // data type
      '',                               // default
      xl('Specifies formatting for the external patient ID.  # = digit, @ = alpha, * = any character.  Empty if not used.')
    ),

    'gbl_mask_invoice_number' => array(
      xl('Mask for Invoice Numbers'),
      'text',                           // data type
      '',                               // default
      xl('Specifies formatting for invoice reference numbers.  # = digit, @ = alpha, * = any character.  Empty if not used.')
    ),

    'gbl_mask_product_id' => array(
      xl('Mask for Product IDs'),
      'text',                           // data type
      '',                               // default
      xl('Specifies formatting for product NDC fields.  # = digit, @ = alpha, * = any character.  Empty if not used.')
    ),

    'force_billing_widget_open' => array(
      xl('Force Billing Widget Open'),
      'bool',                           // data type
      '0',                              // default = false
      xl('This will force the Billing Widget in the Patient Summary screen to always be open.')
    ),

    'activate_ccr_ccd_report' => array(
      xl('Activate CCR/CCD Reporting'),
      'bool',                           // data type
      '0',                              // default = false
      xl('This will activate the CCR(Continuity of Care Record) and CCD(Continuity of Care Document) reporting.')
    ),

  ),

  // Calendar Tab
  //
  'Calendar' => array(

    'disable_calendar' => array(
      xl('Disable Calendar'),
      'bool',                           // data type
      '0',                              // default
      xl('Do not display the calendar.')
    ),

    'schedule_start' => array(
      xl('Calendar Starting Hour'),
      'hour',
      '8',                              // default
      xl('Beginning hour of day for calendar events.')
    ),

    'schedule_end' => array(
      xl('Calendar Ending Hour'),
      'hour',
      '17',                             // default
      xl('Ending hour of day for calendar events.')
    ),

    'calendar_interval' => array(
      xl('Calendar Interval'),
      array(
        '5' => '5',
       '10' => '10',
       '15' => '15',
       '20' => '20',
       '30' => '30',
       '60' => '60',
      ),
      '15',                              // default
      xl('The time granularity of the calendar and the smallest interval in minutes for an appointment slot.')
    ),

    'calendar_appt_style' => array(
      xl('Appointment Display Style'),
      array(
        '1' => 'Last name',
        '2' => 'Last name, first name',
        '3' => 'Last name, first name (title)',
        '4' => 'Last name, first name (title: description)',
      ),
      '2',                               // default
      xl('This determines how appointments display on the calendar.')
    ),

    'docs_see_entire_calendar' => array(
      xl('Providers See Entire Calendar'),
      'bool',                           // data type
      '0',                              // default
      xl('Check this if you want providers to see all appointments by default and not just their own.')
    ),

    'auto_create_new_encounters' => array(
      xl('Auto-Create New Encounters'),
      'bool',                           // data type
      '1',                              // default
      xl('Automatically create a new encounter when appointment status is set to "@" (arrived).')
    ),

  ),

  // Security Tab
  //
  'Security' => array(

    'timeout' => array(
      xl('Idle Session Timeout Seconds'),
      'num',                            // data type
      '7200',                           // default
      xl('Maximum idle time in seconds before logout. Default is 7200 (2 hours).')
    ),

    'secure_password' => array(
      xl('Require Strong Passwords'),
      'bool',                           // data type
      '0',                              // default
      xl('Strong password means at least 8 characters, and at least three of: a number, a lowercase letter, an uppercase letter, a special character.')
    ),

    'password_history' => array(
      xl('Require Unique Passwords'),
      'bool',                           // data type
      '0',                              // default
      xl('Means none of last three passwords are allowed when changing a password.')
    ),

    'password_expiration_days' => array(
      xl('Default Password Expiration Days'),
      'num',                            // data type
      '0',                              // default
      xl('Default password expiration period in days. 0 means this feature is disabled.')
    ),

    'password_grace_time' => array(
      xl('Password Expiration Grace Period'),
      'num',                            // data type
      '0',                              // default
      xl('Period in days where a user may login with an expired password.')
    ),

    'is_client_ssl_enabled' => array(
      xl('Enable Client SSL'),
      'bool',                           // data type
      '0',                              // default
      xl('Enable client SSL certificate authentication.')
    ),

    'certificate_authority_crt' => array(
      xl('Path to CA Certificate File'),
      'text',                           // data type
      '',                               // default
      xl('Set this to the full absolute path. For creating client SSL certificates for HTTPS.')
    ),

    'certificate_authority_key' => array(
      xl('Path to CA Key File'),
      'text',                           // data type
      '',                               // default
      xl('Set this to the full absolute path. For creating client SSL certificates for HTTPS.')
    ),

    'client_certificate_valid_in_days' => array(
      xl('Client Certificate Expiration Days'),
      'num',                            // data type
      '365',                            // default
      xl('Number of days that the client certificate is valid.')
    ),

    'Emergency_Login_email_id' => array(
      xl('Emergency Login Email Address'),
      'text',                           // data type
      '',                               // default
      xl('Email address, if any, to receive emergency login user activation messages.')
    ),

  ),

  // Notifications Tab
  //
  'Notifications' => array(

    'practice_return_email_path' => array(
      xl('Notification Email Address'),
      'text',                           // data type
      '',                               // default
      xl('Email address, if any, to receive administrative notifications.')
    ),

    'EMAIL_METHOD' => array(
      xl('Email Transport Method'),
      array(
        'PHPMAIL'  => 'PHPMAIL',
        'SENDMAIL' => 'SENDMAIL',
        'SMTP'     => 'SMTP',
      ),
      'SMTP',                             // default
      xl('Method for sending outgoing email.')
    ),

    'SMTP_HOST' => array(
      xl('SMTP Server Hostname'),
      'text',                           // data type
      'localhost',                      // default
      xl('If SMTP is used, the server`s hostname or IP address.')
    ),

    'SMTP_PORT' => array(
      xl('SMTP Server Port Number'),
      'num',                            // data type
      '25',                             // default
      xl('If SMTP is used, the server`s TCP port number (usually 25).')
    ),

    'SMTP_USER' => array(
      xl('SMTP User for Authentication'),
      'text',                           // data type
      '',                               // default
      xl('Must be empty if SMTP authentication is not used.')
    ),

    'SMTP_PASS' => array(
      xl('SMTP Password for Authentication'),
      'text',                           // data type
      '',                               // default
      xl('Must be empty if SMTP authentication is not used.')
    ),

    'EMAIL_NOTIFICATION_HOUR' => array(
      xl('Email Notification Hours'),
      'num',                            // data type
      '50',                             // default
      xl('Number of hours in advance to send email notifications.')
    ),

    'SMS_NOTIFICATION_HOUR' => array(
      xl('SMS Notification Hours'),
      'num',                            // data type
      '50',                             // default
      xl('Number of hours in advance to send SMS notifications.')
    ),

    'SMS_GATEWAY_USENAME' => array(
      xl('SMS Gateway Username'),
      'text',                           // data type
      '',                               // default
      xl('Username for SMS Gateway.')
    ),

    'SMS_GATEWAY_PASSWORD' => array(
      xl('SMS Gateway Password'),
      'text',                           // data type
      '',                               // default
      xl('Password for SMS Gateway.')
    ),

    'SMS_GATEWAY_APIKEY' => array(
      xl('SMS Gateway API Key'),
      'text',                           // data type
      '',                               // default
      xl('API key for SMS Gateway.')
    ),

  ),

  // Logging
  //
  'Logging' => array(

    'enable_auditlog' => array(
      xl('Enable Audit Logging'),
      'bool',                           // data type
      '1',                              // default
      xl('Enable Audit Logging')
    ),

    'audit_events_patient-record' => array(
      xl('Audit Logging Patient Record'),
      'bool',                           // data type
      '1',                              // default
      xl('Enable logging of patient record modifications.').' ('.xl('Note that Audit Logging needs to be enabled above').')'
    ),

    'audit_events_scheduling' => array(
      xl('Audit Logging Scheduling'),
      'bool',                           // data type
      '1',                              // default
      xl('Enable logging of scheduling activities.').' ('.xl('Note that Audit Logging needs to be enabled above').')'
    ),

    'audit_events_order' => array(
      xl('Audit Logging Order'),
      'bool',                           // data type
      '1',                              // default
      xl('Enable logging of ordering activities.').' ('.xl('Note that Audit Logging needs to be enabled above').')'
    ),

    'audit_events_security-administration' => array(
      xl('Audit Logging Security Administration'),
      'bool',                           // data type
      '1',                              // default
      xl('Enable logging of security and administration activities.').' ('.xl('Note that Audit Logging needs to be enabled above').')'
    ),

    'audit_events_backup' => array(
      xl('Audit Logging Backups'),
      'bool',                           // data type
      '1',                              // default
      xl('Enable logging of backup related activities.').' ('.xl('Note that Audit Logging needs to be enabled above').')'
    ),

    'audit_events_other' => array(
      xl('Audit Logging Miscellaneous'),
      'bool',                           // data type
      '1',                              // default
      xl('Enable logging of miscellaneous activities.').' ('.xl('Note that Audit Logging needs to be enabled above').')'
    ),

    'audit_events_query' => array(
      xl('Audit Logging SELECT Query'),
      'bool',                           // data type
      '0',                              // default
      xl('Enable logging of all SQL SELECT queries.').' ('.xl('Note that Audit Logging needs to be enabled above').')'
    ),

    'enable_atna_audit' => array(
      xl('Enable ATNA Auditing'),
      'bool',                           // data type
      '0',                              // default
      xl('Enable Audit Trail and Node Authentication (ATNA).')
    ),

    'atna_audit_host' => array(
      xl('ATNA audit host'),
      'text',                           // data type
      '',                               // default
      xl('The hostname of the ATNA audit repository machine.')
    ),

    'atna_audit_port' => array(
      xl('ATNA audit port'),
      'text',                           // data type
      '6514',                           // default
      xl('Listening port of the RFC 5425 TLS syslog server.')
    ),

    'atna_audit_localcert' => array(
      xl('ATNA audit local certificate'),
      'text',                           // data type
      '',                               // default
      xl('Certificate to send to RFC 5425 TLS syslog server.')
    ),

    'atna_audit_cacert' => array(
      xl('ATNA audit CA certificate'),
      'text',                           // data type
      '',                               // default
      xl('CA Certificate for verifying the RFC 5425 TLS syslog server.')
    ),

  ),

  // Miscellaneous Tab
  //
  'Miscellaneous' => array(

    'mysql_bin_dir' => array(
      xl('Path to MySQL Binaries'),
      'text',                           // data type
      $mysql_bin_dir,                   // default
      xl('Full path to directory containing MySQL executables.')
    ),

    'perl_bin_dir' => array(
      xl('Path to Perl Binaries'),
      'text',                           // data type
      $perl_bin_dir,                    // default
      xl('Full path to directory containing Perl executables.')
    ),

    'temporary_files_dir' => array(
      xl('Path to Temporary Files'),
      'text',                           // data type
      $temporary_files_dir,             // default
      xl('Full path to directory used for temporary files.')
    ),

    'backup_log_dir' => array(
      xl('Path for Event Log Backup'),
      'text',                           // data type
      $backup_log_dir,                  // default
      xl('Full path to directory for event log backup.')
    ),

    'state_data_type' => array(
      xl('State Data Type'),
      array(
        '2' => xl('Text field'),
        '1' => xl('Single-selection list'),
       '26' => xl('Single-selection list with ability to add to the list'),
      ),
      '26',                             // default
      xl('Field type to use for employer or subscriber state in demographics.')
    ),

    'state_list' => array(
      xl('State list'),
      'text',                           // data type
      'state',                          // default
      xl('List used by above State Data Type option.')
    ),

    'state_custom_addlist_widget' => array(
      xl('State List Widget Custom Fields'),
      'bool',                           // data type
      '1',                              // default
      xl('Show the custom state form for the add list widget (will ask for title and abbreviation).')
    ),

    'country_data_type' => array(
      xl('Country Data Type'),
      array(
        '2' => xl('Text field'),
        '1' => xl('Single-selection list'),
       '26' => xl('Single-selection list with ability to add to the list'),
      ),
      '26',                             // default
      xl('Field type to use for employer or subscriber country in demographics.')
    ),

    'country_list' => array(
      xl('Country list'),
      'text',                           // data type
      'country',                          // default
      xl('List used by above Country Data Type option.')
    ),

    'print_command' => array(
      xl('Print Command'),
      'text',                           // data type
      'lpr -P HPLaserjet6P -o cpi=10 -o lpi=6 -o page-left=72 -o page-top=72',
      xl('Shell command for printing from the server.')
    ),

    'default_chief_complaint' => array(
      xl('Default Reason for Visit'),
      'text',                           // data type
      '',
      xl('You may put text here as the default complaint in the New Patient Encounter form.')
    ),

    'default_new_encounter_form' => array(
      xl('Default Encounter Form ID'),
      'text',                           // data type
      '',
      xl('To automatically open the specified form. Some sports teams use football_injury_audit here.')
    ),

    'patient_id_category_name' => array(
      xl('Patient ID Category Name'),
      'text',                           // data type
      'Patient ID card',                // default
      xl('Optional category name for an ID Card image that can be viewed from the patient summary page.')
    ),

    'patient_photo_category_name' => array(
      xl('Patient Photo Category Name'),
      'text',                  // data type
      'Patient Photograph',    // default
      xl('Optional category name for photo images that can be viewed from the patient summary page.')
    ),

    'MedicareReferrerIsRenderer' => array(
      xl('Medicare Referrer Is Renderer'),
      'bool',                           // data type
      '0',                              // default = true
      xl('For Medicare only, forces the referring provider to be the same as the rendering provider.')
    ),

    'post_to_date_benchmark' => array(
      xl('Financial Close Date (yyyy-mm-dd)'),
      'text',                           // data type
      date('Y-m-d',time() - (10 * 24 * 60 * 60)),                // default
      xl('The payments posted cannot go below this date.This ensures that after taking the final report nobody post for previous dates.')
    ),

    'enable_hylafax' => array(
      xl('Enable Hylafax Support'),
      'bool',                           // data type
      '0',                              // default
      xl('Enable Hylafax Support')
    ),

    'hylafax_server' => array(
      xl('Hylafax Server'),
      'text',                           // data type
      'localhost',                      // default
      xl('Hylafax server hostname.')
    ),

    'hylafax_basedir' => array(
      xl('Hylafax Directory'),
      'text',                           // data type
      '/var/spool/fax',                 // default
      xl('Location where Hylafax stores faxes.')
    ),

    'hylafax_enscript' => array(
      xl('Hylafax Enscript Command'),
      'text',                           // data type
      'enscript -M Letter -B -e^ --margins=36:36:36:36', // default
      xl('Enscript command used by Hylafax.')
    ),

    'enable_scanner' => array(
      xl('Enable Scanner Support'),
      'bool',                           // data type
      '0',                              // default
      xl('Enable Scanner Support')
    ),

    'scanner_output_directory' => array(
      xl('Scanner Directory'),
      'text',                           // data type
      '/mnt/scan_docs',                 // default
      xl('Location where scans are stored.')
    ),

  ),

  // Connectors Tab
  //
  'Connectors' => array(

    'lab_exchange_enable' => array(
      xl('Enable Lab Exchange'),
      'bool',                           // data type
      '0',
      xl('Enable the OpenEMR Support LLC Lab Exchange Service.')
    ),

    'lab_exchange_siteid' => array(
      xl('Lab Exchange Site ID'),
      'text',                           // data type
      '3',
      xl('Site ID for the OpenEMR Support LLC Lab Exchange Service.')
    ),

    'lab_exchange_token' => array(
      xl('Lab Exchange Token ID'),
      'text',                           // data type
      '12345',
      xl('Token ID for the OpenEMR Support LLC Lab Exchange Service.')
    ),

    'lab_exchange_endpoint' => array(
      xl('Lab Exchange Site Address'),
      'text',                           // data type
      'https://openemrsupport.com:29443/len/api',
      xl('Https link for the OpenEMR Support LLC Lab Exchange Service.')
    ),

  ),

);
?>
