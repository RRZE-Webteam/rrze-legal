<?php

namespace RRZE\Legal;

defined('ABSPATH') || exit;

use RRZE\Legal\TOS\{Endpoint, NavMenu};
use RRZE\Legal\Consent\Frontend;

/**
 * Class Main
 * @package RRZE\Legal
 */
class Main
{
    /**
     * Class constructor.
     */
    public function __construct()
    {
        // Load network settings
        $isPluginActiveForNetwork = Utils::isPluginActiveForNetwork(plugin()->getBaseName());
        if ($isPluginActiveForNetwork) {
            network()->loaded();
            network()->setAdminMenu();
        }

        // Adds a admin menu page
        add_action('admin_menu', [$this, 'adminMenu']);

        // Load consent settings
        consent()->loaded();
        consentCategories()->loaded();
        consentCookies()->loaded();

        // Load TOS settings
        tos()->loaded();
        new Endpoint();
        NavMenu::addTosMenu();

        // Set admin submenus
        tos()->setAdminMenu();
        consent()->setAdminMenu();
        consentCategories()->setAdminMenu();
        consentCookies()->setAdminMenu();

        // Load banner
        if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/wp-json/') === false) {
            Frontend::loaded();
        }

        // Update
        Update::loaded();

        // Notices for the administrator
        add_action('admin_init', [$this, 'adminInit']);
    }

    /**
     * Adds a admin menu page.
     * @return void
     */
    public function adminMenu()
    {
        add_menu_page(
            __('Legal', 'rrze-legal'),
            __('Legal', 'rrze-legal'),
            'manage_options',
            'legal',
            '',
            'dashicons-privacy',
            null
        );
    }

    public function adminInit()
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $slugs = Endpoint::getSlugs();
        $published = [];
        if (tos()->overwriteEndpoints()) {
            foreach (array_keys($slugs) as $slug) {
                $page = get_page_by_path($slug);
                if (!is_null($page) && $page->post_status == 'publish') {
                    $published[$slug] = $page->ID;
                }
            }
        }
        $pagePrefix = tos()->getPagePrefix();
        $currentPage = array_key_exists('page', $_GET) ? $_GET['page'] : '';
        $current = array_key_exists('current-tab', $_GET) ? $_GET['current-tab'] : '';
        $current = $current ? ltrim($current, $pagePrefix) : '';
        $current = $current == '' ? array_key_first($slugs) : $current;

        $options = tos()->getOptions();
        foreach (tos()->getFields() as $key => $field) {
            $slug = explode('_', $key)[0];
            $required = isset($field['required']) ? (bool) $field['required'] : false;
            if ($currentPage != 'legal'  && !isset($published[$slug]) && $options[$key] === '' && $required) {
                add_action('admin_notices', [$this, 'requiredTOSFieldNotice']);
                break;
            }
            if ($currentPage == 'legal' && isset($published[$current])) {
                $postId = $published[$current];
                add_action('admin_notices', function () use ($postId) {
                    $this->currentTOSEndpointOverwritten($postId);
                });
                break;
            }
        }
    }

    public function requiredTOSFieldNotice()
    {
        $link = sprintf(
            /* translators: 1: Url of the settings page, 2: Title of the settings page. */
            '<a href="%1$s">%2$s</a>',
            add_query_arg(
                ['page' => 'legal'],
                admin_url('admin.php')
            ),
            __('Legal Mandatory Information', 'rrze-legal')
        );
        $message = sprintf(
            /* translators: %s: Link of the settings page. */
            __('One or more mandatory fields of the legal settings have not been filled. Please fill in these fields as soon as possible in the following link: %s.', 'rrze-legal'),
            $link
        );
        echo "<div class='notice notice-warning is-dismissible'><p>{$message}</p></div>";
    }

    protected function currentTOSEndpointOverwritten(int $postId = 0)
    {
        $link = '<a href="' . get_permalink($postId) . '">' . get_the_title($postId) . '</a>';
        $message = sprintf(
            /* translators: %s: Permalink of the page that overrides the endpoint. */
            __('The output of this settings page is overwritten by the content of the following page: %s.', 'rrze-legal'),
            $link
        );
        echo "<div class='notice notice-warning is-dismissible'><p>{$message}</p></div>";
    }
}
