import React from 'react';
import { ServerContext } from '@/state/server';
import ScreenBlock from '@/components/elements/ScreenBlock';
import ServerInstallSvg from '@/assets/images/server_installing.svg';
import ServerErrorSvg from '@/assets/images/server_error.svg';
import ServerRestoreSvg from '@/assets/images/server_restore.svg';
import { t } from '@/lib/locale';

export default () => {
    const status = ServerContext.useStoreState((state) => state.server.data?.status || null);
    const isTransferring = ServerContext.useStoreState((state) => state.server.data?.isTransferring || false);
    const isNodeUnderMaintenance = ServerContext.useStoreState(
        (state) => state.server.data?.isNodeUnderMaintenance || false
    );

    return status === 'installing' || status === 'install_failed' || status === 'reinstall_failed' ? (
        <ScreenBlock
            title={t('ui.server.conflict.running_installer')}
            image={ServerInstallSvg}
            message={t('ui.server.conflict.installer_message')}
        />
    ) : status === 'suspended' ? (
        <ScreenBlock
            title={t('ui.server.conflict.server_suspended')}
            image={ServerErrorSvg}
            message={t('ui.server.conflict.suspended_message')}
        />
    ) : isNodeUnderMaintenance ? (
        <ScreenBlock
            title={t('ui.server.conflict.node_under_maintenance')}
            image={ServerErrorSvg}
            message={t('ui.server.conflict.node_under_maintenance_message')}
        />
    ) : (
        <ScreenBlock
            title={
                isTransferring
                    ? t('ui.server.statuses.transferring')
                    : t('ui.server.conflict.restoring_from_backup')
            }
            image={ServerRestoreSvg}
            message={
                isTransferring
                    ? t('ui.server.conflict.transferring_message')
                    : t('ui.server.conflict.restoring_message')
            }
        />
    );
};
