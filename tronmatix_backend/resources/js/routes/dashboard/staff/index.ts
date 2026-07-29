import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Dashboard\StaffController::invite
 * @see app/Http/Controllers/Dashboard/StaffController.php:69
 * @route '/dashboard/staff/invite'
 */
export const invite = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: invite.url(options),
    method: 'post',
})

invite.definition = {
    methods: ["post"],
    url: '/dashboard/staff/invite',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Dashboard\StaffController::invite
 * @see app/Http/Controllers/Dashboard/StaffController.php:69
 * @route '/dashboard/staff/invite'
 */
invite.url = (options?: RouteQueryOptions) => {
    return invite.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\StaffController::invite
 * @see app/Http/Controllers/Dashboard/StaffController.php:69
 * @route '/dashboard/staff/invite'
 */
invite.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: invite.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Dashboard\StaffController::invite
 * @see app/Http/Controllers/Dashboard/StaffController.php:69
 * @route '/dashboard/staff/invite'
 */
    const inviteForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: invite.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Dashboard\StaffController::invite
 * @see app/Http/Controllers/Dashboard/StaffController.php:69
 * @route '/dashboard/staff/invite'
 */
        inviteForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: invite.url(options),
            method: 'post',
        })
    
    invite.form = inviteForm
/**
* @see \App\Http\Controllers\Dashboard\StaffController::role
 * @see app/Http/Controllers/Dashboard/StaffController.php:111
 * @route '/dashboard/staff/{id}/role'
 */
export const role = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: role.url(args, options),
    method: 'patch',
})

role.definition = {
    methods: ["patch"],
    url: '/dashboard/staff/{id}/role',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Dashboard\StaffController::role
 * @see app/Http/Controllers/Dashboard/StaffController.php:111
 * @route '/dashboard/staff/{id}/role'
 */
role.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return role.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\StaffController::role
 * @see app/Http/Controllers/Dashboard/StaffController.php:111
 * @route '/dashboard/staff/{id}/role'
 */
role.patch = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: role.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\Dashboard\StaffController::role
 * @see app/Http/Controllers/Dashboard/StaffController.php:111
 * @route '/dashboard/staff/{id}/role'
 */
    const roleForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: role.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PATCH',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Dashboard\StaffController::role
 * @see app/Http/Controllers/Dashboard/StaffController.php:111
 * @route '/dashboard/staff/{id}/role'
 */
        roleForm.patch = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: role.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    role.form = roleForm
/**
* @see \App\Http\Controllers\Dashboard\StaffController::toggle
 * @see app/Http/Controllers/Dashboard/StaffController.php:130
 * @route '/dashboard/staff/{id}/toggle'
 */
export const toggle = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: toggle.url(args, options),
    method: 'patch',
})

toggle.definition = {
    methods: ["patch"],
    url: '/dashboard/staff/{id}/toggle',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Dashboard\StaffController::toggle
 * @see app/Http/Controllers/Dashboard/StaffController.php:130
 * @route '/dashboard/staff/{id}/toggle'
 */
toggle.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return toggle.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\StaffController::toggle
 * @see app/Http/Controllers/Dashboard/StaffController.php:130
 * @route '/dashboard/staff/{id}/toggle'
 */
toggle.patch = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: toggle.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\Dashboard\StaffController::toggle
 * @see app/Http/Controllers/Dashboard/StaffController.php:130
 * @route '/dashboard/staff/{id}/toggle'
 */
    const toggleForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: toggle.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PATCH',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Dashboard\StaffController::toggle
 * @see app/Http/Controllers/Dashboard/StaffController.php:130
 * @route '/dashboard/staff/{id}/toggle'
 */
        toggleForm.patch = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: toggle.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    toggle.form = toggleForm
/**
* @see \App\Http\Controllers\Dashboard\StaffController::heartbeat
 * @see app/Http/Controllers/Dashboard/StaffController.php:147
 * @route '/dashboard/staff/heartbeat'
 */
export const heartbeat = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: heartbeat.url(options),
    method: 'post',
})

heartbeat.definition = {
    methods: ["post"],
    url: '/dashboard/staff/heartbeat',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Dashboard\StaffController::heartbeat
 * @see app/Http/Controllers/Dashboard/StaffController.php:147
 * @route '/dashboard/staff/heartbeat'
 */
heartbeat.url = (options?: RouteQueryOptions) => {
    return heartbeat.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\StaffController::heartbeat
 * @see app/Http/Controllers/Dashboard/StaffController.php:147
 * @route '/dashboard/staff/heartbeat'
 */
heartbeat.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: heartbeat.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Dashboard\StaffController::heartbeat
 * @see app/Http/Controllers/Dashboard/StaffController.php:147
 * @route '/dashboard/staff/heartbeat'
 */
    const heartbeatForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: heartbeat.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Dashboard\StaffController::heartbeat
 * @see app/Http/Controllers/Dashboard/StaffController.php:147
 * @route '/dashboard/staff/heartbeat'
 */
        heartbeatForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: heartbeat.url(options),
            method: 'post',
        })
    
    heartbeat.form = heartbeatForm
/**
* @see \App\Http\Controllers\Dashboard\StaffController::offline
 * @see app/Http/Controllers/Dashboard/StaffController.php:164
 * @route '/dashboard/staff/offline'
 */
export const offline = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: offline.url(options),
    method: 'post',
})

offline.definition = {
    methods: ["post"],
    url: '/dashboard/staff/offline',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Dashboard\StaffController::offline
 * @see app/Http/Controllers/Dashboard/StaffController.php:164
 * @route '/dashboard/staff/offline'
 */
offline.url = (options?: RouteQueryOptions) => {
    return offline.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\StaffController::offline
 * @see app/Http/Controllers/Dashboard/StaffController.php:164
 * @route '/dashboard/staff/offline'
 */
offline.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: offline.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Dashboard\StaffController::offline
 * @see app/Http/Controllers/Dashboard/StaffController.php:164
 * @route '/dashboard/staff/offline'
 */
    const offlineForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: offline.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Dashboard\StaffController::offline
 * @see app/Http/Controllers/Dashboard/StaffController.php:164
 * @route '/dashboard/staff/offline'
 */
        offlineForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: offline.url(options),
            method: 'post',
        })
    
    offline.form = offlineForm
/**
* @see \App\Http\Controllers\Dashboard\StaffController::destroy
 * @see app/Http/Controllers/Dashboard/StaffController.php:175
 * @route '/dashboard/staff/{id}'
 */
export const destroy = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/dashboard/staff/{id}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Dashboard\StaffController::destroy
 * @see app/Http/Controllers/Dashboard/StaffController.php:175
 * @route '/dashboard/staff/{id}'
 */
destroy.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return destroy.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\StaffController::destroy
 * @see app/Http/Controllers/Dashboard/StaffController.php:175
 * @route '/dashboard/staff/{id}'
 */
destroy.delete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Dashboard\StaffController::destroy
 * @see app/Http/Controllers/Dashboard/StaffController.php:175
 * @route '/dashboard/staff/{id}'
 */
    const destroyForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroy.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Dashboard\StaffController::destroy
 * @see app/Http/Controllers/Dashboard/StaffController.php:175
 * @route '/dashboard/staff/{id}'
 */
        destroyForm.delete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroy.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroy.form = destroyForm
const staff = {
    invite: Object.assign(invite, invite),
role: Object.assign(role, role),
toggle: Object.assign(toggle, toggle),
heartbeat: Object.assign(heartbeat, heartbeat),
offline: Object.assign(offline, offline),
destroy: Object.assign(destroy, destroy),
}

export default staff