import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../wayfinder'
import roles from './roles'
import features from './features'
import marquees7cf7b9 from './marquees'
/**
* @see \App\Http\Controllers\Dashboard\SettingsController::update
 * @see app/Http/Controllers/Dashboard/SettingsController.php:29
 * @route '/dashboard/settings'
 */
export const update = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(options),
    method: 'put',
})

update.definition = {
    methods: ["put"],
    url: '/dashboard/settings',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Dashboard\SettingsController::update
 * @see app/Http/Controllers/Dashboard/SettingsController.php:29
 * @route '/dashboard/settings'
 */
update.url = (options?: RouteQueryOptions) => {
    return update.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\SettingsController::update
 * @see app/Http/Controllers/Dashboard/SettingsController.php:29
 * @route '/dashboard/settings'
 */
update.put = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\Dashboard\SettingsController::update
 * @see app/Http/Controllers/Dashboard/SettingsController.php:29
 * @route '/dashboard/settings'
 */
    const updateForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update.url({
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Dashboard\SettingsController::update
 * @see app/Http/Controllers/Dashboard/SettingsController.php:29
 * @route '/dashboard/settings'
 */
        updateForm.put = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    update.form = updateForm
/**
* @see \App\Http\Controllers\Dashboard\SettingsController::resetVip
 * @see app/Http/Controllers/Dashboard/SettingsController.php:516
 * @route '/dashboard/settings/reset-vip'
 */
export const resetVip = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: resetVip.url(options),
    method: 'post',
})

resetVip.definition = {
    methods: ["post"],
    url: '/dashboard/settings/reset-vip',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Dashboard\SettingsController::resetVip
 * @see app/Http/Controllers/Dashboard/SettingsController.php:516
 * @route '/dashboard/settings/reset-vip'
 */
resetVip.url = (options?: RouteQueryOptions) => {
    return resetVip.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\SettingsController::resetVip
 * @see app/Http/Controllers/Dashboard/SettingsController.php:516
 * @route '/dashboard/settings/reset-vip'
 */
resetVip.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: resetVip.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Dashboard\SettingsController::resetVip
 * @see app/Http/Controllers/Dashboard/SettingsController.php:516
 * @route '/dashboard/settings/reset-vip'
 */
    const resetVipForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: resetVip.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Dashboard\SettingsController::resetVip
 * @see app/Http/Controllers/Dashboard/SettingsController.php:516
 * @route '/dashboard/settings/reset-vip'
 */
        resetVipForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: resetVip.url(options),
            method: 'post',
        })
    
    resetVip.form = resetVipForm
/**
* @see \App\Http\Controllers\Dashboard\SettingsController::reset
 * @see app/Http/Controllers/Dashboard/SettingsController.php:69
 * @route '/dashboard/settings/reset'
 */
export const reset = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: reset.url(options),
    method: 'get',
})

reset.definition = {
    methods: ["get","head"],
    url: '/dashboard/settings/reset',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Dashboard\SettingsController::reset
 * @see app/Http/Controllers/Dashboard/SettingsController.php:69
 * @route '/dashboard/settings/reset'
 */
reset.url = (options?: RouteQueryOptions) => {
    return reset.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\SettingsController::reset
 * @see app/Http/Controllers/Dashboard/SettingsController.php:69
 * @route '/dashboard/settings/reset'
 */
reset.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: reset.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Dashboard\SettingsController::reset
 * @see app/Http/Controllers/Dashboard/SettingsController.php:69
 * @route '/dashboard/settings/reset'
 */
reset.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: reset.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Dashboard\SettingsController::reset
 * @see app/Http/Controllers/Dashboard/SettingsController.php:69
 * @route '/dashboard/settings/reset'
 */
    const resetForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: reset.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Dashboard\SettingsController::reset
 * @see app/Http/Controllers/Dashboard/SettingsController.php:69
 * @route '/dashboard/settings/reset'
 */
        resetForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: reset.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Dashboard\SettingsController::reset
 * @see app/Http/Controllers/Dashboard/SettingsController.php:69
 * @route '/dashboard/settings/reset'
 */
        resetForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: reset.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    reset.form = resetForm
/**
* @see \App\Http\Controllers\Dashboard\SettingsController::permissions
 * @see app/Http/Controllers/Dashboard/SettingsController.php:276
 * @route '/dashboard/settings/permissions'
 */
export const permissions = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: permissions.url(options),
    method: 'put',
})

permissions.definition = {
    methods: ["put"],
    url: '/dashboard/settings/permissions',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Dashboard\SettingsController::permissions
 * @see app/Http/Controllers/Dashboard/SettingsController.php:276
 * @route '/dashboard/settings/permissions'
 */
permissions.url = (options?: RouteQueryOptions) => {
    return permissions.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\SettingsController::permissions
 * @see app/Http/Controllers/Dashboard/SettingsController.php:276
 * @route '/dashboard/settings/permissions'
 */
permissions.put = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: permissions.url(options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\Dashboard\SettingsController::permissions
 * @see app/Http/Controllers/Dashboard/SettingsController.php:276
 * @route '/dashboard/settings/permissions'
 */
    const permissionsForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: permissions.url({
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Dashboard\SettingsController::permissions
 * @see app/Http/Controllers/Dashboard/SettingsController.php:276
 * @route '/dashboard/settings/permissions'
 */
        permissionsForm.put = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: permissions.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    permissions.form = permissionsForm
/**
* @see \App\Http\Controllers\Dashboard\SettingsController::marquees
 * @see app/Http/Controllers/Dashboard/SettingsController.php:552
 * @route '/dashboard/settings/marquees'
 */
export const marquees = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: marquees.url(options),
    method: 'get',
})

marquees.definition = {
    methods: ["get","head"],
    url: '/dashboard/settings/marquees',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Dashboard\SettingsController::marquees
 * @see app/Http/Controllers/Dashboard/SettingsController.php:552
 * @route '/dashboard/settings/marquees'
 */
marquees.url = (options?: RouteQueryOptions) => {
    return marquees.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\SettingsController::marquees
 * @see app/Http/Controllers/Dashboard/SettingsController.php:552
 * @route '/dashboard/settings/marquees'
 */
marquees.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: marquees.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Dashboard\SettingsController::marquees
 * @see app/Http/Controllers/Dashboard/SettingsController.php:552
 * @route '/dashboard/settings/marquees'
 */
marquees.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: marquees.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Dashboard\SettingsController::marquees
 * @see app/Http/Controllers/Dashboard/SettingsController.php:552
 * @route '/dashboard/settings/marquees'
 */
    const marqueesForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: marquees.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Dashboard\SettingsController::marquees
 * @see app/Http/Controllers/Dashboard/SettingsController.php:552
 * @route '/dashboard/settings/marquees'
 */
        marqueesForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: marquees.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Dashboard\SettingsController::marquees
 * @see app/Http/Controllers/Dashboard/SettingsController.php:552
 * @route '/dashboard/settings/marquees'
 */
        marqueesForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: marquees.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    marquees.form = marqueesForm
const settings = {
    update: Object.assign(update, update),
resetVip: Object.assign(resetVip, resetVip),
reset: Object.assign(reset, reset),
permissions: Object.assign(permissions, permissions),
roles: Object.assign(roles, roles),
features: Object.assign(features, features),
marquees: Object.assign(marquees, marquees7cf7b9),
}

export default settings