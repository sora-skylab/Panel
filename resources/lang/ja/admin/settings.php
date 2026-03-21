<?php

return [
    'common' => [
        'admin' => '管理',
        'save' => '保存',
    ],
    'tabs' => [
        'general' => '一般',
        'mail' => 'メール',
        'advanced' => '詳細',
    ],
    'notice' => [
        'environment_only' => '現在の Panel は環境変数のみから設定を読み込むようになっています。設定を動的に読み込むには、環境ファイルで <code>APP_ENVIRONMENT_ONLY=false</code> を設定してください。',
    ],
    'general' => [
        'page_title' => 'Panel 設定',
        'page_description' => 'Pterodactyl の基本設定を行います。',
        'box_title' => 'Panel 設定',
        'company_name' => '会社名',
        'company_name_description' => 'Panel 全体およびクライアントに送信されるメールで使用される名称です。',
        'require_2fa' => '2段階認証を必須にする',
        'not_required' => '必須にしない',
        'admin_only' => '管理者のみ',
        'all_users' => '全ユーザー',
        'require_2fa_description' => '有効にすると、選択した範囲のアカウントは Panel を利用するために2段階認証を有効化している必要があります。',
        'default_language' => '既定の言語',
        'default_language_description' => 'UI コンポーネントの表示に使用する既定言語です。',
    ],
    'notices' => [
        'settings_updated' => 'Panel 設定を更新し、変更を反映するためにキューワーカーを再起動しました。',
    ],
];
