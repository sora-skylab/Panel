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
const READY_TIMEOUT = 60000;
const READY_RETRY_DELAY = 100;
let scriptLoader: Promise<TurnstileApi> | null = null;

const getOrCreateScript = (): HTMLScriptElement => {
    const existing = document.querySelector<HTMLScriptElement>(SCRIPT_SELECTOR);
    if (existing && existing.dataset.turnstileStatus !== 'failed') {
        return existing;
    }

    existing?.remove();

    const script = document.createElement('script');
    script.src = SCRIPT_SOURCE;
    script.async = true;
    script.defer = true;
    script.dataset.turnstileLoader = 'true';
    script.dataset.turnstileStatus = 'loading';
    document.head.appendChild(script);

    return script;
};

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
            const deadline = Date.now() + READY_TIMEOUT;
            const script = getOrCreateScript();

            const finish = (callback: () => void) => {
                if (settled) {
                    return;
                }

                settled = true;
                script.removeEventListener('load', handleLoad);
                script.removeEventListener('error', handleError);
                callback();
            };

            const fail = (message: string) => {
                finish(() => reject(new Error(message)));
            };

            const resolveWhenReady = (deadline = Date.now() + READY_TIMEOUT) => {
                if (window.turnstile) {
                    script.dataset.turnstileStatus = 'loaded';
                    window.turnstile.ready(() => finish(() => resolve(window.turnstile!)));
                    return;
                }

                if (script.dataset.turnstileStatus === 'failed') {
                    fail('Failed to load the Cloudflare Turnstile script.');
                    return;
                }

                if (Date.now() >= deadline) {
                    fail('Cloudflare Turnstile did not finish loading.');
                    return;
                }

                window.setTimeout(() => resolveWhenReady(deadline), READY_RETRY_DELAY);
            };

            function handleLoad() {
                script.dataset.turnstileStatus = 'loaded';
                resolveWhenReady(deadline);
            }

            function handleError() {
                script.dataset.turnstileStatus = 'failed';
                fail('Failed to load the Cloudflare Turnstile script.');
            }

            script.addEventListener('load', handleLoad);
            script.addEventListener('error', handleError);
            resolveWhenReady(deadline);
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
    const containerRef = useRef<HTMLDivElement | null>(null);
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
                const container = containerRef.current;

                if (cancelled || widgetId.current || !container) {
                    return;
                }

                widgetId.current = turnstile.render(container, {
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

    return <div id={containerId} ref={containerRef} css={css} />;
});
