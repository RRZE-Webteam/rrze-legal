<?php

namespace RRZE\Legal\TOS;

defined('ABSPATH') || exit;

use RRZE\Legal\Locale;
use RRZE\Legal\Template;
use function RRZE\Legal\plugin;
use function RRZE\Legal\settings;

class ContactForm
{
    protected $error;

    public function __construct()
    {
        $this->error = false;
    }

    public function setForm()
    {
        $captcha = $this->generateCaptcha();
        $wp_nonce = wp_nonce_field('accessibility_contact_form', '_wpnonce', true, false);

        $defaultData = [
            'captcha_num_1'  => mb_convert_case($captcha['num_1'], MB_CASE_TITLE, 'UTF-8'),
            'captcha_num_2'  => $captcha['num_2'],
            'captcha_result' => $captcha['result'],
            'wp_nonce'       => $wp_nonce
        ];

        if (isset($_POST['_wpnonce']) && wp_verify_nonce(sanitize_key($_POST['_wpnonce']), 'accessibility_contact_form')) {
            global $wp;

            $_wpnonce = $_POST['_wpnonce'];
            $transientName = $this->generateHash();

            if (isset($_POST['message_name'])) {
                $name = sanitize_text_field(wp_unslash($_POST['message_name']));
                $data['name'] = $name;
            }
            if (isset($_POST['message_email'])) {
                $email = sanitize_email(wp_unslash($_POST['message_email']));
                $data['email'] = $email;
            }
            if (isset($_POST['message_feedback'])) {
                $message = sanitize_textarea_field(wp_unslash($_POST['message_feedback']));
                $data['message'] = $message;
            }
            if (isset($_POST['message_human'])) {
                $result = sanitize_text_field(wp_unslash($_POST['message_human']));
            }
            if (isset($_POST['message_solution'])) {
                $solution = sanitize_text_field(wp_unslash($_POST['message_solution']));
            }

            $this->validateForm($name, $email, $message, $result, $solution);

            if ($this->hasError()) {
                $data = array_merge(
                    $data,
                    $defaultData,
                    $this->getError()
                );
                set_transient($transientName, $data, MINUTE_IN_SECONDS);
            } else {
                $response = $this->sendMail($name, $email, $message);
                if (!$response) {
                    $data = array_merge(
                        $data,
                        $defaultData,
                        [
                            'error_message_could_not_be_sent' => __('The message could not be sent.', 'rrze-legal')
                        ]
                    );
                } else {
                    $data = array_merge(
                        $defaultData,
                        [
                            'message_has_been_sent_successfully' => __('Thank you for your message. It was sent successfully.', 'rrze-legal')
                        ]
                    );
                }
                set_transient($transientName, $data, MINUTE_IN_SECONDS);
            }
            $redirectUrl = home_url(
                add_query_arg(
                    [
                        '_wpnonce'   => $_wpnonce,
                        '_transient' => $transientName
                    ],
                    $wp->request
                )
            );
            wp_redirect($redirectUrl . '#contact-form');
            exit;
        } elseif (isset($_GET['_wpnonce']) && wp_verify_nonce(sanitize_key($_GET['_wpnonce']), 'accessibility_contact_form')) {
            global $wp;

            $transientName = isset($_GET['_transient']) ? sanitize_key($_GET['_transient']) : '';
            $data = get_transient($transientName);
            if ($data !== false) {
                delete_transient($transientName);
                $data = array_merge(
                    $data,
                    $defaultData
                );
            } else {
                $redirectUrl = home_url(
                    add_query_arg(
                        [],
                        $wp->request
                    )
                );
                wp_redirect($redirectUrl . '#contact-form');
                exit;
            }
        } else {
            $data = $defaultData;
        }

        $langCode = is_user_logged_in() && is_admin() ? Locale::getUserLangCode() : Locale::getLangCode();
        $_tpl = 'accessibility-contact-form';
        $template = plugin()->getPath(Template::TOS_PATH) . $_tpl . '-' . $langCode . '.html';
        return Template::getContent($template, $data);
    }

    protected function sendMail($name, $from, $message)
    {
        $to = sanitize_email(settings()->getOption('accessibility_feedback', 'contact_email'));
        $subject = sanitize_text_field(settings()->getOption('accessibility_feedback', 'email_subject'));
        $headers = [
            'Content-Type: text/plain; charset=UTF-8',
            sprintf('Reply-To: %1$s <%2$s>', sanitize_text_field($name), sanitize_text_field($from))
        ];
        if (settings()->getOption('accessibility_feedback', 'email_cc')) {
            $headers[] = sprintf('CC: <%s>', sanitize_email(settings()->getOption('accessibility_feedback', 'email_cc')));
        }

        $pretext = __('The following message was entered in the accessibility feedback form.', 'rrze-legal') . " \n\n";
        $pretext .= __('From', 'rrze-legal') . ": \n";
        $pretext .= '   ' . __('Name', 'rrze-legal') . ': ' . sanitize_text_field($name) . " \n";
        $pretext .= '   ' . __('Email Addresse', 'rrze-legal') . ': ' . sanitize_email($from) . " \n";


        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $pretext .= '   ' . __('User Agent', 'rrze-legal') . ': ' . sanitize_text_field($_SERVER['HTTP_USER_AGENT']) . " \n";
        }

        $pretext .= '   ' . __('Sending Time', 'rrze-legal') . ': ' . date(__("d/m/Y - g:i a", 'rrze-legal')) . " \n";
        $pretext .= '   ' . __('Website Form', 'rrze-legal') . ': ' . get_option('siteurl') . " \n\n";
        $pretext .= __('Message entered by the sender', 'rrze-legal') . ": \n\n";


        $message = $pretext . $message;


        $message .= "\n\n-- \n";
        $message .= __('Go to the website', 'rrze-legal') . ': ' . get_option('siteurl') . " \n";
        $message .= __('Dashboard', 'rrze-legal') . ': ' . get_option('siteurl') . "/wp-admin/ \n";

        return wp_mail($to, $subject, $message, $headers);
    }

    protected function validateForm($name, $email, $message, $result, $solution)
    {
        if (empty($name)) {
            $this->error['error_name'] = __('Please enter a name.', 'rrze-legal');
        }
        if (empty($email)) {
            $this->error['error_email'] = __('The email address field must be filled out and contain a correctly spelled email address.', 'rrze-legal');
        } elseif (!is_email($email)) {
            $this->error['error_email'] = __('The specified e-mail address is not correct.', 'rrze-legal');
        }
        if (empty($message)) {
            $this->error['error_message'] = __('Please enter a text.', 'rrze-legal');
        }
        if (empty($result)) {
            $this->error['error_captcha'] = __('Please enter a number as solution.', 'rrze-legal');
        } elseif ($result !== $solution) {
            $this->error['error_captcha'] = __('The entered number is wrong.', 'rrze-legal');
        }
    }

    protected function hasError()
    {
        return $this->error !== false ? true : false;
    }

    protected function getError()
    {
        return $this->error;
    }

    protected function messageResponse($type, $message)
    {
        global $formError;
        if ('success' === $type) {
            echo '<div class="alert alert-success">' . esc_html($message) . '</div>';
        } else {
            if ($_POST && $formError instanceof \WP_Error && is_wp_error($message)) {
                foreach ($formError->get_error_messages() as $error) {
                    echo '<div class="alert alert-warning" role="alert">';
                    echo '<strong>' . __('Error', 'rrze-legal') . '</strong>:';
                    echo esc_html($error) . '<br/>';
                    echo '</div>';
                }
            }
        }
    }

    protected function generateHash()
    {
        return sprintf(
            '%04x%04x%04x',
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000
        );
    }

    protected function generateCaptcha()
    {
        $numbers = [
            __('zero', 'rrze-legal'),
            __('one', 'rrze-legal'),
            __('two', 'rrze-legal'),
            __('three', 'rrze-legal'),
            __('four', 'rrze-legal'),
            __('five', 'rrze-legal'),
            __('six', 'rrze-legal'),
            __('seven', 'rrze-legal'),
            __('eight', 'rrze-legal'),
            __('nine', 'rrze-legal')
        ];

        $num_1 = wp_rand(2, 6);
        $num_2 = wp_rand(2, 6);
        $result = $num_1 * $num_2;

        return [
            'num_1' => $numbers[$num_1],
            'num_2' => $numbers[$num_2],
            'result' => $result
        ];
    }
}
