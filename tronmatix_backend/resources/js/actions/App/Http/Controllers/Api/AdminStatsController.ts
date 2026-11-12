import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\AdminStatsController::stats
 * @see app/Http/Controllers/Api/AdminStatsController.php:20
 * @route '/api/admin/stats'
 */
export const stats = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: stats.url(options),
    method: 'get',
})

stats.definition = {
    methods: ["get","head"],
    url: '/api/admin/stats',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\AdminStatsController::stats
 * @see app/Http/Controllers/Api/AdminStatsController.php:20
 * @route '/api/admin/stats'
 */
stats.url = (options?: RouteQueryOptions) => {
    return stats.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminStatsController::stats
 * @see app/Http/Controllers/Api/AdminStatsController.php:20
 * @route '/api/admin/stats'
 */
stats.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: stats.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\AdminStatsController::stats
 * @see app/Http/Controllers/Api/AdminStatsController.php:20
 * @route '/api/admin/stats'
 */
stats.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: stats.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\AdminStatsController::stats
 * @see app/Http/Controllers/Api/AdminStatsController.php:20
 * @route '/api/admin/stats'
 */
    const statsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: stats.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\AdminStatsController::stats
 * @see app/Http/Controllers/Api/AdminStatsController.php:20
 * @route '/api/admin/stats'
 */
        statsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: stats.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\AdminStatsController::stats
 * @see app/Http/Controllers/Api/AdminStatsController.php:20
 * @route '/api/admin/stats'
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
/**
* @see \App\Http\Controllers\Api\AdminStatsController::users
 * @see app/Http/Controllers/Api/AdminStatsController.php:82
 * @route '/api/admin/users'
 */
export const users = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: users.url(options),
    method: 'get',
})

users.definition = {
    methods: ["get","head"],
    url: '/api/admin/users',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\AdminStatsController::users
 * @see app/Http/Controllers/Api/AdminStatsController.php:82
 * @route '/api/admin/users'
 */
users.url = (options?: RouteQueryOptions) => {
    return users.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminStatsController::users
 * @see app/Http/Controllers/Api/AdminStatsController.php:82
 * @route '/api/admin/users'
 */
users.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: users.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\AdminStatsController::users
 * @see app/Http/Controllers/Api/AdminStatsController.php:82
 * @route '/api/admin/users'
 */
users.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: users.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\AdminStatsController::users
 * @see app/Http/Controllers/Api/AdminStatsController.php:82
 * @route '/api/admin/users'
 */
    const usersForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: users.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\AdminStatsController::users
 * @see app/Http/Controllers/Api/AdminStatsController.php:82
 * @route '/api/admin/users'
 */
        usersForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: users.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\AdminStatsController::users
 * @see app/Http/Controllers/Api/AdminStatsController.php:82
 * @route '/api/admin/users'
 */
        usersForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: users.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    users.form = usersForm
const AdminStatsController = { stats, users }

export default AdminStatsController