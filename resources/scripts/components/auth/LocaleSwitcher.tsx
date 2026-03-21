import React from 'react';
import { State, useStoreState } from 'easy-peasy';
import Select from '@/components/elements/Select';
import { ApplicationStore } from '@/state';
import { useLocation } from 'react-router-dom';
import tw from 'twin.macro';
import { t } from '@/lib/locale';

type Props = {
    css?: any;
};

export default ({ css }: Props) => {
    const locale = useStoreState((state: State<ApplicationStore>) => state.settings.data?.locale ?? 'en');
    const availableLanguages = useStoreState(
        (state: State<ApplicationStore>) => state.settings.data?.availableLanguages ?? {}
    );
    const location = useLocation();

    if (Object.keys(availableLanguages).length < 2) {
        return null;
    }

    const onChange = (event: React.ChangeEvent<HTMLSelectElement>) => {
        const nextLocale = event.currentTarget.value;

        if (nextLocale === locale) {
            return;
        }

        const params = new URLSearchParams(location.search);
        params.set('locale', nextLocale);
        window.location.assign(`${location.pathname}?${params.toString()}`);
    };

    return (
        <div css={[tw`text-center`, css]}>
            <div css={tw`w-40 mx-auto`}>
                <p css={tw`mb-2 text-xs uppercase tracking-wide text-neutral-400`}>{t('ui.auth.language_label')}</p>
                <Select value={locale} onChange={onChange}>
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
