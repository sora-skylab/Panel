import * as React from 'react';
import { useEffect, useRef, useState } from 'react';
import { Link } from 'react-router-dom';
import requestPasswordResetEmail from '@/api/auth/requestPasswordResetEmail';
import { httpErrorToHuman } from '@/api/http';
import LoginFormContainer from '@/components/auth/LoginFormContainer';
import { useStoreState } from 'easy-peasy';
import Field from '@/components/elements/Field';
import { Formik, FormikHelpers } from 'formik';
import { object, string } from 'yup';
import tw from 'twin.macro';
import Button from '@/components/elements/Button';
import Reaptcha from 'reaptcha';
import Turnstile, { TurnstileHandle } from '@/components/elements/turnstile/Turnstile';
import useFlash from '@/plugins/useFlash';
import { t } from '@/lib/locale';

interface Values {
    email: string;
}

export default () => {
    const recaptchaRef = useRef<Reaptcha>(null);
    const turnstileRef = useRef<TurnstileHandle>(null);
    const [token, setToken] = useState('');

    const { clearFlashes, addFlash } = useFlash();
    const { enabled: captchaEnabled, provider: captchaProvider, siteKey } = useStoreState(
        (state) => state.settings.data!.recaptcha
    );

    useEffect(() => {
        clearFlashes();
    }, []);

    const handleSubmission = ({ email }: Values, { setSubmitting, resetForm }: FormikHelpers<Values>) => {
        clearFlashes();

        // If there is no token in the state yet, request the token and then abort this submit request
        // since it will be re-submitted when the captcha data is returned by the component.
        if (captchaEnabled && !token) {
            if (captchaProvider === 'turnstile') {
                setSubmitting(false);
                addFlash({ type: 'error', title: t('ui.common.error'), message: t('strings.captcha_invalid', { ns: 'strings' }) });

                return;
            }

            try {
                recaptchaRef.current!.execute().catch((error) => {
                    console.error(error);

                    setSubmitting(false);
                    addFlash({ type: 'error', title: t('ui.common.error'), message: httpErrorToHuman(error) });
                });
            } catch (error) {
                console.error(error);

                setSubmitting(false);
                addFlash({
                    type: 'error',
                    title: t('ui.common.error'),
                    message: httpErrorToHuman(error instanceof Error ? error : new Error(String(error))),
                });
            }

            return;
        }

        requestPasswordResetEmail(email, captchaProvider, token)
            .then((response) => {
                resetForm();
                addFlash({ type: 'success', title: t('ui.common.success'), message: response });
            })
            .catch((error) => {
                console.error(error);
                addFlash({ type: 'error', title: t('ui.common.error'), message: httpErrorToHuman(error) });
            })
            .then(() => {
                setToken('');
                if (captchaProvider === 'turnstile') {
                    turnstileRef.current?.reset();
                } else if (recaptchaRef.current) {
                    recaptchaRef.current.reset();
                }

                setSubmitting(false);
            });
    };

    return (
        <Formik
            onSubmit={handleSubmission}
            initialValues={{ email: '' }}
            validationSchema={object().shape({
                email: string()
                    .email(t('ui.auth.validation.valid_email_required'))
                    .required(t('ui.auth.validation.valid_email_required')),
            })}
        >
            {({ isSubmitting, setSubmitting, submitForm }) => (
                <LoginFormContainer title={t('ui.auth.request_password_reset')} css={tw`w-full flex`}>
                    <Field
                        light
                        label={t('ui.auth.email')}
                        description={t('auth.forgot_password.label_help', { ns: 'auth' })}
                        name={'email'}
                        type={'email'}
                    />
                    <div css={tw`mt-6`}>
                        <Button type={'submit'} size={'xlarge'} disabled={isSubmitting} isLoading={isSubmitting}>
                            {t('ui.auth.send_email')}
                        </Button>
                    </div>
                    {captchaEnabled && captchaProvider === 'recaptcha' && (
                        <Reaptcha
                            ref={recaptchaRef}
                            size={'invisible'}
                            sitekey={siteKey || '_invalid_key'}
                            onVerify={(response) => {
                                setToken(response);
                                submitForm();
                            }}
                            onExpire={() => {
                                setSubmitting(false);
                                setToken('');
                            }}
                        />
                    )}
                    {captchaEnabled && captchaProvider === 'turnstile' && (
                        <Turnstile
                            ref={turnstileRef}
                            siteKey={siteKey || '_invalid_key'}
                            onVerify={(response) => {
                                setToken(response);
                            }}
                            onExpire={() => {
                                setSubmitting(false);
                                setToken('');
                            }}
                            onError={(error) => {
                                console.error(error);
                                setSubmitting(false);
                                addFlash({ type: 'error', title: t('ui.common.error'), message: httpErrorToHuman(error) });
                            }}
                            css={tw`mt-6`}
                        />
                    )}
                    <div css={tw`mt-6 text-center`}>
                        <Link
                            to={'/auth/login'}
                            css={tw`text-xs text-neutral-500 tracking-wide uppercase no-underline hover:text-neutral-700`}
                        >
                            {t('ui.auth.return_to_login')}
                        </Link>
                    </div>
                </LoginFormContainer>
            )}
        </Formik>
    );
};
