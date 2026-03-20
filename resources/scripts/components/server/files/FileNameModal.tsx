import React from 'react';
import Modal, { RequiredModalProps } from '@/components/elements/Modal';
import { Form, Formik, FormikHelpers } from 'formik';
import { object, string } from 'yup';
import Field from '@/components/elements/Field';
import { ServerContext } from '@/state/server';
import { join } from 'pathe';
import tw from 'twin.macro';
import Button from '@/components/elements/Button';
import { t } from '@/lib/locale';

type Props = RequiredModalProps & {
    onFileNamed: (name: string) => void;
};

interface Values {
    fileName: string;
}

export default ({ onFileNamed, onDismissed, ...props }: Props) => {
    const directory = ServerContext.useStoreState((state) => state.files.directory);

    const submit = (values: Values, { setSubmitting }: FormikHelpers<Values>) => {
        onFileNamed(join(directory, values.fileName));
        setSubmitting(false);
    };

    return (
        <Formik
            onSubmit={submit}
            initialValues={{ fileName: '' }}
            validationSchema={object().shape({
                fileName: string().required(t('ui.server.files.validation.file_name_required')).min(1),
            })}
        >
            {({ resetForm }) => (
                <Modal
                    onDismissed={() => {
                        resetForm();
                        onDismissed();
                    }}
                    {...props}
                >
                    <Form>
                        <Field
                            id={'fileName'}
                            name={'fileName'}
                            label={t('ui.server.files.file_name')}
                            description={t('ui.server.files.file_name_description')}
                            autoFocus
                        />
                        <div css={tw`mt-6 text-right`}>
                            <Button>{t('ui.server.files.create_file')}</Button>
                        </div>
                    </Form>
                </Modal>
            )}
        </Formik>
    );
};
