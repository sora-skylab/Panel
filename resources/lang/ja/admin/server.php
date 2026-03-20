<?php

return [
    'exceptions' => [
        'no_new_default_allocation' => 'このサーバーのデフォルトアロケーションを削除しようとしていますが、代替として使用できるアロケーションがありません。',
        'marked_as_failed' => 'このサーバーは以前のインストール失敗としてマークされています。この状態では現在のステータスを切り替えできません。',
        'bad_variable' => ':name 変数の検証でエラーが発生しました。',
        'daemon_exception' => 'デーモンとの通信中に例外が発生し、HTTP/:code の応答コードが返されました。この例外は記録されています。（request id: :request_id）',
        'default_allocation_not_found' => '要求されたデフォルトアロケーションは、このサーバーのアロケーション一覧に見つかりませんでした。',
    ],
    'alerts' => [
        'startup_changed' => 'このサーバーの起動設定を更新しました。このサーバーの Nest または Egg が変更された場合は、再インストールが行われます。',
        'server_deleted' => 'サーバーをシステムから削除しました。',
        'server_created' => 'Panel 上にサーバーを作成しました。デーモンがこのサーバーのインストールを完了するまで数分お待ちください。',
        'build_updated' => 'このサーバーのビルド詳細を更新しました。変更内容によっては反映のため再起動が必要です。',
        'suspension_toggled' => 'サーバーの停止状態を :status に変更しました。',
        'rebuild_on_boot' => 'このサーバーは Docker コンテナの再ビルドが必要な状態としてマークされました。次回サーバー起動時に実行されます。',
        'install_toggled' => 'このサーバーのインストール状態を切り替えました。',
        'server_reinstalled' => 'このサーバーは再インストール待ちに追加され、まもなく開始されます。',
        'details_updated' => 'サーバー詳細を更新しました。',
        'docker_image_updated' => 'このサーバーで使用するデフォルト Docker イメージを変更しました。変更を反映するには再起動が必要です。',
        'node_required' => 'この Panel にサーバーを追加する前に、少なくとも 1 つのノードを設定する必要があります。',
        'transfer_nodes_required' => 'サーバーを転送するには、少なくとも 2 つのノードを設定する必要があります。',
        'transfer_started' => 'サーバー転送を開始しました。',
        'transfer_not_viable' => '選択したノードには、このサーバーを収容するために必要なディスク容量またはメモリがありません。',
    ],
];
