<?php

return [
    'validation' => [
        'fqdn_not_resolvable' => '指定された FQDN または IP アドレスは有効な IP アドレスへ解決できません。',
        'fqdn_required_for_ssl' => 'このノードで SSL を利用するには、公開 IP アドレスへ解決される完全修飾ドメイン名が必要です。',
    ],
    'notices' => [
        'allocations_added' => 'このノードにアロケーションを追加しました。',
        'node_deleted' => 'ノードを Panel から削除しました。',
        'location_required' => 'この Panel にノードを追加する前に、少なくとも 1 つのロケーションを設定する必要があります。',
        'node_created' => '新しいノードを作成しました。このマシン上のデーモンは「Configuration」タブから自動設定できます。サーバーを追加する前に、少なくとも 1 つの IP アドレスとポートを割り当ててください。',
        'node_updated' => 'ノード情報を更新しました。デーモン設定を変更した場合は、反映のため再起動が必要です。',
        'unallocated_deleted' => '<code>:ip</code> の未割り当てポートをすべて削除しました。',
    ],
];
