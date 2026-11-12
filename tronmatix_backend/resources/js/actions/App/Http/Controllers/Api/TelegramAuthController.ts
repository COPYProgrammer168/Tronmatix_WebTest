import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\TelegramAuthController::handleCallback
 * @see app/Http/Controllers/Api/TelegramAuthController.php:33
 * @route '/api/auth/telegram'
 */
export const handleCallback = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: handleCallback.url(options),
    method: 'post',
})

handleCallback.definition = {
    methods: ["post"],
    url: '/api/auth/telegram',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\TelegramAuthController::handleCallback
 * @see app/Http/Controllers/Api/TelegramAuthController.php:33
 * @route '/api/auth/telegram'
 */
handleCallback.url = (options?: RouteQueryOptions) => {
    return handleCallback.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\TelegramAuthController::handleCallback
 * @see app/Http/Controllers/Api/TelegramAuthController.php:33
 * @route '/api/auth/telegram'
 */
handleCallback.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: handleCallback.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\TelegramAuthController::handleCallback
 * @see app/Http/Controllers/Api/TelegramAuthController.php:33
 * @route '/api/auth/telegram'
 */
    const handleCallbackForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: handleCallback.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\TelegramAuthController::handleCallback
 * @see app/Http/Controllers/Api/TelegramAuthController.php:33
 * @route '/api/auth/telegram'
 */
        handleCallbackForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: handleCallback.url(options),
            method: 'post',
        })
    
    handleCallback.form = handleCallbackForm
/**
* @see \App\Http\Controllers\Api\TelegramAuthController::handleMiniApp
 * @see app/Http/Controllers/Api/TelegramAuthController.php:208
 * @route '/api/auth/telegram/mini-app'
 */
export const handleMiniApp = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: handleMiniApp.url(options),
    method: 'post',
})

handleMiniApp.definition = {
    methods: ["post"],
    url: '/api/auth/telegram/mini-app',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\TelegramAuthController::handleMiniApp
 * @see app/Http/Controllers/Api/TelegramAuthController.php:208
 * @route '/api/auth/telegram/mini-app'
 */
handleMiniApp.url = (options?: RouteQueryOptions) => {
    return handleMiniApp.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\TelegramAuthController::handleMiniApp
 * @see app/Http/Controllers/Api/TelegramAuthController.php:208
 * @route '/api/auth/telegram/mini-app'
 */
handleMiniApp.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: handleMiniApp.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\TelegramAuthController::handleMiniApp
 * @see app/Http/Controllers/Api/TelegramAuthController.php:208
 * @route '/api/auth/telegram/mini-app'
 */
    const handleMiniAppForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: handleMiniApp.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\TelegramAuthController::handleMiniApp
 * @see app/Http/Controllers/Api/TelegramAuthController.php:208
 * @route '/api/auth/telegram/mini-app'
 */
        handleMiniAppForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: handleMiniApp.url(options),
            method: 'post',
        })
    
    handleMiniApp.form = handleMiniAppForm
/**
* @see \App\Http\Controllers\Api\TelegramAuthController::generateLoginToken
 * @see app/Http/Controllers/Api/TelegramAuthController.php:376
 * @route '/api/auth/telegram-generate-token'
 */
export const generateLoginToken = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: generateLoginToken.url(options),
    method: 'post',
})

generateLoginToken.definition = {
    methods: ["post"],
    url: '/api/auth/telegram-generate-token',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\TelegramAuthController::generateLoginToken
 * @see app/Http/Controllers/Api/TelegramAuthController.php:376
 * @route '/api/auth/telegram-generate-token'
 */
generateLoginToken.url = (options?: RouteQueryOptions) => {
    return generateLoginToken.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\TelegramAuthController::generateLoginToken
 * @see app/Http/Controllers/Api/TelegramAuthController.php:376
 * @route '/api/auth/telegram-generate-token'
 */
generateLoginToken.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: generateLoginToken.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\TelegramAuthController::generateLoginToken
 * @see app/Http/Controllers/Api/TelegramAuthController.php:376
 * @route '/api/auth/telegram-generate-token'
 */
    const generateLoginTokenForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: generateLoginToken.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\TelegramAuthController::generateLoginToken
 * @see app/Http/Controllers/Api/TelegramAuthController.php:376
 * @route '/api/auth/telegram-generate-token'
 */
        generateLoginTokenForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: generateLoginToken.url(options),
            method: 'post',
        })
    
    generateLoginToken.form = generateLoginTokenForm
/**
* @see \App\Http\Controllers\Api\TelegramAuthController::checkLoginToken
 * @see app/Http/Controllers/Api/TelegramAuthController.php:390
 * @route '/api/auth/telegram-status'
 */
export const checkLoginToken = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: checkLoginToken.url(options),
    method: 'get',
})

checkLoginToken.definition = {
    methods: ["get","head"],
    url: '/api/auth/telegram-status',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\TelegramAuthController::checkLoginToken
 * @see app/Http/Controllers/Api/TelegramAuthController.php:390
 * @route '/api/auth/telegram-status'
 */
checkLoginToken.url = (options?: RouteQueryOptions) => {
    return checkLoginToken.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\TelegramAuthController::checkLoginToken
 * @see app/Http/Controllers/Api/TelegramAuthController.php:390
 * @route '/api/auth/telegram-status'
 */
checkLoginToken.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: checkLoginToken.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\TelegramAuthController::checkLoginToken
 * @see app/Http/Controllers/Api/TelegramAuthController.php:390
 * @route '/api/auth/telegram-status'
 */
checkLoginToken.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: checkLoginToken.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\TelegramAuthController::checkLoginToken
 * @see app/Http/Controllers/Api/TelegramAuthController.php:390
 * @route '/api/auth/telegram-status'
 */
    const checkLoginTokenForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: checkLoginToken.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\TelegramAuthController::checkLoginToken
 * @see app/Http/Controllers/Api/TelegramAuthController.php:390
 * @route '/api/auth/telegram-status'
 */
        checkLoginTokenForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: checkLoginToken.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\TelegramAuthController::checkLoginToken
 * @see app/Http/Controllers/Api/TelegramAuthController.php:390
 * @route '/api/auth/telegram-status'
 */
        checkLoginTokenForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: checkLoginToken.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    checkLoginToken.form = checkLoginTokenForm
const TelegramAuthController = { handleCallback, handleMiniApp, generateLoginToken, checkLoginToken }

export default TelegramAuthController