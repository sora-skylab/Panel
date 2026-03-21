<?php

return [
    'common' => [
        'admin' => 'Admin',
        'save' => 'Save',
    ],
    'tabs' => [
        'general' => 'General',
        'mail' => 'Mail',
        'advanced' => 'Advanced',
    ],
    'notice' => [
        'environment_only' => 'Your Panel is currently configured to read settings from the environment only. You will need to set <code>APP_ENVIRONMENT_ONLY=false</code> in your environment file in order to load settings dynamically.',
    ],
    'general' => [
        'page_title' => 'Panel Settings',
        'page_description' => 'Configure Pterodactyl to your liking.',
        'box_title' => 'Panel Settings',
        'company_name' => 'Company Name',
        'company_name_description' => 'This is the name that is used throughout the panel and in emails sent to clients.',
        'require_2fa' => 'Require 2-Factor Authentication',
        'not_required' => 'Not Required',
        'admin_only' => 'Admin Only',
        'all_users' => 'All Users',
        'require_2fa_description' => 'If enabled, any account falling into the selected grouping will be required to have 2-Factor authentication enabled to use the Panel.',
        'default_language' => 'Default Language',
        'default_language_description' => 'The default language to use when rendering UI components.',
    ],
    'notices' => [
        'settings_updated' => 'Panel settings have been updated successfully and the queue worker was restarted to apply these changes.',
    ],
];
