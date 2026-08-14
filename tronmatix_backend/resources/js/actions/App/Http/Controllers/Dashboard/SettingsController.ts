import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Dashboard\SettingsController::notifications
 * @see app/Http/Controllers/Dashboard/SettingsController.php:79
 * @route '/dashboard/notifications'
 */
export const notifications = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: notifications.url(options),
    method: 'get',
})

notifications.definition = {
    methods: ["get","head"],
    url: '/dashboard/notifications',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Dashboard\SettingsController::notifications
 * @see app/Http/Controllers/Dashboard/SettingsController.php:79
 * @route '/dashboard/notifications'
 */
notifications.url = (options?: RouteQueryOptions) => {
    return notifications.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\SettingsController::notifications
 * @see app/Http/Controllers/Dashboard/SettingsController.php:79
 * @route '/dashboard/notifications'
 */
notifications.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: notifications.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Dashboard\SettingsController::notifications
 * @see app/Http/Controllers/Dashboard/SettingsController.php:79
 * @route '/dashboard/notifications'
 */
notifications.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: notifications.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Dashboard\SettingsController::notifications
 * @see app/Http/Controllers/Dashboard/SettingsController.php:79
 * @route '/dashboard/notifications'
 */
    const notificationsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: notifications.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Dashboard\SettingsController::notifications
 * @see app/Http/Controllers/Dashboard/SettingsController.php:79
 * @route '/dashboard/notifications'
 */
        notificationsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: notifications.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Dashboard\SettingsController::notifications
 * @see app/Http/Controllers/Dashboard/SettingsController.php:79
 * @route '/dashboard/notifications'
 */
        notificationsForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: notifications.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    notifications.form = notificationsForm
/**
* @see \App\Http\Controllers\Dashboard\SettingsController::clearNotifications
 * @see app/Http/Controllers/Dashboard/SettingsController.php:104
 * @route '/dashboard/notifications/clear'
 */
export const clearNotifications = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: clearNotifications.url(options),
    method: 'post',
})

clearNotifications.definition = {
    methods: ["post"],
    url: '/dashboard/notifications/clear',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Dashboard\SettingsController::clearNotifications
 * @see app/Http/Controllers/Dashboard/SettingsController.php:104
 * @route '/dashboard/notifications/clear'
 */
clearNotifications.url = (options?: RouteQueryOptions) => {
    return clearNotifications.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\SettingsController::clearNotifications
 * @see app/Http/Controllers/Dashboard/SettingsController.php:104
 * @route '/dashboard/notifications/clear'
 */
clearNotifications.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: clearNotifications.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Dashboard\SettingsController::clearNotifications
 * @see app/Http/Controllers/Dashboard/SettingsController.php:104
 * @route '/dashboard/notifications/clear'
 */
    const clearNotificationsForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: clearNotifications.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Dashboard\SettingsController::clearNotifications
 * @see app/Http/Controllers/Dashboard/SettingsController.php:104
 * @route '/dashboard/notifications/clear'
 */
        clearNotificationsForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: clearNotifications.url(options),
            method: 'post',
        })
    
    clearNotifications.form = clearNotificationsForm
/**
* @see \App\Http\Controllers\Dashboard\SettingsController::show
 * @see app/Http/Controllers/Dashboard/SettingsController.php:18
 * @route '/dashboard/settings'
 */
export const show = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/dashboard/settings',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Dashboard\SettingsController::show
 * @see app/Http/Controllers/Dashboard/SettingsController.php:18
 * @route '/dashboard/settings'
 */
show.url = (options?: RouteQueryOptions) => {
    return show.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\SettingsController::show
 * @see app/Http/Controllers/Dashboard/SettingsController.php:18
 * @route '/dashboard/settings'
 */
show.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Dashboard\SettingsController::show
 * @see app/Http/Controllers/Dashboard/SettingsController.php:18
 * @route '/dashboard/settings'
 */
show.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Dashboard\SettingsController::show
 * @see app/Http/Controllers/Dashboard/SettingsController.php:18
 * @route '/dashboard/settings'
 */
    const showForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: show.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Dashboard\SettingsController::show
 * @see app/Http/Controllers/Dashboard/SettingsController.php:18
 * @route '/dashboard/settings'
 */
        showForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Dashboard\SettingsController::show
 * @see app/Http/Controllers/Dashboard/SettingsController.php:18
 * @route '/dashboard/settings'
 */
        showForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    show.form = showForm
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
* @see \App\Http\Controllers\Dashboard\SettingsController::resetVipRoles
 * @see app/Http/Controllers/Dashboard/SettingsController.php:516
 * @route '/dashboard/settings/reset-vip'
 */
export const resetVipRoles = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: resetVipRoles.url(options),
    method: 'post',
})

resetVipRoles.definition = {
    methods: ["post"],
    url: '/dashboard/settings/reset-vip',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Dashboard\SettingsController::resetVipRoles
 * @see app/Http/Controllers/Dashboard/SettingsController.php:516
 * @route '/dashboard/settings/reset-vip'
 */
resetVipRoles.url = (options?: RouteQueryOptions) => {
    return resetVipRoles.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\SettingsController::resetVipRoles
 * @see app/Http/Controllers/Dashboard/SettingsController.php:516
 * @route '/dashboard/settings/reset-vip'
 */
resetVipRoles.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: resetVipRoles.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Dashboard\SettingsController::resetVipRoles
 * @see app/Http/Controllers/Dashboard/SettingsController.php:516
 * @route '/dashboard/settings/reset-vip'
 */
    const resetVipRolesForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: resetVipRoles.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Dashboard\SettingsController::resetVipRoles
 * @see app/Http/Controllers/Dashboard/SettingsController.php:516
 * @route '/dashboard/settings/reset-vip'
 */
        resetVipRolesForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: resetVipRoles.url(options),
            method: 'post',
        })
    
    resetVipRoles.form = resetVipRolesForm
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
* @see \App\Http\Controllers\Dashboard\SettingsController::updatePermissions
 * @see app/Http/Controllers/Dashboard/SettingsController.php:276
 * @route '/dashboard/settings/permissions'
 */
export const updatePermissions = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updatePermissions.url(options),
    method: 'put',
})

updatePermissions.definition = {
    methods: ["put"],
    url: '/dashboard/settings/permissions',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Dashboard\SettingsController::updatePermissions
 * @see app/Http/Controllers/Dashboard/SettingsController.php:276
 * @route '/dashboard/settings/permissions'
 */
updatePermissions.url = (options?: RouteQueryOptions) => {
    return updatePermissions.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\SettingsController::updatePermissions
 * @see app/Http/Controllers/Dashboard/SettingsController.php:276
 * @route '/dashboard/settings/permissions'
 */
updatePermissions.put = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updatePermissions.url(options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\Dashboard\SettingsController::updatePermissions
 * @see app/Http/Controllers/Dashboard/SettingsController.php:276
 * @route '/dashboard/settings/permissions'
 */
    const updatePermissionsForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: updatePermissions.url({
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Dashboard\SettingsController::updatePermissions
 * @see app/Http/Controllers/Dashboard/SettingsController.php:276
 * @route '/dashboard/settings/permissions'
 */
        updatePermissionsForm.put = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: updatePermissions.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    updatePermissions.form = updatePermissionsForm
/**
* @see \App\Http\Controllers\Dashboard\SettingsController::storeRole
 * @see app/Http/Controllers/Dashboard/SettingsController.php:331
 * @route '/dashboard/settings/roles'
 */
export const storeRole = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storeRole.url(options),
    method: 'post',
})

storeRole.definition = {
    methods: ["post"],
    url: '/dashboard/settings/roles',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Dashboard\SettingsController::storeRole
 * @see app/Http/Controllers/Dashboard/SettingsController.php:331
 * @route '/dashboard/settings/roles'
 */
storeRole.url = (options?: RouteQueryOptions) => {
    return storeRole.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\SettingsController::storeRole
 * @see app/Http/Controllers/Dashboard/SettingsController.php:331
 * @route '/dashboard/settings/roles'
 */
storeRole.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storeRole.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Dashboard\SettingsController::storeRole
 * @see app/Http/Controllers/Dashboard/SettingsController.php:331
 * @route '/dashboard/settings/roles'
 */
    const storeRoleForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: storeRole.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Dashboard\SettingsController::storeRole
 * @see app/Http/Controllers/Dashboard/SettingsController.php:331
 * @route '/dashboard/settings/roles'
 */
        storeRoleForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: storeRole.url(options),
            method: 'post',
        })
    
    storeRole.form = storeRoleForm
/**
* @see \App\Http\Controllers\Dashboard\SettingsController::updateRole
 * @see app/Http/Controllers/Dashboard/SettingsController.php:369
 * @route '/dashboard/settings/roles/{id}'
 */
export const updateRole = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updateRole.url(args, options),
    method: 'put',
})

updateRole.definition = {
    methods: ["put"],
    url: '/dashboard/settings/roles/{id}',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Dashboard\SettingsController::updateRole
 * @see app/Http/Controllers/Dashboard/SettingsController.php:369
 * @route '/dashboard/settings/roles/{id}'
 */
updateRole.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    id: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        id: args.id,
                }

    return updateRole.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\SettingsController::updateRole
 * @see app/Http/Controllers/Dashboard/SettingsController.php:369
 * @route '/dashboard/settings/roles/{id}'
 */
updateRole.put = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updateRole.url(args, options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\Dashboard\SettingsController::updateRole
 * @see app/Http/Controllers/Dashboard/SettingsController.php:369
 * @route '/dashboard/settings/roles/{id}'
 */
    const updateRoleForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: updateRole.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Dashboard\SettingsController::updateRole
 * @see app/Http/Controllers/Dashboard/SettingsController.php:369
 * @route '/dashboard/settings/roles/{id}'
 */
        updateRoleForm.put = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: updateRole.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    updateRole.form = updateRoleForm
/**
* @see \App\Http\Controllers\Dashboard\SettingsController::destroyRole
 * @see app/Http/Controllers/Dashboard/SettingsController.php:417
 * @route '/dashboard/settings/roles/{id}'
 */
export const destroyRole = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroyRole.url(args, options),
    method: 'delete',
})

destroyRole.definition = {
    methods: ["delete"],
    url: '/dashboard/settings/roles/{id}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Dashboard\SettingsController::destroyRole
 * @see app/Http/Controllers/Dashboard/SettingsController.php:417
 * @route '/dashboard/settings/roles/{id}'
 */
destroyRole.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    id: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        id: args.id,
                }

    return destroyRole.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\SettingsController::destroyRole
 * @see app/Http/Controllers/Dashboard/SettingsController.php:417
 * @route '/dashboard/settings/roles/{id}'
 */
destroyRole.delete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroyRole.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Dashboard\SettingsController::destroyRole
 * @see app/Http/Controllers/Dashboard/SettingsController.php:417
 * @route '/dashboard/settings/roles/{id}'
 */
    const destroyRoleForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroyRole.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Dashboard\SettingsController::destroyRole
 * @see app/Http/Controllers/Dashboard/SettingsController.php:417
 * @route '/dashboard/settings/roles/{id}'
 */
        destroyRoleForm.delete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroyRole.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroyRole.form = destroyRoleForm
/**
* @see \App\Http\Controllers\Dashboard\SettingsController::storeFeature
 * @see app/Http/Controllers/Dashboard/SettingsController.php:445
 * @route '/dashboard/settings/features'
 */
export const storeFeature = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storeFeature.url(options),
    method: 'post',
})

storeFeature.definition = {
    methods: ["post"],
    url: '/dashboard/settings/features',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Dashboard\SettingsController::storeFeature
 * @see app/Http/Controllers/Dashboard/SettingsController.php:445
 * @route '/dashboard/settings/features'
 */
storeFeature.url = (options?: RouteQueryOptions) => {
    return storeFeature.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\SettingsController::storeFeature
 * @see app/Http/Controllers/Dashboard/SettingsController.php:445
 * @route '/dashboard/settings/features'
 */
storeFeature.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storeFeature.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Dashboard\SettingsController::storeFeature
 * @see app/Http/Controllers/Dashboard/SettingsController.php:445
 * @route '/dashboard/settings/features'
 */
    const storeFeatureForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: storeFeature.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Dashboard\SettingsController::storeFeature
 * @see app/Http/Controllers/Dashboard/SettingsController.php:445
 * @route '/dashboard/settings/features'
 */
        storeFeatureForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: storeFeature.url(options),
            method: 'post',
        })
    
    storeFeature.form = storeFeatureForm
/**
* @see \App\Http\Controllers\Dashboard\SettingsController::updateFeature
 * @see app/Http/Controllers/Dashboard/SettingsController.php:468
 * @route '/dashboard/settings/features/{id}'
 */
export const updateFeature = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updateFeature.url(args, options),
    method: 'put',
})

updateFeature.definition = {
    methods: ["put"],
    url: '/dashboard/settings/features/{id}',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Dashboard\SettingsController::updateFeature
 * @see app/Http/Controllers/Dashboard/SettingsController.php:468
 * @route '/dashboard/settings/features/{id}'
 */
updateFeature.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    id: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        id: args.id,
                }

    return updateFeature.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\SettingsController::updateFeature
 * @see app/Http/Controllers/Dashboard/SettingsController.php:468
 * @route '/dashboard/settings/features/{id}'
 */
updateFeature.put = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updateFeature.url(args, options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\Dashboard\SettingsController::updateFeature
 * @see app/Http/Controllers/Dashboard/SettingsController.php:468
 * @route '/dashboard/settings/features/{id}'
 */
    const updateFeatureForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: updateFeature.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Dashboard\SettingsController::updateFeature
 * @see app/Http/Controllers/Dashboard/SettingsController.php:468
 * @route '/dashboard/settings/features/{id}'
 */
        updateFeatureForm.put = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: updateFeature.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    updateFeature.form = updateFeatureForm
/**
* @see \App\Http\Controllers\Dashboard\SettingsController::destroyFeature
 * @see app/Http/Controllers/Dashboard/SettingsController.php:494
 * @route '/dashboard/settings/features/{id}'
 */
export const destroyFeature = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroyFeature.url(args, options),
    method: 'delete',
})

destroyFeature.definition = {
    methods: ["delete"],
    url: '/dashboard/settings/features/{id}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Dashboard\SettingsController::destroyFeature
 * @see app/Http/Controllers/Dashboard/SettingsController.php:494
 * @route '/dashboard/settings/features/{id}'
 */
destroyFeature.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    id: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        id: args.id,
                }

    return destroyFeature.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\SettingsController::destroyFeature
 * @see app/Http/Controllers/Dashboard/SettingsController.php:494
 * @route '/dashboard/settings/features/{id}'
 */
destroyFeature.delete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroyFeature.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Dashboard\SettingsController::destroyFeature
 * @see app/Http/Controllers/Dashboard/SettingsController.php:494
 * @route '/dashboard/settings/features/{id}'
 */
    const destroyFeatureForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroyFeature.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Dashboard\SettingsController::destroyFeature
 * @see app/Http/Controllers/Dashboard/SettingsController.php:494
 * @route '/dashboard/settings/features/{id}'
 */
        destroyFeatureForm.delete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroyFeature.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroyFeature.form = destroyFeatureForm
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
/**
* @see \App\Http\Controllers\Dashboard\SettingsController::storeMarquee
 * @see app/Http/Controllers/Dashboard/SettingsController.php:569
 * @route '/dashboard/settings/marquees'
 */
export const storeMarquee = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storeMarquee.url(options),
    method: 'post',
})

storeMarquee.definition = {
    methods: ["post"],
    url: '/dashboard/settings/marquees',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Dashboard\SettingsController::storeMarquee
 * @see app/Http/Controllers/Dashboard/SettingsController.php:569
 * @route '/dashboard/settings/marquees'
 */
storeMarquee.url = (options?: RouteQueryOptions) => {
    return storeMarquee.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\SettingsController::storeMarquee
 * @see app/Http/Controllers/Dashboard/SettingsController.php:569
 * @route '/dashboard/settings/marquees'
 */
storeMarquee.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storeMarquee.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Dashboard\SettingsController::storeMarquee
 * @see app/Http/Controllers/Dashboard/SettingsController.php:569
 * @route '/dashboard/settings/marquees'
 */
    const storeMarqueeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: storeMarquee.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Dashboard\SettingsController::storeMarquee
 * @see app/Http/Controllers/Dashboard/SettingsController.php:569
 * @route '/dashboard/settings/marquees'
 */
        storeMarqueeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: storeMarquee.url(options),
            method: 'post',
        })
    
    storeMarquee.form = storeMarqueeForm
/**
* @see \App\Http\Controllers\Dashboard\SettingsController::updateMarquee
 * @see app/Http/Controllers/Dashboard/SettingsController.php:593
 * @route '/dashboard/settings/marquees/{id}'
 */
export const updateMarquee = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updateMarquee.url(args, options),
    method: 'put',
})

updateMarquee.definition = {
    methods: ["put"],
    url: '/dashboard/settings/marquees/{id}',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Dashboard\SettingsController::updateMarquee
 * @see app/Http/Controllers/Dashboard/SettingsController.php:593
 * @route '/dashboard/settings/marquees/{id}'
 */
updateMarquee.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    id: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        id: args.id,
                }

    return updateMarquee.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\SettingsController::updateMarquee
 * @see app/Http/Controllers/Dashboard/SettingsController.php:593
 * @route '/dashboard/settings/marquees/{id}'
 */
updateMarquee.put = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updateMarquee.url(args, options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\Dashboard\SettingsController::updateMarquee
 * @see app/Http/Controllers/Dashboard/SettingsController.php:593
 * @route '/dashboard/settings/marquees/{id}'
 */
    const updateMarqueeForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: updateMarquee.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Dashboard\SettingsController::updateMarquee
 * @see app/Http/Controllers/Dashboard/SettingsController.php:593
 * @route '/dashboard/settings/marquees/{id}'
 */
        updateMarqueeForm.put = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: updateMarquee.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    updateMarquee.form = updateMarqueeForm
/**
* @see \App\Http\Controllers\Dashboard\SettingsController::destroyMarquee
 * @see app/Http/Controllers/Dashboard/SettingsController.php:619
 * @route '/dashboard/settings/marquees/{id}'
 */
export const destroyMarquee = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroyMarquee.url(args, options),
    method: 'delete',
})

destroyMarquee.definition = {
    methods: ["delete"],
    url: '/dashboard/settings/marquees/{id}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Dashboard\SettingsController::destroyMarquee
 * @see app/Http/Controllers/Dashboard/SettingsController.php:619
 * @route '/dashboard/settings/marquees/{id}'
 */
destroyMarquee.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    id: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        id: args.id,
                }

    return destroyMarquee.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\SettingsController::destroyMarquee
 * @see app/Http/Controllers/Dashboard/SettingsController.php:619
 * @route '/dashboard/settings/marquees/{id}'
 */
destroyMarquee.delete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroyMarquee.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Dashboard\SettingsController::destroyMarquee
 * @see app/Http/Controllers/Dashboard/SettingsController.php:619
 * @route '/dashboard/settings/marquees/{id}'
 */
    const destroyMarqueeForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroyMarquee.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Dashboard\SettingsController::destroyMarquee
 * @see app/Http/Controllers/Dashboard/SettingsController.php:619
 * @route '/dashboard/settings/marquees/{id}'
 */
        destroyMarqueeForm.delete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroyMarquee.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroyMarquee.form = destroyMarqueeForm
const SettingsController = { notifications, clearNotifications, show, update, resetVipRoles, reset, updatePermissions, storeRole, updateRole, destroyRole, storeFeature, updateFeature, destroyFeature, marquees, storeMarquee, updateMarquee, destroyMarquee }

export default SettingsController