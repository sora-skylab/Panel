<?php

return [
    'daemon_connection_failed' => 'デーモンとの通信中に例外が発生し、HTTP/:code の応答コードが返されました。この例外は記録されています。',
    'node' => [
        'servers_attached' => 'ノードを削除するには、関連付けられたサーバーが存在してはいけません。',
        'daemon_off_config_updated' => 'デーモン設定は更新されましたが、デーモン上の設定ファイルを自動更新する際にエラーが発生しました。変更を反映するにはデーモンの設定ファイル（config.yml）を手動で更新してください。',
    ],
    'allocations' => [
        'server_using' => '現在このアロケーションはサーバーに割り当てられています。サーバーが割り当てられていない場合にのみ削除できます。',
        'too_many_ports' => '1 回の範囲追加で 1000 個を超えるポートを追加することはサポートされていません。',
        'invalid_mapping' => ':port に対して指定されたマッピングは無効で、処理できませんでした。',
        'cidr_out_of_range' => 'CIDR 表記で指定できるマスクは /25 から /32 までです。',
        'port_out_of_range' => 'アロケーションのポートは 1024 より大きく、65535 以下である必要があります。',
    ],
    'nest' => [
        'delete_has_servers' => '稼働中のサーバーが紐付いた Nest は Panel から削除できません。',
        'egg' => [
            'delete_has_servers' => '稼働中のサーバーが紐付いた Egg は Panel から削除できません。',
            'invalid_copy_id' => 'スクリプトのコピー元として選択された Egg は存在しないか、自身のスクリプトをコピーしようとしています。',
            'must_be_child' => 'この Egg の「Copy Settings From」は、選択した Nest の子オプションである必要があります。',
            'has_children' => 'この Egg は 1 つ以上の子 Egg の親です。削除する前にそれらの Egg を削除してください。',
        ],
        'variables' => [
            'env_not_unique' => '環境変数 :name はこの Egg 内で一意である必要があります。',
            'reserved_name' => '環境変数 :name は保護されており、変数へ割り当てできません。',
            'bad_validation_rule' => 'バリデーションルール ":rule" はこのアプリケーションで有効なルールではありません。',
        ],
        'importer' => [
            'json_error' => 'JSON ファイルの解析中にエラーが発生しました: :error。',
            'file_error' => '指定された JSON ファイルは有効ではありません。',
            'invalid_json_provided' => '指定された JSON ファイルは認識可能な形式ではありません。',
        ],
    ],
    'subusers' => [
        'editing_self' => '自身のサブユーザーアカウントを編集することはできません。',
        'user_is_owner' => 'このサーバーの所有者をサブユーザーとして追加することはできません。',
        'subuser_exists' => 'そのメールアドレスのユーザーは既にこのサーバーのサブユーザーとして割り当てられています。',
    ],
    'databases' => [
        'delete_has_databases' => '稼働中のデータベースが紐付いたデータベースホストサーバーは削除できません。',
    ],
    'tasks' => [
        'chain_interval_too_long' => '連鎖タスクの最大間隔は 15 分です。',
    ],
    'locations' => [
        'has_nodes' => '稼働中のノードが紐付いたロケーションは削除できません。',
    ],
    'users' => [
        'node_revocation_failed' => '<a href=":link">Node #:node</a> のキー無効化に失敗しました。:error',
    ],
    'deployment' => [
        'no_viable_nodes' => '自動デプロイの要件を満たすノードが見つかりませんでした。',
        'no_viable_allocations' => '自動デプロイの要件を満たすアロケーションが見つかりませんでした。',
    ],
    'api' => [
        'resource_not_found' => '要求されたリソースはこのサーバー上に存在しません。',
    ],
];
