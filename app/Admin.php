<?php

namespace Dplugins\Asura\Connector;

class Admin
{
    public function __construct()
    {
        add_action('admin_menu', [$this, 'admin_menu']);
    }

    public function admin_menu(): void
    {
        $capability = 'manage_options';

        if (current_user_can($capability)) {
            $hook = add_submenu_page(
                'ct_dashboard_page',
                __('Asura Connector', 'asura-connector'),
                __('Asura Connector', 'asura-connector'),
                $capability,
                Connector::$module_id,
                [
                    $this,
                    'plugin_page'
                ]
            );

            add_action('load-' . $hook, [$this, 'init_hooks']);
        }
    }

    public function init_hooks(): void
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
    }

    public function enqueue_scripts(): void
    {
        if (!isset($_GET['tab']) || $_GET['tab'] === 'dashboard') {
            wp_enqueue_style(Connector::$module_id . "-admin");
            wp_enqueue_script(Connector::$module_id . "-admin");
            wp_set_script_translations(Connector::$module_id . "-admin", 'asura-connector', dirname(ASURA_CONNECTOR_FILE) . '/languages/');
            wp_localize_script(
                Connector::$module_id . "-admin",
                'thelostasura',
                [
                    'ajax_url'    => admin_url('admin-ajax.php'),
                    'nonce'       => wp_create_nonce(Connector::$module_id . "-admin"),
                    'module_id'   => Connector::$module_id,
                    'web_history' => add_query_arg([
                        'page' => Connector::$module_id,
                        'tab'  => 'dashboard',
                    ], admin_url('admin.php'))
                ]
            );
        }
    }

    public function plugin_page(): void
    {
        $active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'dashboard';
?>
        <hr class="wp-header-end">
        <h2 class="nav-tab-wrapper">
            <a href="<?php echo add_query_arg([
                            'page' => Connector::$module_id,
                            'tab'  => 'dashboard#/provider',
                        ], admin_url('admin.php')); ?>" class="nav-tab <?php echo $active_tab == 'dashboard' ? 'nav-tab-active' : ''; ?>"> Dashboard </a>
            <a href="https://asura.docs.oxyrealm.com/guide/asura-connector.html" target="_blank" class="nav-tab" style="display: inline-flex;">
                Documentation
                <svg class="icon outbound" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" x="0px" y="0px" viewBox="0 0 100 100" width="15" height="15" data-v-641633f9="">
                    <path fill="currentColor" d="M18.8,85.1h56l0,0c2.2,0,4-1.8,4-4v-32h-8v28h-48v-48h28v-8h-32l0,0c-2.2,0-4,1.8-4,4v56C14.8,83.3,16.6,85.1,18.8,85.1z"></path>
                    <polygon fill="currentColor" points="45.7,48.7 51.3,54.3 77.2,28.5 77.2,37.2 85.2,37.2 85.2,14.9 62.8,14.9 62.8,22.9 71.5,22.9"></polygon>
                </svg>
            </a>
        </h2>
<?php
        switch ($active_tab) {
            case 'dashboard':
            default:
                $this->dashboard_tab();
                break;
        }
    }

    public function dashboard_tab(): void
    {
        echo '<div id="thelostasura-app"></div>';
    }
}
