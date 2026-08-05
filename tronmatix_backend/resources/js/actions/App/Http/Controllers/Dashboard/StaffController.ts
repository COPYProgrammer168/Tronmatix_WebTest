import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Dashboard\StaffController::heartbeat
 * @see app/Http/Controllers/Dashboard/StaffController.php:187
 * @route '/api/staff/heartbeat'
 */
const heartbeat822f2e0714a0bce5e773065fb73f4d90 = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: heartbeat822f2e0714a0bce5e773065fb73f4d90.url(options),
    method: 'post',
})

heartbeat822f2e0714a0bce5e773065fb73f4d90.definition = {
    methods: ["post"],
    url: '/api/staff/heartbeat',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Dashboard\StaffController::heartbeat
 * @see app/Http/Controllers/Dashboard/StaffController.php:187
 * @route '/api/staff/heartbeat'
 */
heartbeat822f2e0714a0bce5e773065fb73f4d90.url = (options?: RouteQueryOptions) => {
    return heartbeat822f2e0714a0bce5e773065fb73f4d90.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\StaffController::heartbeat
 * @see app/Http/Controllers/Dashboard/StaffController.php:187
 * @route '/api/staff/heartbeat'
 */
heartbeat822f2e0714a0bce5e773065fb73f4d90.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: heartbeat822f2e0714a0bce5e773065fb73f4d90.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Dashboard\StaffController::heartbeat
 * @see app/Http/Controllers/Dashboard/StaffController.php:187
 * @route '/api/staff/heartbeat'
 */
    const heartbeat822f2e0714a0bce5e773065fb73f4d90Form = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: heartbeat822f2e0714a0bce5e773065fb73f4d90.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Dashboard\StaffController::heartbeat
 * @see app/Http/Controllers/Dashboard/StaffController.php:187
 * @route '/api/staff/heartbeat'
 */
        heartbeat822f2e0714a0bce5e773065fb73f4d90Form.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: heartbeat822f2e0714a0bce5e773065fb73f4d90.url(options),
            method: 'post',
        })
    
    heartbeat822f2e0714a0bce5e773065fb73f4d90.form = heartbeat822f2e0714a0bce5e773065fb73f4d90Form
    /**
* @see \App\Http\Controllers\Dashboard\StaffController::heartbeat
 * @see app/Http/Controllers/Dashboard/StaffController.php:187
 * @route '/dashboard/staff/heartbeat'
 */
const heartbeat83227fa3e59d10d274f0560f39e87e06 = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: heartbeat83227fa3e59d10d274f0560f39e87e06.url(options),
    method: 'post',
})

heartbeat83227fa3e59d10d274f0560f39e87e06.definition = {
    methods: ["post"],
    url: '/dashboard/staff/heartbeat',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Dashboard\StaffController::heartbeat
 * @see app/Http/Controllers/Dashboard/StaffController.php:187
 * @route '/dashboard/staff/heartbeat'
 */
heartbeat83227fa3e59d10d274f0560f39e87e06.url = (options?: RouteQueryOptions) => {
    return heartbeat83227fa3e59d10d274f0560f39e87e06.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\StaffController::heartbeat
 * @see app/Http/Controllers/Dashboard/StaffController.php:187
 * @route '/dashboard/staff/heartbeat'
 */
heartbeat83227fa3e59d10d274f0560f39e87e06.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: heartbeat83227fa3e59d10d274f0560f39e87e06.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Dashboard\StaffController::heartbeat
 * @see app/Http/Controllers/Dashboard/StaffController.php:187
 * @route '/dashboard/staff/heartbeat'
 */
    const heartbeat83227fa3e59d10d274f0560f39e87e06Form = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: heartbeat83227fa3e59d10d274f0560f39e87e06.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Dashboard\StaffController::heartbeat
 * @see app/Http/Controllers/Dashboard/StaffController.php:187
 * @route '/dashboard/staff/heartbeat'
 */
        heartbeat83227fa3e59d10d274f0560f39e87e06Form.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: heartbeat83227fa3e59d10d274f0560f39e87e06.url(options),
            method: 'post',
        })
    
    heartbeat83227fa3e59d10d274f0560f39e87e06.form = heartbeat83227fa3e59d10d274f0560f39e87e06Form

/**
* Multiple routes resolve to \App\Http\Controllers\Dashboard\StaffController::heartbeat, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `heartbeat['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
export const heartbeat = {
    '/api/staff/heartbeat': heartbeat822f2e0714a0bce5e773065fb73f4d90,
    '/dashboard/staff/heartbeat': heartbeat83227fa3e59d10d274f0560f39e87e06,
}

/**
* @see \App\Http\Controllers\Dashboard\StaffController::index
 * @see app/Http/Controllers/Dashboard/StaffController.php:45
 * @route '/dashboard/staff'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/dashboard/staff',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Dashboard\StaffController::index
 * @see app/Http/Controllers/Dashboard/StaffController.php:45
 * @route '/dashboard/staff'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\StaffController::index
 * @see app/Http/Controllers/Dashboard/StaffController.php:45
 * @route '/dashboard/staff'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Dashboard\StaffController::index
 * @see app/Http/Controllers/Dashboard/StaffController.php:45
 * @route '/dashboard/staff'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Dashboard\StaffController::index
 * @see app/Http/Controllers/Dashboard/StaffController.php:45
 * @route '/dashboard/staff'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Dashboard\StaffController::index
 * @see app/Http/Controllers/Dashboard/StaffController.php:45
 * @route '/dashboard/staff'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Dashboard\StaffController::index
 * @see app/Http/Controllers/Dashboard/StaffController.php:45
 * @route '/dashboard/staff'
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
* @see \App\Http\Controllers\Dashboard\StaffController::invite
 * @see app/Http/Controllers/Dashboard/StaffController.php:83
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
 * @see app/Http/Controllers/Dashboard/StaffController.php:83
 * @route '/dashboard/staff/invite'
 */
invite.url = (options?: RouteQueryOptions) => {
    return invite.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\StaffController::invite
 * @see app/Http/Controllers/Dashboard/StaffController.php:83
 * @route '/dashboard/staff/invite'
 */
invite.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: invite.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Dashboard\StaffController::invite
 * @see app/Http/Controllers/Dashboard/StaffController.php:83
 * @route '/dashboard/staff/invite'
 */
    const inviteForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: invite.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Dashboard\StaffController::invite
 * @see app/Http/Controllers/Dashboard/StaffController.php:83
 * @route '/dashboard/staff/invite'
 */
        inviteForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: invite.url(options),
            method: 'post',
        })
    
    invite.form = inviteForm
/**
* @see \App\Http\Controllers\Dashboard\StaffController::resendInvite
 * @see app/Http/Controllers/Dashboard/StaffController.php:135
 * @route '/dashboard/staff/invites/{id}/resend'
 */
export const resendInvite = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: resendInvite.url(args, options),
    method: 'post',
})

resendInvite.definition = {
    methods: ["post"],
    url: '/dashboard/staff/invites/{id}/resend',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Dashboard\StaffController::resendInvite
 * @see app/Http/Controllers/Dashboard/StaffController.php:135
 * @route '/dashboard/staff/invites/{id}/resend'
 */
resendInvite.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return resendInvite.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\StaffController::resendInvite
 * @see app/Http/Controllers/Dashboard/StaffController.php:135
 * @route '/dashboard/staff/invites/{id}/resend'
 */
resendInvite.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: resendInvite.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Dashboard\StaffController::resendInvite
 * @see app/Http/Controllers/Dashboard/StaffController.php:135
 * @route '/dashboard/staff/invites/{id}/resend'
 */
    const resendInviteForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: resendInvite.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Dashboard\StaffController::resendInvite
 * @see app/Http/Controllers/Dashboard/StaffController.php:135
 * @route '/dashboard/staff/invites/{id}/resend'
 */
        resendInviteForm.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: resendInvite.url(args, options),
            method: 'post',
        })
    
    resendInvite.form = resendInviteForm
/**
* @see \App\Http\Controllers\Dashboard\StaffController::updateRole
 * @see app/Http/Controllers/Dashboard/StaffController.php:151
 * @route '/dashboard/staff/{id}/role'
 */
export const updateRole = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: updateRole.url(args, options),
    method: 'patch',
})

updateRole.definition = {
    methods: ["patch"],
    url: '/dashboard/staff/{id}/role',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Dashboard\StaffController::updateRole
 * @see app/Http/Controllers/Dashboard/StaffController.php:151
 * @route '/dashboard/staff/{id}/role'
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
* @see \App\Http\Controllers\Dashboard\StaffController::updateRole
 * @see app/Http/Controllers/Dashboard/StaffController.php:151
 * @route '/dashboard/staff/{id}/role'
 */
updateRole.patch = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: updateRole.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\Dashboard\StaffController::updateRole
 * @see app/Http/Controllers/Dashboard/StaffController.php:151
 * @route '/dashboard/staff/{id}/role'
 */
    const updateRoleForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: updateRole.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PATCH',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Dashboard\StaffController::updateRole
 * @see app/Http/Controllers/Dashboard/StaffController.php:151
 * @route '/dashboard/staff/{id}/role'
 */
        updateRoleForm.patch = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: updateRole.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    updateRole.form = updateRoleForm
/**
* @see \App\Http\Controllers\Dashboard\StaffController::toggle
 * @see app/Http/Controllers/Dashboard/StaffController.php:170
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
 * @see app/Http/Controllers/Dashboard/StaffController.php:170
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
 * @see app/Http/Controllers/Dashboard/StaffController.php:170
 * @route '/dashboard/staff/{id}/toggle'
 */
toggle.patch = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: toggle.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\Dashboard\StaffController::toggle
 * @see app/Http/Controllers/Dashboard/StaffController.php:170
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
 * @see app/Http/Controllers/Dashboard/StaffController.php:170
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
* @see \App\Http\Controllers\Dashboard\StaffController::setOffline
 * @see app/Http/Controllers/Dashboard/StaffController.php:204
 * @route '/dashboard/staff/offline'
 */
export const setOffline = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: setOffline.url(options),
    method: 'post',
})

setOffline.definition = {
    methods: ["post"],
    url: '/dashboard/staff/offline',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Dashboard\StaffController::setOffline
 * @see app/Http/Controllers/Dashboard/StaffController.php:204
 * @route '/dashboard/staff/offline'
 */
setOffline.url = (options?: RouteQueryOptions) => {
    return setOffline.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\StaffController::setOffline
 * @see app/Http/Controllers/Dashboard/StaffController.php:204
 * @route '/dashboard/staff/offline'
 */
setOffline.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: setOffline.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Dashboard\StaffController::setOffline
 * @see app/Http/Controllers/Dashboard/StaffController.php:204
 * @route '/dashboard/staff/offline'
 */
    const setOfflineForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: setOffline.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Dashboard\StaffController::setOffline
 * @see app/Http/Controllers/Dashboard/StaffController.php:204
 * @route '/dashboard/staff/offline'
 */
        setOfflineForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: setOffline.url(options),
            method: 'post',
        })
    
    setOffline.form = setOfflineForm
/**
* @see \App\Http\Controllers\Dashboard\StaffController::destroy
 * @see app/Http/Controllers/Dashboard/StaffController.php:215
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
 * @see app/Http/Controllers/Dashboard/StaffController.php:215
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
 * @see app/Http/Controllers/Dashboard/StaffController.php:215
 * @route '/dashboard/staff/{id}'
 */
destroy.delete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Dashboard\StaffController::destroy
 * @see app/Http/Controllers/Dashboard/StaffController.php:215
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
 * @see app/Http/Controllers/Dashboard/StaffController.php:215
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
const StaffController = { heartbeat, index, invite, resendInvite, updateRole, toggle, setOffline, destroy }

export default StaffController