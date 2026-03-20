import React, { useEffect, useState } from 'react';
import { ServerContext } from '@/state/server';
import TitledGreyBox from '@/components/elements/TitledGreyBox';
import reinstallServer from '@/api/server/reinstallServer';
import { Actions, useStoreActions } from 'easy-peasy';
import { ApplicationStore } from '@/state';
import { httpErrorToHuman } from '@/api/http';
import tw from 'twin.macro';
import { Button } from '@/components/elements/button/index';
import { Dialog } from '@/components/elements/dialog';
import { t } from '@/lib/locale';

export default () => {
    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const [modalVisible, setModalVisible] = useState(false);
    const { addFlash, clearFlashes } = useStoreActions((actions: Actions<ApplicationStore>) => actions.flashes);

    const reinstall = () => {
        clearFlashes('settings');
        reinstallServer(uuid)
            .then(() => {
                addFlash({
                    key: 'settings',
                    type: 'success',
                    message: t('ui.server.settings.reinstall_started'),
                });
            })
            .catch((error) => {
                console.error(error);

                addFlash({ key: 'settings', type: 'error', message: httpErrorToHuman(error) });
            })
            .then(() => setModalVisible(false));
    };

    useEffect(() => {
        clearFlashes();
    }, []);

    return (
        <TitledGreyBox title={t('ui.server.settings.reinstall_server')} css={tw`relative`}>
            <Dialog.Confirm
                open={modalVisible}
                title={t('ui.server.settings.confirm_reinstall_title')}
                confirm={t('ui.server.settings.confirm_reinstall_button')}
                onClose={() => setModalVisible(false)}
                onConfirmed={reinstall}
            >
                {t('ui.server.settings.confirm_reinstall_description')}
            </Dialog.Confirm>
            <p css={tw`text-sm`}>
                {t('ui.server.settings.reinstall_description')}&nbsp;
                <strong css={tw`font-medium`}>{t('ui.server.settings.reinstall_warning')}</strong>
            </p>
            <div css={tw`mt-6 text-right`}>
                <Button.Danger variant={Button.Variants.Secondary} onClick={() => setModalVisible(true)}>
                    {t('ui.server.settings.reinstall_server')}
                </Button.Danger>
            </div>
        </TitledGreyBox>
    );
};
