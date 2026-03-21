import React, { useState } from 'react';
import { State, useStoreState } from 'easy-peasy';
import Select from '@/components/elements/Select';
import { httpErrorToHuman } from '@/api/http';
import setLocalePreference from '@/api/setLocalePreference';
import { ApplicationStore } from '@/state';
import useFlash from '@/plugins/useFlash';
import tw from 'twin.macro';
import { t } from '@/lib/locale';

export default () => {
    const locale = useStoreState((state: State<ApplicationStore>) => state.settings.data?.locale ?? 'en');
    const availableLanguages = useStoreState(
        (state: State<ApplicationStore>) => state.settings.data?.availableLanguages ?? {}
    );
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [selectedLocale, setSelectedLocale] = useState(locale);
    const { clearFlashes, addFlash } = useFlash();

    if (Object.keys(availableLanguages).length < 2) {
        return null;
    }

    const onChange = (event: React.ChangeEvent<HTMLSelectElement>) => {
        const nextLocale = event.currentTarget.value;
        setSelectedLocale(nextLocale);

        if (nextLocale === locale) {
            return;
        }

        clearFlashes();
        setIsSubmitting(true);

        setLocalePreference(nextLocale)
            .then(() => window.location.reload())
            .catch((error) => {
                setSelectedLocale(locale);
                setIsSubmitting(false);
                addFlash({
                    type: 'error',
                    title: t('ui.common.error'),
                    message: httpErrorToHuman(error),
                });
            });
    };

    return (
        <div css={tw`flex justify-end mb-4 px-1`}>
            <div css={tw`w-40`}>
                <p css={tw`mb-2 text-xs uppercase tracking-wide text-neutral-400`}>{t('ui.auth.language_label')}</p>
                <Select value={selectedLocale} onChange={onChange} disabled={isSubmitting}>
                    {Object.entries(availableLanguages).map(([key, value]) => (
                        <option key={key} value={key}>
                            {value}
                        </option>
                    ))}
                </Select>
            </div>
        </div>
    );
};
