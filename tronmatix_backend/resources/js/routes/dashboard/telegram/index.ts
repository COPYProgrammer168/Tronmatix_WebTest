import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Dashboard\TelegramAdminController::setupWebhook
 * @see app/Http/Controllers/Dashboard/TelegramAdminController.php:25
 * @route '/dashboard/telegram/setup-webhook'
 */
export const setupWebhook = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: setupWebhook.url(options),
    method: 'post',
})

setupWebhook.definition = {
    methods: ["post"],
    url: '/dashboard/telegram/setup-webhook',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Dashboard\TelegramAdminController::setupWebhook
 * @see app/Http/Controllers/Dashboard/TelegramAdminController.php:25
 * @route '/dashboard/telegram/setup-webhook'
 */
setupWebhook.url = (options?: RouteQueryOptions) => {
    return setupWebhook.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\TelegramAdminController::setupWebhook
 * @see app/Http/Controllers/Dashboard/TelegramAdminController.php:25
 * @route '/dashboard/telegram/setup-webhook'
 */
setupWebhook.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: setupWebhook.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Dashboard\TelegramAdminController::setupWebhook
 * @see app/Http/Controllers/Dashboard/TelegramAdminController.php:25
 * @route '/dashboard/telegram/setup-webhook'
 */
    const setupWebhookForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: setupWebhook.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Dashboard\TelegramAdminController::setupWebhook
 * @see app/Http/Controllers/Dashboard/TelegramAdminController.php:25
 * @route '/dashboard/telegram/setup-webhook'
 */
        setupWebhookForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: setupWebhook.url(options),
            method: 'post',
        })
    
    setupWebhook.form = setupWebhookForm
/**
* @see \App\Http\Controllers\Dashboard\TelegramAdminController::deleteWebhook
 * @see app/Http/Controllers/Dashboard/TelegramAdminController.php:52
 * @route '/dashboard/telegram/delete-webhook'
 */
export const deleteWebhook = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: deleteWebhook.url(options),
    method: 'post',
})

deleteWebhook.definition = {
    methods: ["post"],
    url: '/dashboard/telegram/delete-webhook',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Dashboard\TelegramAdminController::deleteWebhook
 * @see app/Http/Controllers/Dashboard/TelegramAdminController.php:52
 * @route '/dashboard/telegram/delete-webhook'
 */
deleteWebhook.url = (options?: RouteQueryOptions) => {
    return deleteWebhook.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\TelegramAdminController::deleteWebhook
 * @see app/Http/Controllers/Dashboard/TelegramAdminController.php:52
 * @route '/dashboard/telegram/delete-webhook'
 */
deleteWebhook.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: deleteWebhook.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Dashboard\TelegramAdminController::deleteWebhook
 * @see app/Http/Controllers/Dashboard/TelegramAdminController.php:52
 * @route '/dashboard/telegram/delete-webhook'
 */
    const deleteWebhookForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: deleteWebhook.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Dashboard\TelegramAdminController::deleteWebhook
 * @see app/Http/Controllers/Dashboard/TelegramAdminController.php:52
 * @route '/dashboard/telegram/delete-webhook'
 */
        deleteWebhookForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: deleteWebhook.url(options),
            method: 'post',
        })
    
    deleteWebhook.form = deleteWebhookForm
/**
* @see \App\Http\Controllers\Dashboard\TelegramAdminController::webhookInfo
 * @see app/Http/Controllers/Dashboard/TelegramAdminController.php:70
 * @route '/dashboard/telegram/webhook-info'
 */
export const webhookInfo = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: webhookInfo.url(options),
    method: 'get',
})

webhookInfo.definition = {
    methods: ["get","head"],
    url: '/dashboard/telegram/webhook-info',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Dashboard\TelegramAdminController::webhookInfo
 * @see app/Http/Controllers/Dashboard/TelegramAdminController.php:70
 * @route '/dashboard/telegram/webhook-info'
 */
webhookInfo.url = (options?: RouteQueryOptions) => {
    return webhookInfo.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\TelegramAdminController::webhookInfo
 * @see app/Http/Controllers/Dashboard/TelegramAdminController.php:70
 * @route '/dashboard/telegram/webhook-info'
 */
webhookInfo.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: webhookInfo.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Dashboard\TelegramAdminController::webhookInfo
 * @see app/Http/Controllers/Dashboard/TelegramAdminController.php:70
 * @route '/dashboard/telegram/webhook-info'
 */
webhookInfo.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: webhookInfo.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Dashboard\TelegramAdminController::webhookInfo
 * @see app/Http/Controllers/Dashboard/TelegramAdminController.php:70
 * @route '/dashboard/telegram/webhook-info'
 */
    const webhookInfoForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: webhookInfo.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Dashboard\TelegramAdminController::webhookInfo
 * @see app/Http/Controllers/Dashboard/TelegramAdminController.php:70
 * @route '/dashboard/telegram/webhook-info'
 */
        webhookInfoForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: webhookInfo.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Dashboard\TelegramAdminController::webhookInfo
 * @see app/Http/Controllers/Dashboard/TelegramAdminController.php:70
 * @route '/dashboard/telegram/webhook-info'
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
const telegram = {
    setupWebhook: Object.assign(setupWebhook, setupWebhook),
deleteWebhook: Object.assign(deleteWebhook, deleteWebhook),
webhookInfo: Object.assign(webhookInfo, webhookInfo),
}

export default telegram