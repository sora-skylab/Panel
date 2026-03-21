import React from 'react';
import { State, useStoreState } from 'easy-peasy';
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

    return (
        <div css={[tw`text-center`, css]}>
            <div css={tw`inline-flex flex-col items-center gap-3`}>
                <p css={tw`text-[11px] uppercase tracking-[0.2em] text-neutral-400`}>{t('ui.auth.language_label')}</p>
                <div css={tw`inline-flex flex-wrap justify-center gap-2 rounded-full bg-neutral-100 p-1`}>
                    {Object.entries(availableLanguages).map(([key, value]) => {
                        const isActive = key === locale;

                        return (
                            <button
                                key={key}
                                type={'button'}
                                onClick={() => {
                                    if (isActive) {
                                        return;
                                    }

                                    const params = new URLSearchParams(location.search);
                                    params.set('locale', key);
                                    window.location.assign(`${location.pathname}?${params.toString()}`);
                                }}
                                css={[
                                    tw`rounded-full px-3 py-1.5 text-xs font-medium transition-colors duration-150`,
                                    isActive
                                        ? tw`bg-primary-500 text-white shadow-sm`
                                        : tw`text-neutral-500 hover:bg-white hover:text-neutral-700`,
                                ]}
                            >
                                {value}
                            </button>
                        );
                    })}
                </div>
            </div>
        </div>
    );
};
