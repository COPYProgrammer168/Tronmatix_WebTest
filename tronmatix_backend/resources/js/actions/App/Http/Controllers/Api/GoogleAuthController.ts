import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\GoogleAuthController::handleCallback
 * @see app/Http/Controllers/Api/GoogleAuthController.php:29
 * @route '/api/auth/google'
 */
export const handleCallback = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: handleCallback.url(options),
    method: 'post',
})

handleCallback.definition = {
    methods: ["post"],
    url: '/api/auth/google',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\GoogleAuthController::handleCallback
 * @see app/Http/Controllers/Api/GoogleAuthController.php:29
 * @route '/api/auth/google'
 */
handleCallback.url = (options?: RouteQueryOptions) => {
    return handleCallback.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\GoogleAuthController::handleCallback
 * @see app/Http/Controllers/Api/GoogleAuthController.php:29
 * @route '/api/auth/google'
 */
handleCallback.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: handleCallback.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\GoogleAuthController::handleCallback
 * @see app/Http/Controllers/Api/GoogleAuthController.php:29
 * @route '/api/auth/google'
 */
    const handleCallbackForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: handleCallback.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\GoogleAuthController::handleCallback
 * @see app/Http/Controllers/Api/GoogleAuthController.php:29
 * @route '/api/auth/google'
 */
        handleCallbackForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: handleCallback.url(options),
            method: 'post',
        })
    
    handleCallback.form = handleCallbackForm
const GoogleAuthController = { handleCallback }

export default GoogleAuthController