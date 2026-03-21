import React, { forwardRef, useEffect, useImperativeHandle, useMemo, useRef } from 'react';

type TurnstileRenderOptions = {
    sitekey: string;
    theme?: 'auto' | 'light' | 'dark';
    size?: 'normal' | 'flexible' | 'compact';
    appearance?: 'always' | 'execute' | 'interaction-only';
    execution?: 'render' | 'execute';
    callback?: (token: string) => void;
    'error-callback'?: (errorCode?: string) => void;
    'expired-callback'?: () => void;
    'timeout-callback'?: () => void;
};

type TurnstileApi = {
    ready: (callback: () => void) => void;
    render: (container: string | HTMLElement, options: TurnstileRenderOptions) => string;
    execute: (container: string) => void;
    reset: (widgetId: string) => void;
    remove: (widgetId: string) => void;
};

declare global {
    interface Window {
        turnstile?: TurnstileApi;
    }
}

export type TurnstileHandle = {
    execute: () => void;
    reset: () => void;
};

type Props = {
    siteKey: string;
    onVerify: (token: string) => void;
    onError?: (error: Error) => void;
    onExpire?: () => void;
    css?: any;
};

const SCRIPT_SOURCE = 'https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit';
const SCRIPT_SELECTOR = 'script[data-turnstile-loader="true"]';
const LOAD_TIMEOUT = 10000;
let scriptLoader: Promise<TurnstileApi> | null = null;

const loadTurnstile = (): Promise<TurnstileApi> => {
    if (typeof window === 'undefined') {
        return Promise.reject(new Error('Cloudflare Turnstile can only be used in the browser.'));
    }

    if (window.turnstile) {
        return Promise.resolve(window.turnstile);
    }

    if (!scriptLoader) {
        scriptLoader = new Promise<TurnstileApi>((resolve, reject) => {
            let settled = false;
            let timeoutId = 0;

            const finish = (callback: () => void) => {
                if (settled) {
                    return;
                }

                settled = true;
                window.clearTimeout(timeoutId);
                callback();
            };

            const fail = (message: string) => {
                if (script.isConnected && !window.turnstile) {
                    script.remove();
                }

                finish(() => reject(new Error(message)));
            };

            const resolveWhenReady = (attempts = 20) => {
                if (window.turnstile) {
                    window.turnstile.ready(() => finish(() => resolve(window.turnstile!)));
                    return;
                }

                if (attempts === 0) {
                    fail('Cloudflare Turnstile failed to initialize.');
                    return;
                }

                window.setTimeout(() => resolveWhenReady(attempts - 1), 50);
            };

            const existing = document.querySelector<HTMLScriptElement>(SCRIPT_SELECTOR);
            if (existing && !window.turnstile) {
                existing.remove();
            }

            const script =
                document.querySelector<HTMLScriptElement>(SCRIPT_SELECTOR) ?? document.createElement('script');

            const handleLoad = () => resolveWhenReady();
            const handleError = () => fail('Failed to load the Cloudflare Turnstile script.');

            timeoutId = window.setTimeout(() => fail('Cloudflare Turnstile did not finish loading.'), LOAD_TIMEOUT);

            if (window.turnstile) {
                resolveWhenReady();
                return;
            }

            script.addEventListener('load', handleLoad, { once: true });
            script.addEventListener('error', handleError, { once: true });

            if (!script.isConnected) {
                script.src = SCRIPT_SOURCE;
                script.async = true;
                script.defer = true;
                script.dataset.turnstileLoader = 'true';
                document.head.appendChild(script);
            }
        }).catch((error) => {
            scriptLoader = null;
            throw error;
        });
    }

    return scriptLoader ?? Promise.reject(new Error('Cloudflare Turnstile loader was not initialized.'));
};

export default forwardRef<TurnstileHandle, Props>(({ siteKey, onVerify, onError, onExpire, css }, ref) => {
    const widgetId = useRef<string | null>(null);
    const containerId = useMemo(() => `turnstile-${Math.random().toString(36).slice(2, 10)}`, []);
    const onVerifyRef = useRef(onVerify);
    const onErrorRef = useRef(onError);
    const onExpireRef = useRef(onExpire);

    useEffect(() => {
        onVerifyRef.current = onVerify;
        onErrorRef.current = onError;
        onExpireRef.current = onExpire;
    }, [onError, onExpire, onVerify]);

    useEffect(() => {
        let cancelled = false;

        loadTurnstile()
            .then((turnstile) => {
                if (cancelled || widgetId.current) {
                    return;
                }

                widgetId.current = turnstile.render(`#${containerId}`, {
                    sitekey: siteKey,
                    theme: 'auto',
                    size: 'normal',
                    appearance: 'always',
                    execution: 'render',
                    callback: (token) => onVerifyRef.current(token),
                    'error-callback': (errorCode) =>
                        onErrorRef.current?.(
                            new Error(`Cloudflare Turnstile validation failed${errorCode ? `: ${errorCode}` : '.'}`)
                        ),
                    'expired-callback': () => onExpireRef.current?.(),
                    'timeout-callback': () => onExpireRef.current?.(),
                });
            })
            .catch((error) => onErrorRef.current?.(error instanceof Error ? error : new Error(String(error))));

        return () => {
            cancelled = true;

            if (window.turnstile && widgetId.current) {
                window.turnstile.remove(widgetId.current);
                widgetId.current = null;
            }
        };
    }, [containerId, siteKey]);

    useImperativeHandle(ref, () => ({
        execute: () => {
            if (!window.turnstile || !widgetId.current) {
                throw new Error('Cloudflare Turnstile is not ready yet.');
            }

            window.turnstile.execute(`#${containerId}`);
        },
        reset: () => {
            if (window.turnstile && widgetId.current) {
                window.turnstile.reset(widgetId.current);
            }
        },
    }), [containerId]);

    return <div id={containerId} css={css} />;
});
