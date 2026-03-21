import http from '@/api/http';
import getCaptchaPayload from '@/api/auth/getCaptchaPayload';
import { CaptchaProvider } from '@/types/captcha';

export interface LoginResponse {
    complete: boolean;
    intended?: string;
    confirmationToken?: string;
}

export interface LoginData {
    username: string;
    password: string;
    captchaProvider: CaptchaProvider;
    captchaData?: string | null;
}

export default ({ username, password, captchaProvider, captchaData }: LoginData): Promise<LoginResponse> => {
    return new Promise((resolve, reject) => {
        http.get('/sanctum/csrf-cookie')
            .then(() =>
                http.post('/auth/login', {
                    user: username,
                    password,
                    ...getCaptchaPayload(captchaProvider, captchaData),
                })
            )
            .then((response) => {
                if (!(response.data instanceof Object)) {
                    return reject(new Error('An error occurred while processing the login request.'));
                }

                return resolve({
                    complete: response.data.data.complete,
                    intended: response.data.data.intended || undefined,
                    confirmationToken: response.data.data.confirmation_token || undefined,
                });
            })
            .catch(reject);
    });
};
