import React from 'react';
import { t } from '@/lib/locale';

export default ({ uptime }: { uptime: number }) => {
    const days = Math.floor(uptime / (24 * 60 * 60));
    const hours = Math.floor((Math.floor(uptime) / 60 / 60) % 24);
    const remainder = Math.floor(uptime - hours * 60 * 60);
    const minutes = Math.floor((remainder / 60) % 60);
    const seconds = remainder % 60;

    if (days > 0) {
        return <>{t('ui.server.details.uptime_days_short', { days, hours, minutes })}</>;
    }

    return <>{t('ui.server.details.uptime_short', { hours, minutes, seconds })}</>;
};
