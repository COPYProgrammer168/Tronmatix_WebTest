import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
import email85aaee from './email'
import reset0fffd7 from './reset'
import phone601e20 from './phone'
/**
* @see \App\Http\Controllers\Dashboard\PasswordResetController::email
 * @see app/Http/Controllers/Dashboard/PasswordResetController.php:21
 * @route '/dashboard/password/email'
 */
export const email = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: email.url(options),
    method: 'get',
})

email.definition = {
    methods: ["get","head"],
    url: '/dashboard/password/email',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Dashboard\PasswordResetController::email
 * @see app/Http/Controllers/Dashboard/PasswordResetController.php:21
 * @route '/dashboard/password/email'
 */
email.url = (options?: RouteQueryOptions) => {
    return email.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\PasswordResetController::email
 * @see app/Http/Controllers/Dashboard/PasswordResetController.php:21
 * @route '/dashboard/password/email'
 */
email.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: email.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Dashboard\PasswordResetController::email
 * @see app/Http/Controllers/Dashboard/PasswordResetController.php:21
 * @route '/dashboard/password/email'
 */
email.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: email.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Dashboard\PasswordResetController::email
 * @see app/Http/Controllers/Dashboard/PasswordResetController.php:21
 * @route '/dashboard/password/email'
 */
    const emailForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: email.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Dashboard\PasswordResetController::email
 * @see app/Http/Controllers/Dashboard/PasswordResetController.php:21
 * @route '/dashboard/password/email'
 */
        emailForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: email.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Dashboard\PasswordResetController::email
 * @see app/Http/Controllers/Dashboard/PasswordResetController.php:21
 * @route '/dashboard/password/email'
 */
        emailForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: email.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    email.form = emailForm
/**
* @see \App\Http\Controllers\Dashboard\PasswordResetController::reset
 * @see app/Http/Controllers/Dashboard/PasswordResetController.php:113
 * @route '/dashboard/password/reset/{token}'
 */
export const reset = (args: { token: string | number } | [token: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: reset.url(args, options),
    method: 'get',
})

reset.definition = {
    methods: ["get","head"],
    url: '/dashboard/password/reset/{token}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Dashboard\PasswordResetController::reset
 * @see app/Http/Controllers/Dashboard/PasswordResetController.php:113
 * @route '/dashboard/password/reset/{token}'
 */
reset.url = (args: { token: string | number } | [token: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return reset.definition.url
            .replace('{token}', parsedArgs.token.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\PasswordResetController::reset
 * @see app/Http/Controllers/Dashboard/PasswordResetController.php:113
 * @route '/dashboard/password/reset/{token}'
 */
reset.get = (args: { token: string | number } | [token: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: reset.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Dashboard\PasswordResetController::reset
 * @see app/Http/Controllers/Dashboard/PasswordResetController.php:113
 * @route '/dashboard/password/reset/{token}'
 */
reset.head = (args: { token: string | number } | [token: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: reset.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Dashboard\PasswordResetController::reset
 * @see app/Http/Controllers/Dashboard/PasswordResetController.php:113
 * @route '/dashboard/password/reset/{token}'
 */
    const resetForm = (args: { token: string | number } | [token: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: reset.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Dashboard\PasswordResetController::reset
 * @see app/Http/Controllers/Dashboard/PasswordResetController.php:113
 * @route '/dashboard/password/reset/{token}'
 */
        resetForm.get = (args: { token: string | number } | [token: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: reset.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Dashboard\PasswordResetController::reset
 * @see app/Http/Controllers/Dashboard/PasswordResetController.php:113
 * @route '/dashboard/password/reset/{token}'
 */
        resetForm.head = (args: { token: string | number } | [token: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: reset.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    reset.form = resetForm
/**
* @see \App\Http\Controllers\Dashboard\PhoneOtpController::phone
 * @see app/Http/Controllers/Dashboard/PhoneOtpController.php:20
 * @route '/dashboard/password/phone'
 */
export const phone = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: phone.url(options),
    method: 'get',
})

phone.definition = {
    methods: ["get","head"],
    url: '/dashboard/password/phone',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Dashboard\PhoneOtpController::phone
 * @see app/Http/Controllers/Dashboard/PhoneOtpController.php:20
 * @route '/dashboard/password/phone'
 */
phone.url = (options?: RouteQueryOptions) => {
    return phone.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\PhoneOtpController::phone
 * @see app/Http/Controllers/Dashboard/PhoneOtpController.php:20
 * @route '/dashboard/password/phone'
 */
phone.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: phone.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Dashboard\PhoneOtpController::phone
 * @see app/Http/Controllers/Dashboard/PhoneOtpController.php:20
 * @route '/dashboard/password/phone'
 */
phone.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: phone.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Dashboard\PhoneOtpController::phone
 * @see app/Http/Controllers/Dashboard/PhoneOtpController.php:20
 * @route '/dashboard/password/phone'
 */
    const phoneForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: phone.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Dashboard\PhoneOtpController::phone
 * @see app/Http/Controllers/Dashboard/PhoneOtpController.php:20
 * @route '/dashboard/password/phone'
 */
        phoneForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: phone.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Dashboard\PhoneOtpController::phone
 * @see app/Http/Controllers/Dashboard/PhoneOtpController.php:20
 * @route '/dashboard/password/phone'
 */
        phoneForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: phone.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    phone.form = phoneForm
const password = {
    email: Object.assign(email, email85aaee),
reset: Object.assign(reset, reset0fffd7),
phone: Object.assign(phone, phone601e20),
}

export default password