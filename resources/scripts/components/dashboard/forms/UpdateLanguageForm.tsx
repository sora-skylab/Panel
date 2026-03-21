import React, { useState } from 'react';
import { Actions, State, useStoreActions, useStoreState } from 'easy-peasy';
import SpinnerOverlay from '@/components/elements/SpinnerOverlay';
import { ApplicationStore } from '@/state';
import Label from '@/components/elements/Label';
import Select from '@/components/elements/Select';
import tw from 'twin.macro';
import { Button } from '@/components/elements/button/index';
import { httpErrorToHuman } from '@/api/http';
import { t } from '@/lib/locale';

export default () => {
    const user = useStoreState((state: State<ApplicationStore>) => state.user.data);
    const availableLanguages = useStoreState(
        (state: State<ApplicationStore>) => state.settings.data?.availableLanguages ?? {}
    );
    const updateLanguage = useStoreActions((actions: Actions<ApplicationStore>) => actions.user.updateUserLanguage);
    const { clearFlashes, addFlash } = useStoreActions((actions: Actions<ApplicationStore>) => actions.flashes);
    const [language, setLanguage] = useState(user?.language ?? 'en');
    const [isSubmitting, setIsSubmitting] = useState(false);

    const hasChanges = language !== user?.language;

    if (!user || Object.keys(availableLanguages).length === 0) {
        return null;
    }

    const submit = (event: React.FormEvent) => {
        event.preventDefault();

        if (!hasChanges) {
            return;
        }

        clearFlashes('account:language');
        setIsSubmitting(true);

        updateLanguage(language)
            .then(() => window.location.reload())
            .catch((error) => {
                setIsSubmitting(false);
                addFlash({
                    type: 'error',
                    key: 'account:language',
                    title: t('ui.common.error'),
                    message: httpErrorToHuman(error),
                });
            });
    };

    return (
        <React.Fragment>
            <SpinnerOverlay size={'large'} visible={isSubmitting} />
            <form css={tw`m-0`} onSubmit={submit}>
                <Label htmlFor={'language'}>{t('ui.auth.language_label')}</Label>
                <Select id={'language'} value={language} onChange={(event) => setLanguage(event.currentTarget.value)}>
                    {Object.entries(availableLanguages).map(([key, value]) => (
                        <option key={key} value={key}>
                            {value}
                        </option>
                    ))}
                </Select>
                <p className={'input-help'}>{t('ui.dashboard.language_preferences_description')}</p>
                <div css={tw`mt-6`}>
                    <Button disabled={isSubmitting || !hasChanges}>{t('ui.common.save')}</Button>
                </div>
            </form>
        </React.Fragment>
    );
};
