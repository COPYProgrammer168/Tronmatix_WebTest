import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Dashboard\ActivityLogController::index
 * @see app/Http/Controllers/Dashboard/ActivityLogController.php:36
 * @route '/api/activity-logs'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/api/activity-logs',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Dashboard\ActivityLogController::index
 * @see app/Http/Controllers/Dashboard/ActivityLogController.php:36
 * @route '/api/activity-logs'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\ActivityLogController::index
 * @see app/Http/Controllers/Dashboard/ActivityLogController.php:36
 * @route '/api/activity-logs'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Dashboard\ActivityLogController::index
 * @see app/Http/Controllers/Dashboard/ActivityLogController.php:36
 * @route '/api/activity-logs'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Dashboard\ActivityLogController::index
 * @see app/Http/Controllers/Dashboard/ActivityLogController.php:36
 * @route '/api/activity-logs'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Dashboard\ActivityLogController::index
 * @see app/Http/Controllers/Dashboard/ActivityLogController.php:36
 * @route '/api/activity-logs'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Dashboard\ActivityLogController::index
 * @see app/Http/Controllers/Dashboard/ActivityLogController.php:36
 * @route '/api/activity-logs'
 */
        indexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    index.form = indexForm
/**
* @see \App\Http\Controllers\Dashboard\ActivityLogController::stats
 * @see app/Http/Controllers/Dashboard/ActivityLogController.php:92
 * @route '/api/activity-logs/stats'
 */
export const stats = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: stats.url(options),
    method: 'get',
})

stats.definition = {
    methods: ["get","head"],
    url: '/api/activity-logs/stats',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Dashboard\ActivityLogController::stats
 * @see app/Http/Controllers/Dashboard/ActivityLogController.php:92
 * @route '/api/activity-logs/stats'
 */
stats.url = (options?: RouteQueryOptions) => {
    return stats.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\ActivityLogController::stats
 * @see app/Http/Controllers/Dashboard/ActivityLogController.php:92
 * @route '/api/activity-logs/stats'
 */
stats.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: stats.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Dashboard\ActivityLogController::stats
 * @see app/Http/Controllers/Dashboard/ActivityLogController.php:92
 * @route '/api/activity-logs/stats'
 */
stats.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: stats.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Dashboard\ActivityLogController::stats
 * @see app/Http/Controllers/Dashboard/ActivityLogController.php:92
 * @route '/api/activity-logs/stats'
 */
    const statsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: stats.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Dashboard\ActivityLogController::stats
 * @see app/Http/Controllers/Dashboard/ActivityLogController.php:92
 * @route '/api/activity-logs/stats'
 */
        statsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: stats.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Dashboard\ActivityLogController::stats
 * @see app/Http/Controllers/Dashboard/ActivityLogController.php:92
 * @route '/api/activity-logs/stats'
 */
        statsForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: stats.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    stats.form = statsForm
const ActivityLogController = { index, stats }

export default ActivityLogController