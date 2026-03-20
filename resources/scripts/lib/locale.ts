import i18n from '@/i18n';
import { format, formatDistanceToNow, formatDistanceToNowStrict } from 'date-fns';
import { enUS, ja } from 'date-fns/locale';

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

export const t = (key: string, options?: Record<string, unknown>) => i18n.t(key, options) as string;

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
