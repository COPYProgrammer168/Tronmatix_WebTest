import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\SettingsApiController::roles
 * @see app/Http/Controllers/Api/SettingsApiController.php:12
 * @route '/api/settings/roles'
 */
export const roles = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: roles.url(options),
    method: 'get',
})

roles.definition = {
    methods: ["get","head"],
    url: '/api/settings/roles',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\SettingsApiController::roles
 * @see app/Http/Controllers/Api/SettingsApiController.php:12
 * @route '/api/settings/roles'
 */
roles.url = (options?: RouteQueryOptions) => {
    return roles.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\SettingsApiController::roles
 * @see app/Http/Controllers/Api/SettingsApiController.php:12
 * @route '/api/settings/roles'
 */
roles.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: roles.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\SettingsApiController::roles
 * @see app/Http/Controllers/Api/SettingsApiController.php:12
 * @route '/api/settings/roles'
 */
roles.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: roles.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\SettingsApiController::roles
 * @see app/Http/Controllers/Api/SettingsApiController.php:12
 * @route '/api/settings/roles'
 */
    const rolesForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: roles.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\SettingsApiController::roles
 * @see app/Http/Controllers/Api/SettingsApiController.php:12
 * @route '/api/settings/roles'
 */
        rolesForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: roles.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\SettingsApiController::roles
 * @see app/Http/Controllers/Api/SettingsApiController.php:12
 * @route '/api/settings/roles'
 */
        rolesForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: roles.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    roles.form = rolesForm
/**
* @see \App\Http\Controllers\Api\SettingsApiController::features
 * @see app/Http/Controllers/Api/SettingsApiController.php:25
 * @route '/api/settings/features'
 */
export const features = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: features.url(options),
    method: 'get',
})

features.definition = {
    methods: ["get","head"],
    url: '/api/settings/features',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\SettingsApiController::features
 * @see app/Http/Controllers/Api/SettingsApiController.php:25
 * @route '/api/settings/features'
 */
features.url = (options?: RouteQueryOptions) => {
    return features.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\SettingsApiController::features
 * @see app/Http/Controllers/Api/SettingsApiController.php:25
 * @route '/api/settings/features'
 */
features.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: features.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\SettingsApiController::features
 * @see app/Http/Controllers/Api/SettingsApiController.php:25
 * @route '/api/settings/features'
 */
features.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: features.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\SettingsApiController::features
 * @see app/Http/Controllers/Api/SettingsApiController.php:25
 * @route '/api/settings/features'
 */
    const featuresForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: features.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\SettingsApiController::features
 * @see app/Http/Controllers/Api/SettingsApiController.php:25
 * @route '/api/settings/features'
 */
        featuresForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: features.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\SettingsApiController::features
 * @see app/Http/Controllers/Api/SettingsApiController.php:25
 * @route '/api/settings/features'
 */
        featuresForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: features.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    features.form = featuresForm
const SettingsApiController = { roles, features }

export default SettingsApiController