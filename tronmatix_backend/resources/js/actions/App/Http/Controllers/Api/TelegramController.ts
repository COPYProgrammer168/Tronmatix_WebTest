import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\TelegramController::connect
 * @see app/Http/Controllers/Api/TelegramController.php:19
 * @route '/api/telegram/connect'
 */
export const connect = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: connect.url(options),
    method: 'post',
})

connect.definition = {
    methods: ["post"],
    url: '/api/telegram/connect',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\TelegramController::connect
 * @see app/Http/Controllers/Api/TelegramController.php:19
 * @route '/api/telegram/connect'
 */
connect.url = (options?: RouteQueryOptions) => {
    return connect.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\TelegramController::connect
 * @see app/Http/Controllers/Api/TelegramController.php:19
 * @route '/api/telegram/connect'
 */
connect.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: connect.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\TelegramController::connect
 * @see app/Http/Controllers/Api/TelegramController.php:19
 * @route '/api/telegram/connect'
 */
    const connectForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: connect.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\TelegramController::connect
 * @see app/Http/Controllers/Api/TelegramController.php:19
 * @route '/api/telegram/connect'
 */
        connectForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: connect.url(options),
            method: 'post',
        })
    
    connect.form = connectForm
/**
* @see \App\Http\Controllers\Api\TelegramController::generateToken
 * @see app/Http/Controllers/Api/TelegramController.php:142
 * @route '/api/telegram/generate-token'
 */
export const generateToken = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: generateToken.url(options),
    method: 'post',
})

generateToken.definition = {
    methods: ["post"],
    url: '/api/telegram/generate-token',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\TelegramController::generateToken
 * @see app/Http/Controllers/Api/TelegramController.php:142
 * @route '/api/telegram/generate-token'
 */
generateToken.url = (options?: RouteQueryOptions) => {
    return generateToken.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\TelegramController::generateToken
 * @see app/Http/Controllers/Api/TelegramController.php:142
 * @route '/api/telegram/generate-token'
 */
generateToken.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: generateToken.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\TelegramController::generateToken
 * @see app/Http/Controllers/Api/TelegramController.php:142
 * @route '/api/telegram/generate-token'
 */
    const generateTokenForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: generateToken.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\TelegramController::generateToken
 * @see app/Http/Controllers/Api/TelegramController.php:142
 * @route '/api/telegram/generate-token'
 */
        generateTokenForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: generateToken.url(options),
            method: 'post',
        })
    
    generateToken.form = generateTokenForm
/**
* @see \App\Http\Controllers\Api\TelegramController::disconnect
 * @see app/Http/Controllers/Api/TelegramController.php:67
 * @route '/api/telegram/disconnect'
 */
export const disconnect = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: disconnect.url(options),
    method: 'post',
})

disconnect.definition = {
    methods: ["post"],
    url: '/api/telegram/disconnect',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\TelegramController::disconnect
 * @see app/Http/Controllers/Api/TelegramController.php:67
 * @route '/api/telegram/disconnect'
 */
disconnect.url = (options?: RouteQueryOptions) => {
    return disconnect.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\TelegramController::disconnect
 * @see app/Http/Controllers/Api/TelegramController.php:67
 * @route '/api/telegram/disconnect'
 */
disconnect.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: disconnect.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\TelegramController::disconnect
 * @see app/Http/Controllers/Api/TelegramController.php:67
 * @route '/api/telegram/disconnect'
 */
    const disconnectForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: disconnect.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\TelegramController::disconnect
 * @see app/Http/Controllers/Api/TelegramController.php:67
 * @route '/api/telegram/disconnect'
 */
        disconnectForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: disconnect.url(options),
            method: 'post',
        })
    
    disconnect.form = disconnectForm
/**
* @see \App\Http\Controllers\Api\TelegramController::status
 * @see app/Http/Controllers/Api/TelegramController.php:118
 * @route '/api/telegram/status'
 */
export const status = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: status.url(options),
    method: 'get',
})

status.definition = {
    methods: ["get","head"],
    url: '/api/telegram/status',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\TelegramController::status
 * @see app/Http/Controllers/Api/TelegramController.php:118
 * @route '/api/telegram/status'
 */
status.url = (options?: RouteQueryOptions) => {
    return status.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\TelegramController::status
 * @see app/Http/Controllers/Api/TelegramController.php:118
 * @route '/api/telegram/status'
 */
status.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: status.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\TelegramController::status
 * @see app/Http/Controllers/Api/TelegramController.php:118
 * @route '/api/telegram/status'
 */
status.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: status.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\TelegramController::status
 * @see app/Http/Controllers/Api/TelegramController.php:118
 * @route '/api/telegram/status'
 */
    const statusForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: status.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\TelegramController::status
 * @see app/Http/Controllers/Api/TelegramController.php:118
 * @route '/api/telegram/status'
 */
        statusForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: status.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\TelegramController::status
 * @see app/Http/Controllers/Api/TelegramController.php:118
 * @route '/api/telegram/status'
 */
        statusForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: status.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    status.form = statusForm
/**
* @see \App\Http\Controllers\Api\TelegramController::testMessage
 * @see app/Http/Controllers/Api/TelegramController.php:96
 * @route '/api/telegram/test-message'
 */
export const testMessage = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: testMessage.url(options),
    method: 'post',
})

testMessage.definition = {
    methods: ["post"],
    url: '/api/telegram/test-message',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\TelegramController::testMessage
 * @see app/Http/Controllers/Api/TelegramController.php:96
 * @route '/api/telegram/test-message'
 */
testMessage.url = (options?: RouteQueryOptions) => {
    return testMessage.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\TelegramController::testMessage
 * @see app/Http/Controllers/Api/TelegramController.php:96
 * @route '/api/telegram/test-message'
 */
testMessage.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: testMessage.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\TelegramController::testMessage
 * @see app/Http/Controllers/Api/TelegramController.php:96
 * @route '/api/telegram/test-message'
 */
    const testMessageForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: testMessage.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\TelegramController::testMessage
 * @see app/Http/Controllers/Api/TelegramController.php:96
 * @route '/api/telegram/test-message'
 */
        testMessageForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: testMessage.url(options),
            method: 'post',
        })
    
    testMessage.form = testMessageForm
const TelegramController = { connect, generateToken, disconnect, status, testMessage }

export default TelegramController