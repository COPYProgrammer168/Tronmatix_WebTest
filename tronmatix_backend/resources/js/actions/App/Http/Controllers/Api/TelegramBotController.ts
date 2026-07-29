import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\TelegramBotController::webhook
 * @see app/Http/Controllers/Api/TelegramBotController.php:25
 * @route '/api/telegram/bot-webhook'
 */
export const webhook = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: webhook.url(options),
    method: 'post',
})

webhook.definition = {
    methods: ["post"],
    url: '/api/telegram/bot-webhook',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\TelegramBotController::webhook
 * @see app/Http/Controllers/Api/TelegramBotController.php:25
 * @route '/api/telegram/bot-webhook'
 */
webhook.url = (options?: RouteQueryOptions) => {
    return webhook.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\TelegramBotController::webhook
 * @see app/Http/Controllers/Api/TelegramBotController.php:25
 * @route '/api/telegram/bot-webhook'
 */
webhook.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: webhook.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\TelegramBotController::webhook
 * @see app/Http/Controllers/Api/TelegramBotController.php:25
 * @route '/api/telegram/bot-webhook'
 */
    const webhookForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: webhook.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\TelegramBotController::webhook
 * @see app/Http/Controllers/Api/TelegramBotController.php:25
 * @route '/api/telegram/bot-webhook'
 */
        webhookForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: webhook.url(options),
            method: 'post',
        })
    
    webhook.form = webhookForm
/**
* @see \App\Http\Controllers\Api\TelegramBotController::setupWebhook
 * @see app/Http/Controllers/Api/TelegramBotController.php:56
 * @route '/api/telegram/setup-webhook'
 */
export const setupWebhook = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: setupWebhook.url(options),
    method: 'post',
})

setupWebhook.definition = {
    methods: ["post"],
    url: '/api/telegram/setup-webhook',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\TelegramBotController::setupWebhook
 * @see app/Http/Controllers/Api/TelegramBotController.php:56
 * @route '/api/telegram/setup-webhook'
 */
setupWebhook.url = (options?: RouteQueryOptions) => {
    return setupWebhook.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\TelegramBotController::setupWebhook
 * @see app/Http/Controllers/Api/TelegramBotController.php:56
 * @route '/api/telegram/setup-webhook'
 */
setupWebhook.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: setupWebhook.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\TelegramBotController::setupWebhook
 * @see app/Http/Controllers/Api/TelegramBotController.php:56
 * @route '/api/telegram/setup-webhook'
 */
    const setupWebhookForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: setupWebhook.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\TelegramBotController::setupWebhook
 * @see app/Http/Controllers/Api/TelegramBotController.php:56
 * @route '/api/telegram/setup-webhook'
 */
        setupWebhookForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: setupWebhook.url(options),
            method: 'post',
        })
    
    setupWebhook.form = setupWebhookForm
/**
* @see \App\Http\Controllers\Api\TelegramBotController::deleteWebhook
 * @see app/Http/Controllers/Api/TelegramBotController.php:72
 * @route '/api/telegram/delete-webhook'
 */
export const deleteWebhook = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: deleteWebhook.url(options),
    method: 'post',
})

deleteWebhook.definition = {
    methods: ["post"],
    url: '/api/telegram/delete-webhook',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\TelegramBotController::deleteWebhook
 * @see app/Http/Controllers/Api/TelegramBotController.php:72
 * @route '/api/telegram/delete-webhook'
 */
deleteWebhook.url = (options?: RouteQueryOptions) => {
    return deleteWebhook.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\TelegramBotController::deleteWebhook
 * @see app/Http/Controllers/Api/TelegramBotController.php:72
 * @route '/api/telegram/delete-webhook'
 */
deleteWebhook.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: deleteWebhook.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\TelegramBotController::deleteWebhook
 * @see app/Http/Controllers/Api/TelegramBotController.php:72
 * @route '/api/telegram/delete-webhook'
 */
    const deleteWebhookForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: deleteWebhook.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\TelegramBotController::deleteWebhook
 * @see app/Http/Controllers/Api/TelegramBotController.php:72
 * @route '/api/telegram/delete-webhook'
 */
        deleteWebhookForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: deleteWebhook.url(options),
            method: 'post',
        })
    
    deleteWebhook.form = deleteWebhookForm
/**
* @see \App\Http\Controllers\Api\TelegramBotController::webhookInfo
 * @see app/Http/Controllers/Api/TelegramBotController.php:88
 * @route '/api/telegram/webhook-info'
 */
export const webhookInfo = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: webhookInfo.url(options),
    method: 'get',
})

webhookInfo.definition = {
    methods: ["get","head"],
    url: '/api/telegram/webhook-info',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\TelegramBotController::webhookInfo
 * @see app/Http/Controllers/Api/TelegramBotController.php:88
 * @route '/api/telegram/webhook-info'
 */
webhookInfo.url = (options?: RouteQueryOptions) => {
    return webhookInfo.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\TelegramBotController::webhookInfo
 * @see app/Http/Controllers/Api/TelegramBotController.php:88
 * @route '/api/telegram/webhook-info'
 */
webhookInfo.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: webhookInfo.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\TelegramBotController::webhookInfo
 * @see app/Http/Controllers/Api/TelegramBotController.php:88
 * @route '/api/telegram/webhook-info'
 */
webhookInfo.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: webhookInfo.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\TelegramBotController::webhookInfo
 * @see app/Http/Controllers/Api/TelegramBotController.php:88
 * @route '/api/telegram/webhook-info'
 */
    const webhookInfoForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: webhookInfo.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\TelegramBotController::webhookInfo
 * @see app/Http/Controllers/Api/TelegramBotController.php:88
 * @route '/api/telegram/webhook-info'
 */
        webhookInfoForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: webhookInfo.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\TelegramBotController::webhookInfo
 * @see app/Http/Controllers/Api/TelegramBotController.php:88
 * @route '/api/telegram/webhook-info'
 */
        webhookInfoForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: webhookInfo.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    webhookInfo.form = webhookInfoForm
/**
* @see \App\Http\Controllers\Api\TelegramBotController::setCommands
 * @see app/Http/Controllers/Api/TelegramBotController.php:94
 * @route '/api/telegram/set-commands'
 */
export const setCommands = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: setCommands.url(options),
    method: 'post',
})

setCommands.definition = {
    methods: ["post"],
    url: '/api/telegram/set-commands',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\TelegramBotController::setCommands
 * @see app/Http/Controllers/Api/TelegramBotController.php:94
 * @route '/api/telegram/set-commands'
 */
setCommands.url = (options?: RouteQueryOptions) => {
    return setCommands.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\TelegramBotController::setCommands
 * @see app/Http/Controllers/Api/TelegramBotController.php:94
 * @route '/api/telegram/set-commands'
 */
setCommands.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: setCommands.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\TelegramBotController::setCommands
 * @see app/Http/Controllers/Api/TelegramBotController.php:94
 * @route '/api/telegram/set-commands'
 */
    const setCommandsForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: setCommands.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\TelegramBotController::setCommands
 * @see app/Http/Controllers/Api/TelegramBotController.php:94
 * @route '/api/telegram/set-commands'
 */
        setCommandsForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: setCommands.url(options),
            method: 'post',
        })
    
    setCommands.form = setCommandsForm
const TelegramBotController = { webhook, setupWebhook, deleteWebhook, webhookInfo, setCommands }

export default TelegramBotController