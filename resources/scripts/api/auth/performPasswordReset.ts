import http from '@/api/http';
import getCaptchaPayload from '@/api/auth/getCaptchaPayload';
import { CaptchaProvider } from '@/types/captcha';

interface Data {
    token: string;
    password: string;
    passwordConfirmation: string;
}

interface PasswordResetResponse {
    redirectTo?: string | null;
    sendToLogin: boolean;
}

export default (
    email: string,
    data: Data,
    captchaProvider: CaptchaProvider,
    captchaData?: string | null
): Promise<PasswordResetResponse> => {
    return new Promise((resolve, reject) => {
        http.post('/auth/password/reset', {
            email,
            token: data.token,
            password: data.password,
            password_confirmation: data.passwordConfirmation,
            ...getCaptchaPayload(captchaProvider, captchaData),
        })
            .then((response) =>
                resolve({
                    redirectTo: response.data.redirect_to,
                    sendToLogin: response.data.send_to_login,
                })
            )
            .catch(reject);
    });
};
