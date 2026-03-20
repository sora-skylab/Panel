import React, { memo, useCallback } from 'react';
import { useField } from 'formik';
import TitledGreyBox from '@/components/elements/TitledGreyBox';
import tw from 'twin.macro';
import Input from '@/components/elements/Input';
import isEqual from 'react-fast-compare';
import { t } from '@/lib/locale';

interface Props {
    isEditable: boolean;
    title: string;
    permissions: string[];
    className?: string;
}

const groupTitleKeys: Record<string, string> = {
    allocation: 'ui.server.users.permission_groups.allocation',
    control: 'ui.server.users.permission_groups.control',
    database: 'ui.server.users.permission_groups.database',
    file: 'ui.server.users.permission_groups.file',
    schedule: 'ui.server.users.permission_groups.schedule',
    settings: 'ui.server.users.permission_groups.settings',
    startup: 'ui.server.users.permission_groups.startup',
    user: 'ui.server.users.permission_groups.user',
    websocket: 'ui.server.users.permission_groups.websocket',
};

const PermissionTitleBox: React.FC<Props> = memo(({ isEditable, title, permissions, className, children }) => {
    const [{ value }, , { setValue }] = useField<string[]>('permissions');

    const onCheckboxClicked = useCallback(
        (e: React.ChangeEvent<HTMLInputElement>) => {
            if (e.currentTarget.checked) {
                setValue([...value, ...permissions.filter((p) => !value.includes(p))]);
            } else {
                setValue(value.filter((p) => !permissions.includes(p)));
            }
        },
        [permissions, value]
    );

    return (
        <TitledGreyBox
            title={
                <div css={tw`flex items-center`}>
                    <p css={tw`text-sm uppercase flex-1`}>
                        {groupTitleKeys[title] ? t(groupTitleKeys[title]) : title}
                    </p>
                    {isEditable && (
                        <Input
                            type={'checkbox'}
                            checked={permissions.every((p) => value.includes(p))}
                            onChange={onCheckboxClicked}
                        />
                    )}
                </div>
            }
            className={className}
        >
            {children}
        </TitledGreyBox>
    );
}, isEqual);

export default PermissionTitleBox;
