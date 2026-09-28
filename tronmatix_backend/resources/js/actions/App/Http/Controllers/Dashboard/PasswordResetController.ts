import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Dashboard\PasswordResetController::showForgotForm
 * @see app/Http/Controllers/Dashboard/PasswordResetController.php:21
 * @route '/dashboard/password/email'
 */
export const showForgotForm = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: showForgotForm.url(options),
    method: 'get',
})

showForgotForm.definition = {
    methods: ["get","head"],
    url: '/dashboard/password/email',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Dashboard\PasswordResetController::showForgotForm
 * @see app/Http/Controllers/Dashboard/PasswordResetController.php:21
 * @route '/dashboard/password/email'
 */
showForgotForm.url = (options?: RouteQueryOptions) => {
    return showForgotForm.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\PasswordResetController::showForgotForm
 * @see app/Http/Controllers/Dashboard/PasswordResetController.php:21
 * @route '/dashboard/password/email'
 */
showForgotForm.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: showForgotForm.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Dashboard\PasswordResetController::showForgotForm
 * @see app/Http/Controllers/Dashboard/PasswordResetController.php:21
 * @route '/dashboard/password/email'
 */
showForgotForm.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: showForgotForm.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Dashboard\PasswordResetController::showForgotForm
 * @see app/Http/Controllers/Dashboard/PasswordResetController.php:21
 * @route '/dashboard/password/email'
 */
    const showForgotFormForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: showForgotForm.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Dashboard\PasswordResetController::showForgotForm
 * @see app/Http/Controllers/Dashboard/PasswordResetController.php:21
 * @route '/dashboard/password/email'
 */
        showForgotFormForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: showForgotForm.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Dashboard\PasswordResetController::showForgotForm
 * @see app/Http/Controllers/Dashboard/PasswordResetController.php:21
 * @route '/dashboard/password/email'
 */
        showForgotFormForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: showForgotForm.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    showForgotForm.form = showForgotFormForm
/**
* @see \App\Http\Controllers\Dashboard\PasswordResetController::sendResetLink
 * @see app/Http/Controllers/Dashboard/PasswordResetController.php:32
 * @route '/dashboard/password/email'
 */
export const sendResetLink = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: sendResetLink.url(options),
    method: 'post',
})

sendResetLink.definition = {
    methods: ["post"],
    url: '/dashboard/password/email',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Dashboard\PasswordResetController::sendResetLink
 * @see app/Http/Controllers/Dashboard/PasswordResetController.php:32
 * @route '/dashboard/password/email'
 */
sendResetLink.url = (options?: RouteQueryOptions) => {
    return sendResetLink.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\PasswordResetController::sendResetLink
 * @see app/Http/Controllers/Dashboard/PasswordResetController.php:32
 * @route '/dashboard/password/email'
 */
sendResetLink.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: sendResetLink.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Dashboard\PasswordResetController::sendResetLink
 * @see app/Http/Controllers/Dashboard/PasswordResetController.php:32
 * @route '/dashboard/password/email'
 */
    const sendResetLinkForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: sendResetLink.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Dashboard\PasswordResetController::sendResetLink
 * @see app/Http/Controllers/Dashboard/PasswordResetController.php:32
 * @route '/dashboard/password/email'
 */
        sendResetLinkForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: sendResetLink.url(options),
            method: 'post',
        })
    
    sendResetLink.form = sendResetLinkForm
/**
* @see \App\Http\Controllers\Dashboard\PasswordResetController::showResetForm
 * @see app/Http/Controllers/Dashboard/PasswordResetController.php:113
 * @route '/dashboard/password/reset/{token}'
 */
export const showResetForm = (args: { token: string | number } | [token: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: showResetForm.url(args, options),
    method: 'get',
})

showResetForm.definition = {
    methods: ["get","head"],
    url: '/dashboard/password/reset/{token}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Dashboard\PasswordResetController::showResetForm
 * @see app/Http/Controllers/Dashboard/PasswordResetController.php:113
 * @route '/dashboard/password/reset/{token}'
 */
showResetForm.url = (args: { token: string | number } | [token: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { token: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    token: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        token: args.token,
                }

    return showResetForm.definition.url
            .replace('{token}', parsedArgs.token.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\PasswordResetController::showResetForm
 * @see app/Http/Controllers/Dashboard/PasswordResetController.php:113
 * @route '/dashboard/password/reset/{token}'
 */
showResetForm.get = (args: { token: string | number } | [token: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: showResetForm.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Dashboard\PasswordResetController::showResetForm
 * @see app/Http/Controllers/Dashboard/PasswordResetController.php:113
 * @route '/dashboard/password/reset/{token}'
 */
showResetForm.head = (args: { token: string | number } | [token: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: showResetForm.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Dashboard\PasswordResetController::showResetForm
 * @see app/Http/Controllers/Dashboard/PasswordResetController.php:113
 * @route '/dashboard/password/reset/{token}'
 */
    const showResetFormForm = (args: { token: string | number } | [token: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: showResetForm.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Dashboard\PasswordResetController::showResetForm
 * @see app/Http/Controllers/Dashboard/PasswordResetController.php:113
 * @route '/dashboard/password/reset/{token}'
 */
        showResetFormForm.get = (args: { token: string | number } | [token: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: showResetForm.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Dashboard\PasswordResetController::showResetForm
 * @see app/Http/Controllers/Dashboard/PasswordResetController.php:113
 * @route '/dashboard/password/reset/{token}'
 */
        showResetFormForm.head = (args: { token: string | number } | [token: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: showResetForm.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    showResetForm.form = showResetFormForm
/**
* @see \App\Http\Controllers\Dashboard\PasswordResetController::resetPassword
 * @see app/Http/Controllers/Dashboard/PasswordResetController.php:125
 * @route '/dashboard/password/reset'
 */
export const resetPassword = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: resetPassword.url(options),
    method: 'post',
})

resetPassword.definition = {
    methods: ["post"],
    url: '/dashboard/password/reset',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Dashboard\PasswordResetController::resetPassword
 * @see app/Http/Controllers/Dashboard/PasswordResetController.php:125
 * @route '/dashboard/password/reset'
 */
resetPassword.url = (options?: RouteQueryOptions) => {
    return resetPassword.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\PasswordResetController::resetPassword
 * @see app/Http/Controllers/Dashboard/PasswordResetController.php:125
 * @route '/dashboard/password/reset'
 */
resetPassword.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: resetPassword.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Dashboard\PasswordResetController::resetPassword
 * @see app/Http/Controllers/Dashboard/PasswordResetController.php:125
 * @route '/dashboard/password/reset'
 */
    const resetPasswordForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: resetPassword.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Dashboard\PasswordResetController::resetPassword
 * @see app/Http/Controllers/Dashboard/PasswordResetController.php:125
 * @route '/dashboard/password/reset'
 */
        resetPasswordForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: resetPassword.url(options),
            method: 'post',
        })
    
    resetPassword.form = resetPasswordForm
const PasswordResetController = { showForgotForm, sendResetLink, showResetForm, resetPassword }

export default PasswordResetController