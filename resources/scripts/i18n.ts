import i18n from 'i18next';
import { Resource } from 'i18next';
import { initReactI18next } from 'react-i18next';
import I18NextHttpBackend, { HttpBackendOptions } from 'i18next-http-backend';
import I18NextMultiloadBackendAdapter from 'i18next-multiload-backend-adapter';

const frontendNamespaces = ['activity', 'auth', 'strings', 'ui'] as const;

type BootstrapWindow = Window & {
    PterodactylUser?: {
        language?: string;
    };
    SiteConfiguration?: {
        locale?: string;
    };
    PterodactylLocaleData?: Resource;
};

// If we're using HMR use a unique hash per page reload so that we're always
// doing cache busting. Otherwise just use the builder provided hash value in
// the URL to allow cache busting to occur whenever the front-end is rebuilt.
const hash = module.hot ? Date.now().toString(16) : process.env.WEBPACK_BUILD_HASH;
const bootstrapWindow = typeof window === 'undefined' ? undefined : (window as BootstrapWindow);
const initialLocale = bootstrapWindow?.PterodactylUser?.language || bootstrapWindow?.SiteConfiguration?.locale || 'en';
const preloadedResources = bootstrapWindow?.PterodactylLocaleData;

i18n.use(I18NextMultiloadBackendAdapter)
    .use(initReactI18next)
    .init({
        debug: process.env.DEBUG === 'true',
        lng: initialLocale,
        fallbackLng: 'en',
        defaultNS: 'ui',
        ns: frontendNamespaces as unknown as string[],
        keySeparator: '.',
        partialBundledLanguages: true,
        resources: preloadedResources,
        backend: {
            backend: I18NextHttpBackend,
            backendOption: {
                loadPath: '/locales/locale.json?locale={{lng}}&namespace={{ns}}',
                queryStringParams: { hash },
                allowMultiLoading: true,
            } as HttpBackendOptions,
        } as Record<string, any>,
        interpolation: {
            // Per i18n-react documentation: this is not needed since React is already
            // handling escapes for us.
            escapeValue: false,
        },
        react: {
            useSuspense: false,
        },
    });

export default i18n;
