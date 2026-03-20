import styled from 'styled-components/macro';
import tw from 'twin.macro';
import Checkbox from '@/components/elements/Checkbox';
import React from 'react';
import { useStoreState } from 'easy-peasy';
import Label from '@/components/elements/Label';
import { t } from '@/lib/locale';

const Container = styled.label`
    ${tw`flex items-center border border-transparent rounded md:p-2 transition-colors duration-75`};
    text-transform: none;

    &:not(.disabled) {
        ${tw`cursor-pointer`};

        &:hover {
            ${tw`border-neutral-500 bg-neutral-800`};
        }
    }

    &:not(:first-of-type) {
        ${tw`mt-4 sm:mt-2`};
    }

    &.disabled {
        ${tw`opacity-50`};

        & input[type='checkbox']:not(:checked) {
            ${tw`border-0`};
        }
    }
`;

interface Props {
    permission: string;
    disabled: boolean;
}

const permissionActionKeys: Record<string, string> = {
    archive: 'ui.server.users.permission_actions.archive',
    connect: 'ui.server.users.permission_actions.connect',
    console: 'ui.server.users.permission_actions.console',
    create: 'ui.server.users.permission_actions.create',
    delete: 'ui.server.users.permission_actions.delete',
    kill: 'ui.server.users.permission_actions.kill',
    read: 'ui.server.users.permission_actions.read',
    restart: 'ui.server.users.permission_actions.restart',
    sftp: 'ui.server.users.permission_actions.sftp',
    start: 'ui.server.users.permission_actions.start',
    stop: 'ui.server.users.permission_actions.stop',
    update: 'ui.server.users.permission_actions.update',
    view_password: 'ui.server.users.permission_actions.view_password',
};

const PermissionRow = ({ permission, disabled }: Props) => {
    const [key, pkey] = permission.split('.', 2);
    const permissions = useStoreState((state) => state.permissions.data);

    return (
        <Container htmlFor={`permission_${permission}`} className={disabled ? 'disabled' : undefined}>
            <div css={tw`p-2`}>
                <Checkbox
                    id={`permission_${permission}`}
                    name={'permissions'}
                    value={permission}
                    css={tw`w-5 h-5 mr-2`}
                    disabled={disabled}
                />
            </div>
            <div css={tw`flex-1`}>
                <Label as={'p'} css={tw`font-medium`}>
                    {permissionActionKeys[pkey] ? t(permissionActionKeys[pkey]) : pkey}
                </Label>
                {permissions[key].keys[pkey].length > 0 && (
                    <p css={tw`text-xs text-neutral-400 mt-1`}>{permissions[key].keys[pkey]}</p>
                )}
            </div>
        </Container>
    );
};

export default PermissionRow;
