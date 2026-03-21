import i18n from '@/i18n';
import { format, formatDistanceToNow, formatDistanceToNowStrict } from 'date-fns';
import { enUS, ja } from 'date-fns/locale';

const frontendNamespaces = new Set(['activity', 'auth', 'strings', 'ui']);
const getDateLocale = () => (i18n.language.startsWith('ja') ? ja : enUS);
const useJapanesePatterns = () => i18n.language.startsWith('ja');
const statusKeyMap: Record<string, string> = {
    active: 'ui.server.statuses.active',
    connection_error: 'ui.server.statuses.connection_error',
    inactive: 'ui.server.statuses.inactive',
    installing: 'ui.server.statuses.installing',
    offline: 'ui.server.statuses.offline',
    processing: 'ui.server.statuses.processing',
    restoring_backup: 'ui.server.statuses.restoring_backup',
    running: 'ui.server.statuses.running',
    starting: 'ui.server.statuses.starting',
    stopping: 'ui.server.statuses.stopping',
    suspended: 'ui.server.statuses.suspended',
    transferring: 'ui.server.statuses.transferring',
    unavailable: 'ui.server.statuses.unavailable',
};

type TranslationOptions = Record<string, unknown> & {
    ns?: string;
};

const resolveTranslation = (key: string, options?: TranslationOptions) => {
    const separator = key.indexOf('.');

    if (separator === -1) {
        return { key, options };
    }

    const namespace = key.slice(0, separator);
    const resolvedKey = key.slice(separator + 1);

    if (!resolvedKey) {
        return { key, options };
    }

    if (typeof options?.ns === 'string' && options.ns === namespace) {
        return {
            key: resolvedKey,
            options,
        };
    }

    if (!options?.ns && frontendNamespaces.has(namespace)) {
        return {
            key: resolvedKey,
            options: { ...options, ns: namespace },
        };
    }

    return { key, options };
};

export const t = (key: string, options?: TranslationOptions) => {
    const resolved = resolveTranslation(key, options);

    return i18n.t(resolved.key, resolved.options) as string;
};

export const formatDateTime = (date: Date | number, englishPattern: string, japanesePattern?: string) =>
    format(date, useJapanesePatterns() ? japanesePattern || 'yyyy/MM/dd HH:mm' : englishPattern, {
        locale: getDateLocale(),
    });

export const translateServerStatus = (status?: string | null) => {
    if (!status) {
        return '';
    }

    return statusKeyMap[status] ? t(statusKeyMap[status]) : status.charAt(0).toUpperCase() + status.slice(1);
};

export const formatRelativeTime = (
    date: Date | number,
    options?: Omit<Parameters<typeof formatDistanceToNow>[1], 'locale'>
) => formatDistanceToNow(date, { locale: getDateLocale(), ...options });

export const formatRelativeTimeStrict = (
    date: Date | number,
    options?: Omit<Parameters<typeof formatDistanceToNowStrict>[1], 'locale'>
) => formatDistanceToNowStrict(date, { locale: getDateLocale(), ...options });
