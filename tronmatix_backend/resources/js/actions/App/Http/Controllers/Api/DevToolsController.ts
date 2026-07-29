import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\DevToolsController::health
 * @see app/Http/Controllers/Api/DevToolsController.php:20
 * @route '/api/dev/health'
 */
export const health = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: health.url(options),
    method: 'get',
})

health.definition = {
    methods: ["get","head"],
    url: '/api/dev/health',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\DevToolsController::health
 * @see app/Http/Controllers/Api/DevToolsController.php:20
 * @route '/api/dev/health'
 */
health.url = (options?: RouteQueryOptions) => {
    return health.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\DevToolsController::health
 * @see app/Http/Controllers/Api/DevToolsController.php:20
 * @route '/api/dev/health'
 */
health.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: health.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\DevToolsController::health
 * @see app/Http/Controllers/Api/DevToolsController.php:20
 * @route '/api/dev/health'
 */
health.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: health.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\DevToolsController::health
 * @see app/Http/Controllers/Api/DevToolsController.php:20
 * @route '/api/dev/health'
 */
    const healthForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: health.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\DevToolsController::health
 * @see app/Http/Controllers/Api/DevToolsController.php:20
 * @route '/api/dev/health'
 */
        healthForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: health.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\DevToolsController::health
 * @see app/Http/Controllers/Api/DevToolsController.php:20
 * @route '/api/dev/health'
 */
        healthForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: health.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    health.form = healthForm
/**
* @see \App\Http\Controllers\Api\DevToolsController::logs
 * @see app/Http/Controllers/Api/DevToolsController.php:89
 * @route '/api/dev/logs'
 */
export const logs = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: logs.url(options),
    method: 'get',
})

logs.definition = {
    methods: ["get","head"],
    url: '/api/dev/logs',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\DevToolsController::logs
 * @see app/Http/Controllers/Api/DevToolsController.php:89
 * @route '/api/dev/logs'
 */
logs.url = (options?: RouteQueryOptions) => {
    return logs.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\DevToolsController::logs
 * @see app/Http/Controllers/Api/DevToolsController.php:89
 * @route '/api/dev/logs'
 */
logs.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: logs.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\DevToolsController::logs
 * @see app/Http/Controllers/Api/DevToolsController.php:89
 * @route '/api/dev/logs'
 */
logs.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: logs.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\DevToolsController::logs
 * @see app/Http/Controllers/Api/DevToolsController.php:89
 * @route '/api/dev/logs'
 */
    const logsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: logs.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\DevToolsController::logs
 * @see app/Http/Controllers/Api/DevToolsController.php:89
 * @route '/api/dev/logs'
 */
        logsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: logs.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\DevToolsController::logs
 * @see app/Http/Controllers/Api/DevToolsController.php:89
 * @route '/api/dev/logs'
 */
        logsForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: logs.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    logs.form = logsForm
/**
* @see \App\Http\Controllers\Api\DevToolsController::env
 * @see app/Http/Controllers/Api/DevToolsController.php:132
 * @route '/api/dev/env'
 */
export const env = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: env.url(options),
    method: 'get',
})

env.definition = {
    methods: ["get","head"],
    url: '/api/dev/env',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\DevToolsController::env
 * @see app/Http/Controllers/Api/DevToolsController.php:132
 * @route '/api/dev/env'
 */
env.url = (options?: RouteQueryOptions) => {
    return env.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\DevToolsController::env
 * @see app/Http/Controllers/Api/DevToolsController.php:132
 * @route '/api/dev/env'
 */
env.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: env.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\DevToolsController::env
 * @see app/Http/Controllers/Api/DevToolsController.php:132
 * @route '/api/dev/env'
 */
env.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: env.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\DevToolsController::env
 * @see app/Http/Controllers/Api/DevToolsController.php:132
 * @route '/api/dev/env'
 */
    const envForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: env.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\DevToolsController::env
 * @see app/Http/Controllers/Api/DevToolsController.php:132
 * @route '/api/dev/env'
 */
        envForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: env.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\DevToolsController::env
 * @see app/Http/Controllers/Api/DevToolsController.php:132
 * @route '/api/dev/env'
 */
        envForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: env.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    env.form = envForm
const DevToolsController = { health, logs, env }

export default DevToolsController