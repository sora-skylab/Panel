<?php

return [
    'location' => [
        'no_location_found' => '指定されたショートコードに一致するレコードが見つかりませんでした。',
        'ask_short' => 'ロケーションのショートコード',
        'ask_long' => 'ロケーションの説明',
        'created' => '新しいロケーション (:name) を ID :id で作成しました。',
        'deleted' => '指定されたロケーションを削除しました。',
    ],
    'user' => [
        'search_users' => 'ユーザー名、ユーザー ID、またはメールアドレスを入力してください',
        'select_search_user' => '削除するユーザーの ID（再検索する場合は「0」を入力）',
        'deleted' => 'ユーザーを Panel から削除しました。',
        'confirm_delete' => 'このユーザーを Panel から削除してもよろしいですか？',
        'no_users_found' => '指定された検索語に一致するユーザーが見つかりませんでした。',
        'multiple_found' => '複数のアカウントが見つかったため、--no-interaction フラグではユーザーを削除できません。',
        'ask_admin' => 'このユーザーを管理者にしますか？',
        'ask_email' => 'メールアドレス',
        'ask_username' => 'ユーザー名',
        'ask_name_first' => '名',
        'ask_name_last' => '姓',
        'ask_password' => 'パスワード',
        'ask_password_tip' => 'ランダムパスワードをユーザーへメール送信するアカウントを作成したい場合は、このコマンドを再実行し（CTRL+C）、`--no-password` フラグを付けてください。',
        'ask_password_help' => 'パスワードは 8 文字以上で、少なくとも 1 つの大文字と数字を含める必要があります。',
        '2fa_help_text' => [
            'このコマンドは、ユーザーのアカウントで二要素認証が有効な場合にそれを無効化します。ユーザーがアカウントに入れなくなった際の復旧目的でのみ使用してください。',
            '意図した操作でない場合は、CTRL+C を押してこの処理を終了してください。',
        ],
        '2fa_disabled' => ':email の二要素認証を無効化しました。',
    ],
    'schedule' => [
        'output_line' => 'スケジュール `:schedule` (:hash) の最初のタスクに対するジョブをディスパッチしています。',
    ],
    'maintenance' => [
        'deleting_service_backup' => 'サービスバックアップファイル :file を削除しています。',
    ],
    'server' => [
        'rebuild_failed' => 'ノード ":node" 上の ":name" (#:id) に対する再ビルド要求がエラーで失敗しました: :message',
        'reinstall' => [
            'failed' => 'ノード ":node" 上の ":name" (#:id) に対する再インストール要求がエラーで失敗しました: :message',
            'confirm' => '複数のサーバーに対して再インストールを実行しようとしています。続行しますか？',
        ],
        'power' => [
            'confirm' => ':count 台のサーバーに対して :action を実行しようとしています。続行しますか？',
            'action_failed' => 'ノード ":node" 上の ":name" (#:id) に対する電源操作要求がエラーで失敗しました: :message',
        ],
    ],
    'environment' => [
        'mail' => [
            'ask_smtp_host' => 'SMTP ホスト（例: smtp.gmail.com）',
            'ask_smtp_port' => 'SMTP ポート',
            'ask_smtp_username' => 'SMTP ユーザー名',
            'ask_smtp_password' => 'SMTP パスワード',
            'ask_mailgun_domain' => 'Mailgun ドメイン',
            'ask_mailgun_endpoint' => 'Mailgun エンドポイント',
            'ask_mailgun_secret' => 'Mailgun シークレット',
            'ask_mandrill_secret' => 'Mandrill シークレット',
            'ask_postmark_username' => 'Postmark API キー',
            'ask_driver' => 'メール送信に使用するドライバーを選択してください',
            'ask_mail_from' => '送信元メールアドレス',
            'ask_mail_name' => '送信元として表示する名前',
            'ask_encryption' => '使用する暗号化方式',
        ],
    ],
];
