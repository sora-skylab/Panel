import { action, Action } from 'easy-peasy';
import { CaptchaProvider } from '@/types/captcha';

export interface SiteSettings {
    name: string;
    footerCustomHtml: string;
    locale: string;
    availableLanguages: Record<string, string>;
    recaptcha: {
        enabled: boolean;
        provider: CaptchaProvider;
        siteKey: string;
    };
}

export interface SettingsStore {
    data?: SiteSettings;
    setSettings: Action<SettingsStore, SiteSettings>;
}

const settings: SettingsStore = {
    data: undefined,

    setSettings: action((state, payload) => {
        state.data = payload;
    }),
};

export default settings;
