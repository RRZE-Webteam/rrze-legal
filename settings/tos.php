<?php

namespace RRZE\Legal;

defined('ABSPATH') || exit;

$settings = [
    'version' => 1,
    'options_page' => [
        'parent' => [
            'slug' => 'legal',
        ],
        'page' => [
            'title' => __('Legal Mandatory Information', 'rrze-legal'),
        ],
        'menu' => [
            'title' => __('Legal Mandatory Information', 'rrze-legal'),
            'capability' => 'manage_options',
            'slug' => 'legal',
            'position' => 10,
        ],
    ],
    'settings' => [
        'title' => __('Legal Mandatory Information Settings', 'rrze-legal'),
        'sections' => [    
            [
                'id' => 'imprint',
                'title' => __('Imprint', 'rrze-legal'),
                'hide_title' => true,
                'description' => sprintf(
                    /* translators: %s: Url of the endpoint page. */
                    __('The output of this settings page is available at the following link: %s', 'rrze-legal'),
                    tos()->endpointLink('imprint')
                ),
                'subsections' => [
                    [
                        'id' => 'scope',
                        'title' => __('Scope', 'rrze-legal'),
                        'description' => '',
                        'fields' => [    
                            [
                                'name' => 'scope_websites',
                                'label' => __('Websites', 'rrze-legal'),
                                'description' => __('If this imprint applies to more than one website, add here the domain namens of the other websites. Please enter one domain name per line.', 'rrze-legal'),
                                'type' => 'textarea',
                                'default' => '',
                                'sanitize_callback' => [tos(), 'sanitizeTextareaList'],
                            ],
                        ],
                    ],
                    // NOTE:  'disabled' => true macht es readonly
                    [
                        'id' => 'representation',
                        'title' => __('Legal representatives', 'rrze-legal'),
                        'description' => __('Mandatory legal information about the legal representatives.', 'rrze-legal'),
                        'fields' => [
                            [
                                'name' => 'representation_name',
                                'label' => __('Organization name', 'rrze-legal'),
                                'type' => 'text',
                                'default' => '',
                                'sanitize_callback' => 'sanitize_text_field',
                                'required' => true,
                            ],
                            [
                                'name' => 'representation_person_name',
                                'label' => __('Legal representative', 'rrze-legal'),
                                'description' => __('Name of the legal representative.', 'rrze-legal'),
                                'type' => 'text',
                                'sanitize_callback' => 'sanitize_text_field',
                                'required' => true,
                                'template' => ['' => 'imprint-no-responsible-person'],
                            ],
                            [
                                'name' => 'representation_legal-label',
                                'label' => __('Legal form', 'rrze-legal'),
                                'description' => __('Legal form of the institution', 'rrze-legal'),
                                'type' => 'text',
                                'sanitize_callback' => 'sanitize_text_field',
                                'required' => true,
                            ],
                            [
                                'name' => 'representation_email',
                                'label' => __('Email', 'rrze-legal'),
                                'type' => 'email',
                                'sanitize_callback' => function ($input) {
                                    return tos()->validateEmail($input);
                                },
                            ],
                            [
                                'name' => 'representation_phone',
                                'label' => __('Phone', 'rrze-legal'),
                                'type' => 'tel',
                                'sanitize_callback' => 'sanitize_text_field',
                            ],
                            [
                                'name' => 'representation_fax',
                                'label' => __('Fax Number', 'rrze-legal'),
                                'type' => 'tel',
                                'sanitize_callback' => 'sanitize_text_field',
                            ],
                            [
                                'name' => 'representation_postal_street',
                                'label' => __('Street Name & House Number', 'rrze-legal'),
                                'type' => 'text',
                                'sanitize_callback' => 'sanitize_text_field',
                                'required' => true,
                            ],
                            [
                                'name' => 'representation_postal_code',
                                'label' => __('Postal Code', 'rrze-legal'),
                                'type' => 'text',
                                 'size'  => 5,
                                'sanitize_callback' => 'sanitize_text_field',
                                'required' => true,
                            ],
                            [
                                'name' => 'representation_postal_city',
                                'label' => __('City', 'rrze-legal'),
                                'type' => 'text',
                                'sanitize_callback' => 'sanitize_text_field',
                                'required' => true,
                            ],
                           
                            
                        ],
                    ],
                    [
                        'id' => 'responsible_person',
                        'title' => __('Editorially responsible person', 'rrze-legal'),
                        'description' => __('Data for contacting in legal terms.', 'rrze-legal'),
                        'fields' => [
                            [
                                'name' => 'responsible_person_organization',
                                'label' => __('Organization', 'rrze-legal'),
                                'description'   => __('The name of the department, chair or other facility','rrze-legal'),
                                'type' => 'text',
                                'default' => tos()->getSiteUrlHost(),
                                'sanitize_callback' => 'sanitize_text_field',
                                'required' => true,
                            ],
                            [
                                'name' => 'responsible_person_name',
                                'label' => __('Name', 'rrze-legal'),
                                'description' => __('Editorially responsible person for the website. (This is usually the chair owner or facility manager).', 'rrze-legal'),
                                'type' => 'text',
                                'sanitize_callback' => 'sanitize_text_field',
                                'required' => true,
                                'template' => ['' => 'imprint-no-responsible-person'],
                            ],
                            [
                                'name' => 'responsible_person_email',
                                'label' => __('Email', 'rrze-legal'),
                                'description'   => __('Contact email for the responsible person or the organization.','rrze-legal'),
                                'type' => 'email',
                                  'required' => true,
                                'sanitize_callback' => function ($input) {
                                    return tos()->validateEmail($input);
                                },
                            ],
                            [
                                'name' => 'responsible_person_phone',
                                'label' => __('Phone', 'rrze-legal'),
                                'description'   => __('Contact phone number for the responsible person or the organization.','rrze-legal'),
                                'type' => 'tel',
                                'sanitize_callback' => 'sanitize_text_field',
                            ],    
                            [
                                'name' => 'responsible_person_co',
                                'label' => __('c/o', 'rrze-legal'),
                                'description'   => __('Additional address line.','rrze-legal'),
                                'type' => 'text',
                                'sanitize_callback' => 'sanitize_text_field',
                                'required' => false,
                            ],
                            [
                                'name' => 'responsible_person_street',
                                'label' => __('Street Name & House Number', 'rrze-legal'),
                                'type' => 'text',
                                'sanitize_callback' => 'sanitize_text_field',
                                'required' => false,
                            ],
                            [
                                'name' => 'responsible_person_postal_code',
                                'label' => __('Postal Code', 'rrze-legal'),
                                'type' => 'text',
                                'size'  => 5,
                                'inline'    => true,
                                'sanitize_callback' => 'sanitize_text_field',
                                'required' => false,
                            ],
                            [
                                'name' => 'responsible_person_city',
                                'label' => __('City', 'rrze-legal'),
                                'type' => 'text',
                                'sanitize_callback' => 'sanitize_text_field',
                                'required' => false,
                            ],
                            
                            
                        ],
                    ],
                    [
                        'id' => 'webmaster',
                        'title' => __('Webmaster', 'rrze-legal'),
                        'description' => __('Data for contacting the content of the website.', 'rrze-legal'),
                        'fields' => [
                            [
                                'name' => 'webmaster_name',
                                'label' => __('Name', 'rrze-legal'),
                                'description' => __('Name of the webmaster or the technical contact for the website. This can be also a servicepoint.', 'rrze-legal'),
                                'type' => 'text',
                                'sanitize_callback' => 'sanitize_text_field',
                                'required' => true,
                            ],
                            [
                                'name' => 'webmaster_email',
                                'label' => __('Email', 'rrze-legal'),
                                'type' => 'email',
                                'description'   => __('Contact email for problems concerning the website.','rrze-legal'),
                                'default' => get_option('admin_email'),
                                'sanitize_callback' => function ($input) {
                                    return tos()->validateEmail($input);
                                },
                                'required' => true,
                            ],
                            [
                                'name' => 'webmaster_phone',
                                'label' => __('Phone', 'rrze-legal'),
                                'description'   => __('Contact phone number for problems concerning the website.','rrze-legal'),
                                'type' => 'tel',
                                'sanitize_callback' => 'sanitize_text_field',
                            ],
                        ],
                    ],
                    [
                        'id' => 'supervisory_authority',
                        'title' => __('Supervisory Authority', 'rrze-legal'),
                        'description' => __('Displays the supervisory authority.', 'rrze-legal'),
                        'fields' => [
                            [
                                'name' => 'supervisory_authority_name',
                                'label' => __('Name', 'rrze-legal'),
                                'type' => 'text',
                                'default' => '',
                                'sanitize_callback' => 'sanitize_text_field',
                                'required' => true,
                            ],
                         
                            [
                                'name' => 'supervisory_authority_email',
                                'label' => __('Email', 'rrze-legal'),
                                'type' => 'email',
                                'sanitize_callback' => function ($input) {
                                    return tos()->validateEmail($input);
                                },
                            ],
                            [
                                'name' => 'supervisory_authority_phone',
                                'label' => __('Phone', 'rrze-legal'),
                                'type' => 'tel',
                                'sanitize_callback' => 'sanitize_text_field',
                            ],
                            [
                                'name' => 'supervisory_authority_fax',
                                'label' => __('Fax Number', 'rrze-legal'),
                                'type' => 'tel',
                                'sanitize_callback' => 'sanitize_text_field',
                            ],
                            [
                                'name' => 'supervisory_authority_postal_street',
                                'label' => __('Street Name & House Number', 'rrze-legal'),
                                'type' => 'text',
                                'sanitize_callback' => 'sanitize_text_field',
                                'required' => true,
                            ],
                            [
                                'name' => 'supervisory_authority_postal_code',
                                'label' => __('Postal Code', 'rrze-legal'),
                                'type' => 'text',
                                 'size'  => 5,
                                'sanitize_callback' => 'sanitize_text_field',
                                'required' => true,
                            ],
                            [
                                'name' => 'supervisory_authority_postal_city',
                                'label' => __('City', 'rrze-legal'),
                                'type' => 'text',
                                'sanitize_callback' => 'sanitize_text_field',
                                'required' => true,
                            ],
                        ],
                    ],    
                    [
                        'id' => 'id_numbers',
                        'title' => __('Id Numbers', 'rrze-legal'),
                        'description' => __('ID Numbers and Banking informations of the organization.', 'rrze-legal'),
                        'fields' => [
                            
          
                            
                            [
                                'name' => 'id_numbers_ustg',
                                'label' => __('VAT identification number', 'rrze-legal'),
                                'type' => 'text',
                                'default' => '',
                                'sanitize_callback' => 'sanitize_text_field',
                                'required' => false,
                            ],
                            [
                                'name' => 'id_numbers_tax',
                                'label' => __('Tax Number', 'rrze-legal'),
                                'type' => 'text',
                                'default' => '',
                                'sanitize_callback' => 'sanitize_text_field',
                                'required' => false,
                            ],
                             [
                                'name' => 'id_numbers_duns',
                                'label' => __('DUNS Number', 'rrze-legal'),
                                'type' => 'text',
                                'default' => '',
                                'sanitize_callback' => 'sanitize_text_field',
                                'required' => false,
                            ],
                             [
                                'name' => 'id_numbers_eori',
                                'label' => __('EORI Number', 'rrze-legal'),
                                'type' => 'text',
                                'default' => '',
                                'sanitize_callback' => 'sanitize_text_field',
                                'required' => false,
                            ],
                             [
                                'name' => 'id_numbers_bankname',
                                'label' => __('Bank (Name)', 'rrze-legal'),
                                'type' => 'text',
                                'default' => '',
                                'sanitize_callback' => 'sanitize_text_field',
                                'required' => false,
                            ],
                            [
                                'name' => 'id_numbers_iban',
                                'label' => __('IBAN', 'rrze-legal'),
                                'type' => 'text',
                                'default' => '',
                                'sanitize_callback' => 'sanitize_text_field',
                                'required' => false,
                            ],
                             [
                                'name' => 'id_numbers_bic',
                                'label' => __('BIC-Code', 'rrze-legal'),
                                'type' => 'text',
                                'default' => '',
                                'sanitize_callback' => 'sanitize_text_field',
                                'required' => false,
                            ],
                        ],    
                    ],    
                    [
                        'id' => 'it_security',
                        'title' => __('IT Security', 'rrze-legal'),
                        'description' => __('Contact informations for IT Security.', 'rrze-legal'),
                        'fields' => [
                            [
                                'name' => 'it_security_name',
                                'label' => __('Name', 'rrze-legal'),
                                'type' => 'text',
                                'default' => '',
                                'sanitize_callback' => 'sanitize_text_field',
                                'required' => false,
                            ],
                         
                            [
                                'name' => 'it_security_email',
                                'label' => __('Email', 'rrze-legal'),
                                'type' => 'email',
                                'sanitize_callback' => function ($input) {
                                    return tos()->validateEmail($input);
                                },
                                'required' => false,
                            ],
                             [
                                'name' => 'it_security_url',
                                'label' => __('URL', 'rrze-legal'),
                                'type' => 'url',
                            ],
                            [
                                'name' => 'it_security_phone',
                                'label' => __('Phone', 'rrze-legal'),
                                'type' => 'tel',
                                'sanitize_callback' => 'sanitize_text_field',
                            ],
                            [
                                'name' => 'it_security_postal_co',
                                'label' => __('c/o', 'rrze-legal'),
                                'description'   => __('Additional address line.','rrze-legal'),
                                'type' => 'text',
                                'sanitize_callback' => 'sanitize_text_field',
                            ],
                            [
                                'name' => 'it_security_postal_street',
                                'label' => __('Street Name & House Number', 'rrze-legal'),
                                'type' => 'text',
                                'sanitize_callback' => 'sanitize_text_field',
                            ],
                            [
                                'name' => 'it_security_postal_code',
                                'label' => __('Postal Code', 'rrze-legal'),
                                'type' => 'text',
                                 'size'  => 5,
                                'sanitize_callback' => 'sanitize_text_field',
                            ],
                            [
                                'name' => 'it_security_postal_city',
                                'label' => __('City', 'rrze-legal'),
                                'type' => 'text',
                                'sanitize_callback' => 'sanitize_text_field',
                            ],
                        ],
                    ],    
                    [
                        'id' => 'whistleblower_system',
                        'title' => __('Whistleblower protection', 'rrze-legal'),
                        'description' => __('Information about a system in accordance with the Whistleblower Protection Act.', 'rrze-legal'),
                        'fields' => [
                            
          
                            
                            [
                                'name' => 'whistleblower_system_linktitle',
                                'label' => __('Title', 'rrze-legal'),
                                'type' => 'text',
                                'default' => __('Whistleblower system', 'rrze-legal'),
                                'sanitize_callback' => 'sanitize_text_field',
                                'required' => false,
                            ],
                            [
                                'name' => 'whistleblower_system_url',
                                'label' => __('URL', 'rrze-legal'),
                                'type' => 'url',
                                'default' => '',
                                'sanitize_callback' => 'sanitize_text_field',
                                'required' => false,
                            ],
                            
                        ],    
                    ],                     
                                        

                                        
                    [
                        'id' => 'optional',
                        'title' => __('Optional Information', 'rrze-legal'),
                        'description' => __('This option allows you to change predefined paragraphs, as well as to add another self-phrased paragraph.<br>Note: Official FAU facilities should have all of the following options enabled.', 'rrze-legal'),
                        'fields' => [
                            /*
                             * Wahl entfällt, da automatisch drin und nun änderbar
                             * Nur noch als reminder für IF-Bedingung enthalten
                            [
                                'name' => 'optional_representation',
                                'label' => __('Reference to the University Management', 'rrze-legal'),
                                'description' => __('Official representative of the university and its outward institutions is the president. For this purpose, a corresponding paragraph is displayed.', 'rrze-legal'),
                                'type' => 'radio',
                                'options' => [
                                    '1' => __('Yes', 'rrze-legal'),
                                    '0' => __('No', 'rrze-legal'),
                                ],
                                'default' => '0',
                                'inline' => true,
                                'template' => ['1' => 'imprint-representation'],
                            ],
                            
                            [
                                'name' => 'optional_supervisory_authority',
                                'label' => __('Supervisory Authority', 'rrze-legal'),
                                'description' => __('Displays the supervisory authority.', 'rrze-legal'),
                                'type' => 'radio',
                                'options' => [
                                    '1' => __('Yes', 'rrze-legal'),
                                    '0' => __('No', 'rrze-legal'),
                                ],
                                'default' => '0',
                                'inline' => true,
                                'template' => ['1' => 'imprint-supervisory-authority'],
                            ],
                            [
                                'name' => 'optional_id_numbers',
                                'label' => __('Identification Numbers', 'rrze-legal'),
                                'description' => __('Display of public and official identification numbers of the university.', 'rrze-legal'),
                                'type' => 'radio',
                                'options' => [
                                    '1' => __('Yes', 'rrze-legal'),
                                    '0' => __('No', 'rrze-legal'),
                                ],
                                'default' => '0',
                                'inline' => true,
                                'template' => ['1' => 'imprint-id-numbers'],
                            ],
                            [
                                'name' => 'optional_it_security',
                                'label' => __('IT Security', 'rrze-legal'),
                                'description' => __('Note and contact details for reporting IT security incidents.', 'rrze-legal'),
                                'type' => 'radio',
                                'options' => [
                                    '1' => __('Yes', 'rrze-legal'),
                                    '0' => __('No', 'rrze-legal'),
                                ],
                                'default' => '0',
                                'inline' => true,
                                'template' => ['1' => 'imprint-it-security'],
                            ],
                             *
                             */
                            [
                                'name' => 'optional_image_rights',
                                'label' => __('Image Rights', 'rrze-legal'),
                                'description' => __('Insert free text field for image rights?', 'rrze-legal'),
                                'type' => 'radio',
                                'options' => [
                                    '1' => __('Yes', 'rrze-legal'),
                                    '0' => __('No', 'rrze-legal'),
                                ],
                                'default' => '0',
                                'inline' => true,
                            ],
                            [
                                'name' => 'optional_image_rights_content',
                                'label' => __('Content', 'rrze-legal'),
                                'description' => __('Optional paragraph for the description of any image rights used.', 'rrze-legal'),
                                'type' => 'wpeditor',
                                'default' => '',
                            ],
                            [
                                'name' => 'optional_new_section',
                                'label' => __('Add a New Section', 'rrze-legal'),
                                'description' => '',
                                'type' => 'radio',
                                'options' => [
                                    '1' => __('Yes', 'rrze-legal'),
                                    '0' => __('No', 'rrze-legal'),
                                ],
                                'default' => '0',
                                'inline' => true,
                            ],
                            [
                                'name' => 'optional_new_section_content',
                                'label' => __('Content', 'rrze-legal'),
                                'description' => __('Content of the new, additional section.', 'rrze-legal'),
                                'type' => 'wpeditor',
                            ]
                        ],
                    ],
                ],
            ],
            [
                'id' => 'privacy',
                'title' => __('Privacy', 'rrze-legal'),
                'hide_title' => true,
                'description' => sprintf(
                    /* translators: %s: Url of the endpoint page. */
                    __('The output of this settings page is available at the following link: %s', 'rrze-legal'),
                    tos()->endpointLink('privacy')
                ),
                'subsections' => [
                    [
                        'id' => 'dpo',
                        'title' => __('Data Protection Officer', 'rrze-legal'),
                        'description' => __("The designation, position and tasks of a data protection officer (DPO) within an organization are described in Articles 37, 38 and 39 of the European Union (EU) General Data Protection Regulation (GDPR).", 'rrze-legal'),
                        'fields' => [
                            [
                                'name' => 'dpo_name',
                                'label' => __('Name (Office)', 'rrze-legal'),
                                'description' => '',
                                'type' => 'text',
                                'sanitize_callback' => 'sanitize_text_field',
                                'required' => true,
                            ],
                             [
                                'name' => 'dpo_person_name',
                                'label' => __('Name (Person)', 'rrze-legal'),
                                'description' => '',
                                'type' => 'text',
                                'sanitize_callback' => 'sanitize_text_field',
                            ],
                            [
                                'name' => 'dpo_email',
                                'label' => __('Email', 'rrze-legal'),
                                'type' => 'email',
                                'sanitize_callback' => function ($input) {
                                    return tos()->validateEmail($input);
                                },
                                'required' => true,
                            ],
                            [
                                'name' => 'dpo_phone',
                                'label' => __('Phone', 'rrze-legal'),
                                'type' => 'tel',
                                'sanitize_callback' => 'sanitize_text_field',
                            ],
                            [
                                'name' => 'dpo_fax',
                                'label' => __('Fax Number', 'rrze-legal'),
                                'type' => 'tel',
                                'sanitize_callback' => 'sanitize_text_field',
                            ],
                                        
                            [
                                'name' => 'dpo_postal_co',
                                'label' => __('c/o', 'rrze-legal'),
                                'description'   => __('Additional address line.','rrze-legal'),
                                'type' => 'text',
                                'sanitize_callback' => 'sanitize_text_field',
                            ],         
                                        
                            [
                                'name' => 'dpo_postal_street',
                                'label' => __('Street Name & House Number', 'rrze-legal'),
                                'type' => 'text',
                                'sanitize_callback' => 'sanitize_text_field',
                                'required' => true,
                            ],
                            [
                                'name' => 'dpo_postal_code',
                                'label' => __('Postal Code', 'rrze-legal'),
                                'type' => 'text',
                                'sanitize_callback' => 'sanitize_text_field',
                                'required' => true,
                            ],
                            [
                                'name' => 'dpo_postal_city',
                                'label' => __('City', 'rrze-legal'),
                                'type' => 'text',
                                'sanitize_callback' => 'sanitize_text_field',
                                'required' => true,
                            ],
                           
                        ],
                    ],
                    [
                        'id' => 'services',
                        'title' => __('Services', 'rrze-legal'),
                        'description' => __('If any of the following services are used, enable them to generate a corresponding notice in the privacy policy.', 'rrze-legal'),
                        'fields' => [
                            [
                                'name' => 'services_contact_form',
                                'label' => __('Contact Form', 'rrze-legal'),
                                'description' => __('Do you use a contact form on this website? (The accessibility declaration offers one, so the answer is usually "yes").', 'rrze-legal'),
                                'type' => 'radio',
                                'options' => [
                                    '1' => __('Yes', 'rrze-legal'),
                                    '0' => __('No', 'rrze-legal'),
                                ],
                                'default' => '1',
                                'inline' => true,
                                'template' => ['1' => 'privacy-contact-form'],
                            ],
                            [
                                'name' => 'services_registration_forms',
                                'label' => __('Registration/Registration Forms', 'rrze-legal'),
                                'description' => __('Do you use forms to sign up for events or other functions that require registration?', 'rrze-legal'),
                                'type' => 'radio',
                                'options' => [
                                    '1' => __('Yes', 'rrze-legal'),
                                    '0' => __('No', 'rrze-legal'),
                                ],
                                'default' => tos()->isRsvpActive() ? '1' : '0',
                                'inline' => true,
                                'template' => ['1' => 'privacy-registration-forms'],
                            ],
                            [
                                'name' => 'services_newsletter',
                                'label' => __('Newsletter/Mailinglist', 'rrze-legal'),
                                'description' => __('Do you offer a newsletter or mailing list?', 'rrze-legal'),
                                'type' => 'radio',
                                'options' => [
                                    '1' => __('Yes', 'rrze-legal'),
                                    '0' => __('No', 'rrze-legal'),
                                ],
                                'default' => tos()->isNewsletterActive() ? '1' : '0',
                                'inline' => true,
                                'template' => ['1' => 'privacy-newsletter'],
                            ],
                           
                        ],
                    ],
                    [
                        'id' => 'external_services',
                        'title' => __('External Service Providers', 'rrze-legal'),
                        'description' => __('If external service providers are used to include content on the website, they must also be included in the privacy policy.', 'rrze-legal'),
                        'fields' => [
                            [
                                'name' => 'service_providers',
                                'label' => __('Service Providers', 'rrze-legal'),
                                'description' => '',
                                'type' => 'multicheckbox',
                                'options' => tos()->getServiceProvidersOptions(),
                                'default' => tos()->getServiceProvidersStatus(),
                            ],
                        ],
                    ],
                    [
                        'id' => 'optional',
                        'title' => __('Optional Information', 'rrze-legal'),
                        'description' => __('Additional information about the privacy policy.', 'rrze-legal'),
                        'fields' => [
                            [
                                'name' => 'optional_new_section',
                                'label' => __('Add a New Section', 'rrze-legal'),
                                'type' => 'radio',
                                'options' => [
                                    '1' => __('Yes', 'rrze-legal'),
                                    '0' => __('No', 'rrze-legal'),
                                ],
                                'default' => '0',
                                'inline' => true,
                            ],
                            [
                                'name' => 'optional_new_section_content',
                                'label' => __('Content', 'rrze-legal'),
                                'description' => __('Content of the new, additional section.', 'rrze-legal'),
                                'type' => 'wpeditor',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'id' => 'accessibility',
                'title' => __('Accessibility', 'rrze-legal'),
                'hide_title' => true,
                'description' => sprintf(
                    /* translators: %s: Url of the endpoint page. */
                    __('The output of this settings page is available at the following link: %s', 'rrze-legal'),
                    tos()->endpointLink('accessibility')
                ),
                'subsections' => [
                    
                    [
                        'id' => 'compliance_status',
                        'title' => __('Compliance Status', 'rrze-legal'),
                        'description' => __('Officially stated status of the website, as well as its contents regarding the fulfillment of the legal requirements.', 'rrze-legal'),
                        'fields' => [
                            [
                                'name' => 'compliance_status_conformity',
                                'label' => __('Declaration of Conformity', 'rrze-legal'),
                                'description' => __('State of conformity in accordance with EU Directive 2102 and local legislation.', 'rrze-legal'),
                                'type' => 'select',
                                'options' => [
                                    '2'  => __('Completely compliant: the content is fully compliant with the accessibility standard without exceptions', 'rrze-legal'),
                                    '1'  => __('Partially Compliant: Some parts of the content are not fully compliant with the accessibility standard', 'rrze-legal'),
                                    '0'  => __('Non-compliant: The content does not comply with the accessibility standard', 'rrze-legal'),
                                    '-1' => __('Unknown: Content was not rated or rating results are not available', 'rrze-legal')
                                ],
                                'default' => '1',
                                'style' => [
                                    '-1' => 'alert-danger',
                                    '0'  => 'alert-danger',
                                    '1'  => 'alert-warning',
                                    '2'  => 'alert-success',
                                ],
                                'compliance' => [
                                    '-1' => false,
                                    '0'  => true,
                                    '1'  => true,
                                    '2'  => true,
                                ],
                            ],
                            [
                                'name' => 'compliance_status_method',
                                'label' => __('Method', 'rrze-legal'),
                                'type' => 'radio',
                                'options' => [
                                    '1' => __('Self-Evaluation', 'rrze-legal'),
                                    '2' => __('Third party rating', 'rrze-legal')
                                ],
                                'default' => '1',
                                'inline' => true,
                            ],
                            [
                                'name' => 'compliance_status_creation_date',
                                'label' => __('Creation Date', 'rrze-legal'),
                                'size' => 'normal',
                                'type' => 'date',
                                'min' => '2018-01-01',
                                'sanitize_callback' => 'sanitize_text_field',
                            ],
                            [
                                'name' => 'compliance_status_last_review_date',
                                'label' => __('Last Review Date', 'rrze-legal'),
                                'size' => 'normal',
                                'type' => 'date',
                                'min' => '2018-01-01',
                                'sanitize_callback' => 'sanitize_text_field',
                            ],
                            [
                                'name' => 'compliance_status_report_url',
                                'label' => __('Report URL', 'rrze-legal'),
                                'description' => __('If there is a detailed review, this can be linked here.', 'rrze-legal'),
                                'type' => 'text',
                                'placeholder' => 'https://',
                                'sanitize_callback' => 'sanitize_url',
                            ],
                        ],
                    ],
                    [
                        'id' => 'statement',
                        'title' => __('Statement', 'rrze-legal'),
                        'description' => __('List and explain the problems with the implementation of accessibility.', 'rrze-legal'),
                        'fields' => [
                            [
                                'name' => 'statement_non_accessible_content_helper',
                                'label' => __('Input Assistance For Non-Accessible Content', 'rrze-legal'),
                                'type' => 'radio',
                                'options' => [
                                    '1' => __('Fill out explanations manually', 'rrze-legal'),
                                    '0' => __('Use input help and supplement it with manual entries', 'rrze-legal'),
                                ],
                                'default' => '1',
                            ],
                            [
                                'name' => 'statement_non_accessible_content_list',
                                'label' => __('Non-Accessible Content (Selection List)', 'rrze-legal'),
                                'description' => __('Selection of the most common deficiencies that a website can have. However, when choosing one or more of the above-mentioned deficiencies, please provide below a plausible justification why this deficiency exists and which alternatives are available in order to access the content nevertheless.', 'rrze-legal'),
                                'type' => 'multicheckbox',
                                'options' => [
                                    '1' =>  __('PDF documents could not yet be converted to an accessible format.', 'rrze-legal'),
                                    '3' =>  __('Some documents have been provided by third parties. These documents are not available in an accessible version.', 'rrze-legal'),
                                    '4' =>  __('Embedded videos currently have no subtitles or transcription available.', 'rrze-legal'),
                                    '5' =>  __('Currently there is no textual description for directions describing maps or maps.', 'rrze-legal'),
                                    '6' =>  __('Graphics or images contained in the pages are currently not completely supplemented by text descriptions.', 'rrze-legal'),
                                    '7' =>  __('Tables are used for the purpose of optical design.', 'rrze-legal'),
                                    '8' =>  __('When using multilingual content on a page, sometimes the languages ​​are not correctly marked in HTML.', 'rrze-legal'),
                                    '9' =>  __('The font color in the logo with the full title of the website does not have enough contrast.', 'rrze-legal'),
                                ],
                            ],
                            [
                                'name' => 'statement_non_accessible_content_text',
                                'label' => __('Non-Accessible Content (Free Text)', 'rrze-legal'),
                                'description' => __('The website owner is obliged to publicly list all non-accessible components of the website and its contents. These must be specified here.', 'rrze-legal'),
                                'type' => 'wpeditor',
                            ],
                            [
                                'name' => 'statement_non_accessible_content_reason',
                                'label' => __('Reason', 'rrze-legal'),
                                'description' => __('In addition to the pure list of non-accessible contents, a justification for each of the above points should also be provided as to why barrier-free accessibility could not be achieved. Please note that the legislator lists the following reasons as unjustified: "Lack of priorities, time or ignorance". These items should not be used as justification.', 'rrze-legal'),
                                'type' => 'wpeditor',
                            ],
                            [
                                'name' => 'statement_non_accessible_content_alternative',
                                'label' => __('Alternatives', 'rrze-legal'),
                                'description' => __('Indicate here whether and which alternatives are available to obtain the above inaccessible content. This can be, for example, the contact via the feedback form or the indication of a body that provides help.', 'rrze-legal'),
                                'type' => 'wpeditor',
                            ],
                        ],
                    ],
                    [
                        'id' => 'feedback',
                        'title' => __('Feedback Mechanism', 'rrze-legal'),
                        'description' => __('Opportunities to contact for accessibility issues and errors.', 'rrze-legal'),
                        'fields' => [
                            [
                                'name' => 'feedback_contact_person',
                                'label' => __('Contact Person', 'rrze-legal'),
                                'description' => __('Enter a name for the responsible contact person for complaints or requests for help about lack of accessibility.', 'rrze-legal'),
                                'type' => 'text',
                                'sanitize_callback' => 'sanitize_text_field',
                            ],
                            [
                                'name' => 'feedback_contact_email',
                                'label' => __('Contact Email Addresse', 'rrze-legal'),
                                'description' => __('Recipient email address for complaints or requests for help about lack of accessibility. Please note: If a request about the possibility of contact remains completely or partially unanswered within six weeks, the supervisory authority will check at the request of the user whether measures are required in the context of the monitoring of the operator of the website (ie you).', 'rrze-legal'),
                                'type' => 'email',
                                'default' => get_option('admin_email'),
                                'sanitize_callback' => function ($input) {
                                    return tos()->validateEmail($input);
                                },
                            ],
                            [
                                'name' => 'feedback_email_cc',
                                'label' => __('Email CC', 'rrze-legal'),
                                'description' => __('Optional additional email address.', 'rrze-legal'),
                                'type' => 'email',
                                'sanitize_callback' => function ($input) {
                                    return tos()->validateEmail($input);
                                },
                            ],
                            [
                                'name' => 'feedback_email_subject',
                                'label' => __('Email Subject', 'rrze-legal'),
                                'type' => 'text',
                                'default' => __('Accessibility Feedback Form', 'rrze-legal'),
                                'sanitize_callback' => 'sanitize_text_field',
                            ],
                            [
                                'name' => 'feedback_contact_phone',
                                'label' => __('Contact Phone', 'rrze-legal'),
                                'description' => __('Contact number for telephone assistance.', 'rrze-legal'),
                                'type' => 'tel',
                                'sanitize_callback' => 'sanitize_text_field',
                            ],
                            [
                                'name' => 'feedback_contact_address',
                                'label' => __('Contact Address', 'rrze-legal'),
                                'description' =>  __('Postal address as an alternative to email address.', 'rrze-legal'),
                                'type' => 'textarea',
                                'sanitize_callback' => 'sanitize_textarea_field',
                            ],
                        ],
                    ],
                                         [
                        'id' => 'supervisory_authority',
                        'title' => __('Supervisory Authority', 'rrze-legal'),
                        'description' => __('Displays the supervisory authority.', 'rrze-legal'),
                        'fields' => [
                            [
                                'name' => 'supervisory_authority_name',
                                'label' => __('Name', 'rrze-legal'),
                                'type' => 'text',
                                'default' => '',
                                'sanitize_callback' => 'sanitize_text_field',
                                'required' => true,
                            ],
                         
                            [
                                'name' => 'supervisory_authority_email',
                                'label' => __('Email', 'rrze-legal'),
                                'type' => 'email',
                                'sanitize_callback' => function ($input) {
                                    return tos()->validateEmail($input);
                                },
                            ],
                            [
                                'name' => 'supervisory_authority_phone',
                                'label' => __('Phone', 'rrze-legal'),
                                'type' => 'tel',
                                'sanitize_callback' => 'sanitize_text_field',
                            ],
                            [
                                'name' => 'supervisory_authority_fax',
                                'label' => __('Fax Number', 'rrze-legal'),
                                'type' => 'tel',
                                'sanitize_callback' => 'sanitize_text_field',
                            ],
                            [
                                'name' => 'supervisory_authority_url',
                                'label' => __('URL', 'rrze-legal'),
                                'type' => 'url',
                                'sanitize_callback' => 'sanitize_text_field',
                            ], 
                             [
                                'name' => 'supervisory_authority_url_law',
                                'label' => __('URL (concerning LAW)', 'rrze-legal'),
                                'type' => 'url',
                                'sanitize_callback' => 'sanitize_text_field',
                            ], 
                            [
                                'name' => 'supervisory_authority_url_vo',
                                'label' => __('URL (concerning VO)', 'rrze-legal'),
                                'type' => 'url',
                                'sanitize_callback' => 'sanitize_text_field',
                            ], 
                             [
                                'name' => 'supervisory_authority_postal_co',
                                'label' => __('c/o', 'rrze-legal'),
                                'description'   => __('Additional address line.','rrze-legal'),
                                'type' => 'text',
                                'sanitize_callback' => 'sanitize_text_field',
                                'required' => true,
                            ],            
                            [
                                'name' => 'supervisory_authority_postal_street',
                                'label' => __('Street Name & House Number', 'rrze-legal'),
                                'type' => 'text',
                                'sanitize_callback' => 'sanitize_text_field',
                                'required' => true,
                            ],
                            [
                                'name' => 'supervisory_authority_postal_code',
                                'label' => __('Postal Code', 'rrze-legal'),
                                'type' => 'text',
                                 'size'  => 5,
                                'sanitize_callback' => 'sanitize_text_field',
                                'required' => true,
                            ],
                            [
                                'name' => 'supervisory_authority_postal_city',
                                'label' => __('City', 'rrze-legal'),
                                'type' => 'text',
                                'sanitize_callback' => 'sanitize_text_field',
                                'required' => true,
                            ],
                           
                        ],
                    ],    
                                        
                                     
                                        
                ],
            ],
            [
                'id' => 'scope',
                'title' => __('Organizational Affiliation', 'rrze-legal'),
                'hide_title' => true,
                'subsections' => [
                    [
                        'id' => 'scope',
                        'title' => __('Organizational Affiliation', 'rrze-legal'),
                        'hide_title' => true,
                        
                        'fields' => [
                            [
                                'name' => 'context',
                                'label' => __('Organization', 'rrze-legal'),
                                'description' => __('Indicate whether the website is operated by an institution affiliated with the university or by another institution.','rrze-legal').'<br>'.__('Please notice, that by chosing the organisation, additional data, like the name of the legal representative contact or the data policy officer are updated automatically.', 'rrze-legal'),
                                'type' => 'select',
                                // optionlist filled by data/tos.php
           //                     'options' => [
           //                         'fau' => __('Friedrich-Alexander-Universität Erlangen-Nürnberg', 'rrze-legal'),
           //                         'utn' => __('University of Technology Nuremberg ', 'rrze-legal'),
           //                         'uk' => __('Universitätsklinikum Erlangen', 'rrze-legal'),
           //                         'cooperation'  => __('Cooperation between different institutions', 'rrze-legal'),
           //                         'external' => __('External institution', 'rrze-legal'),
           //                     ],
                                'default' => 'fau'
                            ]
                           
                        ],
                    ],
                ],
             ],
        ],
    ],
];
