import React from 'react';
import { Route } from 'react-router-dom';
import { RouteProps } from 'react-router';
import Can from '@/components/elements/Can';
import { ServerError } from '@/components/elements/ScreenBlock';
import { t } from '@/lib/locale';

interface Props extends Omit<RouteProps, 'path'> {
    path: string;
    permission: string | string[] | null;
}

export default ({ permission, children, ...props }: Props) => (
    <Route {...props}>
        {!permission ? (
            children
        ) : (
            <Can
                matchAny
                action={permission}
                renderOnError={
                    <ServerError
                        title={t('ui.common.access_denied')}
                        message={t('ui.common.permission_denied_message')}
                    />
                }
            >
                {children}
            </Can>
        )}
    </Route>
);
