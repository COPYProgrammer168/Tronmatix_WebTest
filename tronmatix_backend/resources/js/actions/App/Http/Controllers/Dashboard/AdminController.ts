import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Dashboard\AdminController::invite
 * @see app/Http/Controllers/Dashboard/AdminController.php:33
 * @route '/dashboard/admin/invite'
 */
export const invite = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: invite.url(options),
    method: 'post',
})

invite.definition = {
    methods: ["post"],
    url: '/dashboard/admin/invite',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Dashboard\AdminController::invite
 * @see app/Http/Controllers/Dashboard/AdminController.php:33
 * @route '/dashboard/admin/invite'
 */
invite.url = (options?: RouteQueryOptions) => {
    return invite.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\AdminController::invite
 * @see app/Http/Controllers/Dashboard/AdminController.php:33
 * @route '/dashboard/admin/invite'
 */
invite.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: invite.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Dashboard\AdminController::invite
 * @see app/Http/Controllers/Dashboard/AdminController.php:33
 * @route '/dashboard/admin/invite'
 */
    const inviteForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: invite.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Dashboard\AdminController::invite
 * @see app/Http/Controllers/Dashboard/AdminController.php:33
 * @route '/dashboard/admin/invite'
 */
        inviteForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: invite.url(options),
            method: 'post',
        })
    
    invite.form = inviteForm
/**
* @see \App\Http\Controllers\Dashboard\AdminController::updateRole
 * @see app/Http/Controllers/Dashboard/AdminController.php:67
 * @route '/dashboard/admin/{id}/role'
 */
export const updateRole = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: updateRole.url(args, options),
    method: 'patch',
})

updateRole.definition = {
    methods: ["patch"],
    url: '/dashboard/admin/{id}/role',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Dashboard\AdminController::updateRole
 * @see app/Http/Controllers/Dashboard/AdminController.php:67
 * @route '/dashboard/admin/{id}/role'
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
* @see \App\Http\Controllers\Dashboard\AdminController::updateRole
 * @see app/Http/Controllers/Dashboard/AdminController.php:67
 * @route '/dashboard/admin/{id}/role'
 */
updateRole.patch = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: updateRole.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\Dashboard\AdminController::updateRole
 * @see app/Http/Controllers/Dashboard/AdminController.php:67
 * @route '/dashboard/admin/{id}/role'
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
* @see \App\Http\Controllers\Dashboard\AdminController::updateRole
 * @see app/Http/Controllers/Dashboard/AdminController.php:67
 * @route '/dashboard/admin/{id}/role'
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
* @see \App\Http\Controllers\Dashboard\AdminController::toggle
 * @see app/Http/Controllers/Dashboard/AdminController.php:88
 * @route '/dashboard/admin/{id}/toggle'
 */
export const toggle = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: toggle.url(args, options),
    method: 'patch',
})

toggle.definition = {
    methods: ["patch"],
    url: '/dashboard/admin/{id}/toggle',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Dashboard\AdminController::toggle
 * @see app/Http/Controllers/Dashboard/AdminController.php:88
 * @route '/dashboard/admin/{id}/toggle'
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
* @see \App\Http\Controllers\Dashboard\AdminController::toggle
 * @see app/Http/Controllers/Dashboard/AdminController.php:88
 * @route '/dashboard/admin/{id}/toggle'
 */
toggle.patch = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: toggle.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\Dashboard\AdminController::toggle
 * @see app/Http/Controllers/Dashboard/AdminController.php:88
 * @route '/dashboard/admin/{id}/toggle'
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
* @see \App\Http\Controllers\Dashboard\AdminController::toggle
 * @see app/Http/Controllers/Dashboard/AdminController.php:88
 * @route '/dashboard/admin/{id}/toggle'
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
* @see \App\Http\Controllers\Dashboard\AdminController::destroy
 * @see app/Http/Controllers/Dashboard/AdminController.php:107
 * @route '/dashboard/admin/{id}'
 */
export const destroy = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/dashboard/admin/{id}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Dashboard\AdminController::destroy
 * @see app/Http/Controllers/Dashboard/AdminController.php:107
 * @route '/dashboard/admin/{id}'
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
* @see \App\Http\Controllers\Dashboard\AdminController::destroy
 * @see app/Http/Controllers/Dashboard/AdminController.php:107
 * @route '/dashboard/admin/{id}'
 */
destroy.delete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Dashboard\AdminController::destroy
 * @see app/Http/Controllers/Dashboard/AdminController.php:107
 * @route '/dashboard/admin/{id}'
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
* @see \App\Http\Controllers\Dashboard\AdminController::destroy
 * @see app/Http/Controllers/Dashboard/AdminController.php:107
 * @route '/dashboard/admin/{id}'
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
const AdminController = { invite, updateRole, toggle, destroy }

export default AdminController