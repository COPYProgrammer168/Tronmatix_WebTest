import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Dashboard\PhoneOtpController::showPhoneForm
 * @see app/Http/Controllers/Dashboard/PhoneOtpController.php:20
 * @route '/dashboard/password/phone'
 */
export const showPhoneForm = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: showPhoneForm.url(options),
    method: 'get',
})

showPhoneForm.definition = {
    methods: ["get","head"],
    url: '/dashboard/password/phone',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Dashboard\PhoneOtpController::showPhoneForm
 * @see app/Http/Controllers/Dashboard/PhoneOtpController.php:20
 * @route '/dashboard/password/phone'
 */
showPhoneForm.url = (options?: RouteQueryOptions) => {
    return showPhoneForm.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\PhoneOtpController::showPhoneForm
 * @see app/Http/Controllers/Dashboard/PhoneOtpController.php:20
 * @route '/dashboard/password/phone'
 */
showPhoneForm.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: showPhoneForm.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Dashboard\PhoneOtpController::showPhoneForm
 * @see app/Http/Controllers/Dashboard/PhoneOtpController.php:20
 * @route '/dashboard/password/phone'
 */
showPhoneForm.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: showPhoneForm.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Dashboard\PhoneOtpController::showPhoneForm
 * @see app/Http/Controllers/Dashboard/PhoneOtpController.php:20
 * @route '/dashboard/password/phone'
 */
    const showPhoneFormForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: showPhoneForm.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Dashboard\PhoneOtpController::showPhoneForm
 * @see app/Http/Controllers/Dashboard/PhoneOtpController.php:20
 * @route '/dashboard/password/phone'
 */
        showPhoneFormForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: showPhoneForm.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Dashboard\PhoneOtpController::showPhoneForm
 * @see app/Http/Controllers/Dashboard/PhoneOtpController.php:20
 * @route '/dashboard/password/phone'
 */
        showPhoneFormForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: showPhoneForm.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    showPhoneForm.form = showPhoneFormForm
/**
* @see \App\Http\Controllers\Dashboard\PhoneOtpController::verifyOtpAndReset
 * @see app/Http/Controllers/Dashboard/PhoneOtpController.php:45
 * @route '/dashboard/password/phone/verify'
 */
export const verifyOtpAndReset = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: verifyOtpAndReset.url(options),
    method: 'post',
})

verifyOtpAndReset.definition = {
    methods: ["post"],
    url: '/dashboard/password/phone/verify',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Dashboard\PhoneOtpController::verifyOtpAndReset
 * @see app/Http/Controllers/Dashboard/PhoneOtpController.php:45
 * @route '/dashboard/password/phone/verify'
 */
verifyOtpAndReset.url = (options?: RouteQueryOptions) => {
    return verifyOtpAndReset.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\PhoneOtpController::verifyOtpAndReset
 * @see app/Http/Controllers/Dashboard/PhoneOtpController.php:45
 * @route '/dashboard/password/phone/verify'
 */
verifyOtpAndReset.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: verifyOtpAndReset.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Dashboard\PhoneOtpController::verifyOtpAndReset
 * @see app/Http/Controllers/Dashboard/PhoneOtpController.php:45
 * @route '/dashboard/password/phone/verify'
 */
    const verifyOtpAndResetForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: verifyOtpAndReset.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Dashboard\PhoneOtpController::verifyOtpAndReset
 * @see app/Http/Controllers/Dashboard/PhoneOtpController.php:45
 * @route '/dashboard/password/phone/verify'
 */
        verifyOtpAndResetForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: verifyOtpAndReset.url(options),
            method: 'post',
        })
    
    verifyOtpAndReset.form = verifyOtpAndResetForm
const PhoneOtpController = { showPhoneForm, verifyOtpAndReset }

export default PhoneOtpController