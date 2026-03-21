import React, { useEffect, useRef, useState } from 'react';
import { Link, RouteComponentProps } from 'react-router-dom';
import login from '@/api/auth/login';
import LoginFormContainer from '@/components/auth/LoginFormContainer';
import { useStoreState } from 'easy-peasy';
import { Formik, FormikHelpers } from 'formik';
import { object, string } from 'yup';
import Field from '@/components/elements/Field';
import tw from 'twin.macro';
import Button from '@/components/elements/Button';
import Reaptcha from 'reaptcha';
import Turnstile, { TurnstileHandle } from '@/components/elements/turnstile/Turnstile';
import useFlash from '@/plugins/useFlash';
import { t } from '@/lib/locale';
import LocaleSwitcher from '@/components/auth/LocaleSwitcher';

interface Values {
    username: string;
    password: string;
}

const LoginContainer = ({ history }: RouteComponentProps) => {
    const recaptchaRef = useRef<Reaptcha>(null);
    const turnstileRef = useRef<TurnstileHandle>(null);
    const [token, setToken] = useState('');

    const { addFlash, clearFlashes, clearAndAddHttpError } = useFlash();
    const { enabled: captchaEnabled, provider: captchaProvider, siteKey } = useStoreState(
        (state) => state.settings.data!.recaptcha
    );

    useEffect(() => {
        clearFlashes();
    }, []);

    const onSubmit = (values: Values, { setSubmitting }: FormikHelpers<Values>) => {
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
                    clearAndAddHttpError({ error });
                });
            } catch (error) {
                console.error(error);

                setSubmitting(false);
                clearAndAddHttpError({ error: error instanceof Error ? error : new Error(String(error)) });
            }

            return;
        }

        login({ ...values, captchaProvider, captchaData: token })
            .then((response) => {
                if (response.complete) {
                    // @ts-expect-error this is valid
                    window.location = response.intended || '/';
                    return;
                }

                history.replace('/auth/login/checkpoint', { token: response.confirmationToken });
            })
            .catch((error) => {
                console.error(error);

                setToken('');
                if (captchaProvider === 'turnstile') {
                    turnstileRef.current?.reset();
                } else if (recaptchaRef.current) {
                    recaptchaRef.current.reset();
                }

                setSubmitting(false);
                clearAndAddHttpError({ error });
            });
    };

    return (
        <Formik
            onSubmit={onSubmit}
            initialValues={{ username: '', password: '' }}
            validationSchema={object().shape({
                username: string().required(t('ui.auth.validation.username_or_email_required')),
                password: string().required(t('ui.auth.validation.password_required')),
            })}
        >
            {({ isSubmitting, setSubmitting, submitForm }) => (
                <LoginFormContainer title={t('ui.auth.login_title')} css={tw`w-full flex`}>
                    <Field
                        light
                        type={'text'}
                        label={t('ui.auth.username_or_email')}
                        name={'username'}
                        disabled={isSubmitting}
                    />
                    <div css={tw`mt-6`}>
                        <Field
                            light
                            type={'password'}
                            label={t('strings.password', { ns: 'strings' })}
                            name={'password'}
                            disabled={isSubmitting}
                        />
                    </div>
                    <div css={tw`mt-6`}>
                        <Button type={'submit'} size={'xlarge'} isLoading={isSubmitting} disabled={isSubmitting}>
                            {t('ui.auth.login_button')}
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
                        <div css={tw`mt-5`}>
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
                                    clearAndAddHttpError({ error });
                                }}
                            />
                        </div>
                    )}
                    <div css={tw`mt-6 text-center`}>
                        <Link
                            to={'/auth/password'}
                            css={tw`text-xs text-neutral-500 tracking-wide no-underline uppercase hover:text-neutral-600`}
                        >
                            {t('ui.auth.forgot_password')}
                        </Link>
                    </div>
                    <div css={tw`mt-6 pt-1`}>
                        <LocaleSwitcher />
                    </div>
                </LoginFormContainer>
            )}
        </Formik>
    );
};

export default LoginContainer;
