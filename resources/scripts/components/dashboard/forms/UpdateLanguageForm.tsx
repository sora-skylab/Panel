import React, { useState } from 'react';
import { State, useStoreState } from 'easy-peasy';
import { ApplicationStore } from '@/state';
import Label from '@/components/elements/Label';
import Select from '@/components/elements/Select';
import tw from 'twin.macro';
import { Button } from '@/components/elements/button/index';
import { useLocation } from 'react-router-dom';
import { t } from '@/lib/locale';

export default () => {
    const user = useStoreState((state: State<ApplicationStore>) => state.user.data);
    const availableLanguages = useStoreState(
        (state: State<ApplicationStore>) => state.settings.data?.availableLanguages ?? {}
    );
    const [language, setLanguage] = useState(user?.language ?? 'en');
    const location = useLocation();

    const hasChanges = language !== user?.language;

    if (!user || Object.keys(availableLanguages).length === 0) {
        return null;
    }

    const submit = (event: React.FormEvent) => {
        event.preventDefault();

        if (!hasChanges) {
            return;
        }

        const params = new URLSearchParams(location.search);
        params.set('locale', language);
        window.location.assign(`${location.pathname}?${params.toString()}`);
    };

    return (
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
                <Button disabled={!hasChanges}>{t('ui.common.save')}</Button>
            </div>
        </form>
    );
};
